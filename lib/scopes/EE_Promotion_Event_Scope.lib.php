<?php

use EventEspresso\core\services\request\DataType;

/**
 * Defines the Event Scope
 *
 * @since      1.0.0
 * @see        EE_Promotion_Scope for any phpdoc comments on classes defined there.
 * @package    EE4 Promotions
 * @subpackage models
 * @author     Darren Ethier
 */
class EE_Promotion_Event_Scope extends EE_Promotion_Scope
{
    public function __construct()
    {
        parent::__construct();
        add_filter(
            'FHEE__EventEspresso_core_services_calculators_LineItemCalculator__reorderSubLineItems__line_items',
            [$this, 'reorderSubLineItems']
        );
    }


    protected function _set_main_properties_and_hooks()
    {
        $this->label->singular = esc_html__('Event', 'event_espresso');
        $this->label->plural   = esc_html__('Events', 'event_espresso');
        $this->slug            = EE_Promotion_Scope::SCOPE_EVENT;
        // filter for get_events on admin list table to only show events attached to specific promotion.
        add_filter('FHEE__Events_Admin_Page__get_events__where', [$this, 'event_list_query_params'], 10, 2);
        // filter to show a helpful title on events list table when displaying events filtered by promotion
        add_filter(
            'FHEE__EE_Admin_Page___display_admin_list_table_page__before_list_table__template_arg',
            [$this, 'before_events_list_table_content'],
            10,
            4
        );
        // control which events have promotions applied to them
        add_filter(
            'FHEE__EE_Promotion_Scope__get_object_line_items_from_cart__is_applicable_item',
            [$this, 'is_applicable_item'],
            10,
            2
        );
    }


    /**
     * Callback for FHEE__Events_Admin_Page__get_events__where.  Event Scope adds
     * additional query params to the query retrieving the events in certain conditions.
     *
     * @param array $where    current query where params for event query
     * @param array $req_data incoming request data.
     * @return array            where query_args for get_events query.
     * @since       1.0.0
     */
    public function event_list_query_params(array $where, array $req_data): array
    {
        if (! empty($req_data['EVT_IDs'])) {
            $evt_ids         = is_array($req_data['EVT_IDs'])
                ? $req_data['EVT_IDs']
                : explode(',', $req_data['EVT_IDs']);
            $where['EVT_ID'] = ['IN', $evt_ids];
        }
        return $where;
    }


    /**
     * callback for the FHEE__EE_Admin_Page___display_admin_list_table_page__before_list_table__template_arg filter so
     * if displaying events filtered by promotion we add helpful title for viewer.
     *
     * @param string $content    Any current content.
     * @param string $page_slug  Page slug of page
     * @param array  $req_data   Incoming request data.
     * @param string $req_action 'action' value for page
     * @return string  If correct page then and conditions are met the new string. Otherwise existing.
     * @throws EE_Error
     * @throws ReflectionException
     * @since 1.0.0
     */
    public function before_events_list_table_content(
        string $content,
        string $page_slug,
        array $req_data,
        string $req_action
    ): string {
        if ($page_slug !== 'espresso_events' || $req_action !== 'default' || empty($req_data['PRO_ID'])) {
            return $content;
        }
        $promotion = EEM_Promotion::instance()->get_one_by_ID($req_data['PRO_ID']);
        if ($promotion instanceof EE_Promotion) {
            $query_args = [
                'action' => 'edit',
                'PRO_ID' => $promotion->ID(),
            ];
            $url        =
                EEH_URL::add_query_args_and_nonce($query_args, admin_url('admin.php?page=espresso_promotions'));
            $pro_linked = '
            <a href="' . $url . '"
               class="ee-aria-tooltip"
               aria-label="' . esc_html__('Click to view promotion details.', 'event_espresso') . '"
            >
                ' . $promotion->name() . '
            </a>';
            $content    .= '<h3>' . sprintf(
                    esc_html__('Viewing Events that the %s promotion applies to.', 'event_espresso'),
                    $pro_linked
                ) . '</h3>';
        }

        return $content;
    }


    /**
     * Child scope classes indicate what gets returned when a "name" is requested.
     *
     * @param array|int|EE_Event $EVT_ID Event ID (or array of IDs)
     *                                   or EE_Event object for the EE_Event object being utilized.
     * @param bool|string        $link   if FALSE then just return name,
     *                                   otherwise 'front' wraps name in link to frontend details,
     *                                   'admin' wraps name in link to backend details.
     * @param int                $PRO_ID Optional. Providing the Promotion ID allows identification in downstream code
     *                                   for what promotion is being handled.
     *                                   (i.e. adding it to the query_args for the links).
     * @return string
     * @throws EE_Error
     * @throws ReflectionException
     * @since 1.0.0
     */
    public function name($EVT_ID, $link = false, int $PRO_ID = 0): string
    {
        if (empty($EVT_ID) || (is_array($EVT_ID) && count($EVT_ID) > 1)) {
            switch ($link) {
                case 'front':
                    // @todo eventually a filter could be added here so that the link goes to a filtered archive view of the events JUST for this promotion in the frontend. For now we won't do anything (cause I doubt this will be used much).
                    $prepend = $append = '';
                    break;

                case 'admin':
                    $EVT_IDs    = is_array($EVT_ID) && ! empty($EVT_ID) ? implode(',', $EVT_ID) : '';
                    $query_args = ! empty($EVT_IDs) ? ['EVT_IDs' => $EVT_IDs, 'PRO_ID' => $PRO_ID] : [];
                    $url        = add_query_arg($query_args, $this->get_admin_url(0));
                    $prepend    = '<a href="' . $url . '" class="ee-aria-tooltip" aria-label="' . esc_html__(
                            'See events this promotion applies to.',
                            'event_espresso'
                        ) . '">';
                    $append     = '</a>';
                    break;

                default:
                    $prepend = $append = '';
                    break;
            }
            $count       = is_array($EVT_ID) ? count($EVT_ID) : 0;
            $promo_count = $this->get_promo_count_display($count);

            return $this->get_scope_icon() . $prepend . $this->label->plural . $append . $promo_count;
        }

        $evt = $EVT_ID instanceof EE_Event ? $EVT_ID : $this->_get_model_object($EVT_ID);

        switch ($link) {
            case 'front':
                $url     = $this->get_frontend_url($evt->ID());
                $prepend = '<a href="' . $url . '" class="ee-aria-tooltip" aria-label="'
                    . esc_html__('View details about this event.', 'event_espresso')
                    . '">';
                $append  = '</a>';
                break;

            case 'admin':
                $url     = $this->get_admin_url($evt->ID());
                $prepend =
                    '<a href="' . $url . '" class="ee-aria-tooltip" aria-label="' . esc_html__(
                        'See details on this event',
                        'event_espresso'
                    ) . '">';
                $append  = '</a>';
                break;

            default:
                $prepend = $append = '';
                break;
        }
        $event_name = '<span class="ee-promo-applies-to-event-name">' . $evt->name() . '</span>';

        return $this->get_scope_icon() . $prepend . $event_name . $append;
    }


    /**
     * This returns a html span string for the event scope icon.
     *
     * @param bool $class_only used to indicate if we only want to return the icon class
     *                         or the entire html string.
     * @return string
     * @since 1.0.0
     */
    public function get_scope_icon(bool $class_only = false): string
    {
        return $class_only
            ? 'dashicons dashicons-flag'
            : '<span class="dashicons dashicons-flag ee-aria-tooltip"
                     aria-label="' . esc_html__('Event Scope Promotion - applies to Events', 'event_espresso') . '">
               </span>';
    }


    /**
     * Child scope classes indicate what gets returned when a "description" is requested.
     *
     * @param array|int|EE_Event $EVT_ID (or array of IDS)  or EE_Event object for the EE_Event object being utilized
     * @return string
     * @throws EE_Error
     * @throws ReflectionException
     * @since 1.0.0
     */
    public function description($EVT_ID): string
    {
        if (empty($EVT_ID) || is_array($EVT_ID)) {
            return esc_html__('Applied to all events.', 'event_espresso');
        }

        $evt = $EVT_ID instanceof EE_Event ? $EVT_ID : $this->_get_model_object($EVT_ID);

        return sprintf(__('Applied to %s', 'event_espresso'), $evt->name());
    }


    /**
     * Child scope classes indicate what gets returned when the admin_url is requested.
     * Admin url usually points to the details page for the given id.
     *
     * @param array|int $EVT_ID ID or array of ids for the EE_Event object being utilized
     * @return string
     * @since 1.0.0
     */
    public function get_admin_url($EVT_ID): string
    {
        $base_url = admin_url('admin.php?page=espresso_events');
        if (empty($EVT_ID) || is_array($EVT_ID)) {
            return $base_url;
        }

        $query_args = [
            'action' => 'edit',
            'post'   => $EVT_ID,
        ];

        return EEH_URL::add_query_args_and_nonce($query_args, $base_url);
    }


    /**
     * Child scope classes indicate what gets returned when the frontend_url is requested.
     * Frontend url usually points to the single page view for the given id.
     *
     * @param array|int $EVT_ID ID or array of ids for the EE_Event object being utilized
     * @return string
     * @throws EE_Error
     * @throws ReflectionException
     * @since 1.0.0
     */
    public function get_frontend_url($EVT_ID): string
    {
        if (empty($EVT_ID) || is_array($EVT_ID)) {
            return EEH_Event_View::event_archive_url();
        }

        return EEH_Event_View::event_link_url($EVT_ID);
    }


    /**
     * Generate the html for selecting events that a promotion applies to.
     *
     * @param int $PRO_ID The promotion ID for the applies to selector we are retrieving.
     * @return string html
     * @throws EE_Error
     * @throws ReflectionException
     * @since 1.0.0
     */
    public function get_admin_applies_to_selector(int $PRO_ID): string
    {
        $show_filters    = $this->_maybe_overload_request_with_saved_filters($PRO_ID);
        $total_items     = $this->_get_total_items();
        $items_to_select = $this->get_scope_items();
        $selected_items  = $this->_get_applied_to_item_ids($PRO_ID);
        $template_args   = [
            'scope_slug'               => $this->slug,
            'scope'                    => $this,
            'header_content'           => sprintf(
                __('%sCheck off the specific events that this promotion will be applied to.%s', 'event_espresso'),
                '<p>',
                '</p>'
            ),
            'filters'                  => $this->_get_applies_to_filters($show_filters),
            'show_filters'             => $show_filters,
            'items_to_select'          => $this->_get_applies_to_items_to_select(
                $items_to_select,
                $selected_items,
                $PRO_ID
            ),
            'items_paging'             => $this->_get_applies_to_items_paging($total_items),
            'selected_items'           => $selected_items,
            'number_of_selected_items' => count($selected_items),
            'display_selected_label'   => esc_html__('Display only selected Events', 'event_espresso'),
            'footer_content'           => '',
        ];

        return EEH_Template::display_template(
            EE_PROMOTIONS_PATH . '/lib/scopes/templates/promotion_applies_to_wrapper.template.php',
            $template_args,
            true
        );
    }


    /**
     * Returns query args for use in model queries on the
     * EEM model related to scope.  Note this also should
     * consider any filters present
     *
     * @return array
     * @throws EE_Error
     * @throws ReflectionException
     * @since 1.0.0
     */
    public function get_query_args(): array
    {
        // $month_increment = apply_filters( 'FHEE__EE_Promotion_Event_Scope__get_query_args__month_increment', 1 );
        // check for any existing dtt queries
        $DTT_EVT_start = $this->request->getRequestParam('EVT_start_date_filter');
        $DTT_EVT_end   = $this->request->getRequestParam('EVT_end_date_filter');

        $_where = [
            'status' => ['NOT IN', [EEM_Event::cancelled, 'trash']],
        ];

        if (! empty($DTT_EVT_start)) {
            $_where['Datetime.DTT_EVT_start'] = [
                '>',
                EEM_datetime::instance()->convert_datetime_for_query('DTT_EVT_start', $DTT_EVT_start, 'Y-m-d g:i a'),
            ];
        }

        if (! empty($DTT_EVT_end)) {
            $_where['Datetime.DTT_EVT_end'] =
                ['<', EEM_Datetime::instance()->convert_datetime_for_query('DTT_EVT_end', $DTT_EVT_end, 'Y-m-d g:i a')];
        }

        // exclude expired events by default unless include_expiry is checked.
        if (! $this->request->requestParamIsSet('include_expired_events_filter')) {
            $_where['Datetime.DTT_EVT_end**exclude_expired_events_query'] = ['>', time()];
        }

        // category filters?
        $EVT_CAT_ID = $this->request->getRequestParam('EVT_CAT_ID', 0, DataType::INT);
        if ($EVT_CAT_ID) {
            $_where['Term_Taxonomy.term_id'] = $EVT_CAT_ID;
        }

        // event title?
        $title_filter = $this->request->getRequestParam('EVT_title_filter');
        if (! empty($title_filter)) {
            $_where['EVT_name'] = ['LIKE', '%' . $title_filter . '%'];
        }

        $orderby = $this->request->getRequestParam('PRO_scope_sort', 'DESC');

        $query_params = ['0' => $_where, 'order_by' => ['EVT_created' => $orderby], 'group_by' => 'EVT_ID'];

        // apply caps
        if (! EE_Registry::instance()->CAP->current_user_can('ee_read_others_events', 'get_events_for_promotions')) {
            $query_params = EEM_Event::instance()->alter_query_params_to_only_include_mine($query_params);
        }

        return $query_params;
    }


    /**
     * sets up the filters for the promotions scope selector
     *
     * @param bool|int|string|null $show_filters
     * @return string
     * @since 1.0.0
     */
    protected function _get_applies_to_filters($show_filters): string
    {
        $template_args = [
            'scope'        => $this,
            'show_filters' => $show_filters,
            'categories'   => [],
        ];

        // categories
        $categories = get_terms('espresso_event_categories', ['hide_empty' => false, 'fields' => 'id=>name']);

        $template_args['categories'][] = ['text' => esc_html__('Include all categories', 'event_espresso'), 'id' => 0];
        foreach ($categories as $id => $name) {
            $template_args['categories'][] = ['text' => $name, 'id' => $id];
        }
        $template_args['default'] = $this->request->getRequestParam('EVT_CAT_ID', 0, DataType::INT);

        // start date
        $existing_start_date = $this->request->requestParamIsSet('EVT_start_date_filter')
            ? date('Y-m-d h:i a', strtotime($this->request->getRequestParam('EVT_start_date_filter')))
            : '';

        $template_args['start_date_picker'] = new PromotionsDatepicker(
            $existing_start_date,
            'start',
            '#promotions-applied-to-mbox',
            'EVT_start_date_filter',
            esc_html__('enter filter start date', 'event_espresso')
        );

        // end date
        $existing_end_date                = $this->request->requestParamIsSet('EVT_end_date_filter')
            ? date('Y-m-d h:i a', strtotime($this->request->getRequestParam('EVT_end_date_filter')))
            : '';
        $template_args['end_date_picker'] = new PromotionsDatepicker(
            $existing_end_date,
            'end',
            '#promotions-applied-to-mbox',
            'EVT_end_date_filter',
            esc_html__('enter filter end date', 'event_espresso')
        );

        // event name
        $template_args['existing_name'] = $this->request->requestParamIsSet('EVT_title_filter')
            ? sanitize_title_for_query($this->request->getRequestParam('EVT_title_filter'))
            : '';

        // include expired events
        $template_args['expired_checked'] = $this->request->requestParamIsSet('include_expired_events_filter')
            ? " checked"
            : '';

        // return $cat_filter . '<br>' . $start_date_filter . '<br>' . $end_date_filter . '<br>' . $event_title_filter . '<br>' . $include_expired_filter . '<div style="clear: both"></div>';
        return EEH_Template::display_template(
            EE_PROMOTIONS_PATH . '/lib/scopes/templates/promotion_applies_to_filters.template.php',
            $template_args,
            true
        );
    }


    /**
     * save event relation for the applies to promotion
     *
     * @param EE_Promotion $promotion
     * @param array        $data the incoming form data
     * @return void
     * @throws EE_Error
     * @throws ReflectionException
     * @since   1.0.0
     */
    public function handle_promotion_update(EE_Promotion $promotion, array $data)
    {
        // first do we have any selected items?
        $selected_items = ! empty($data['ee_promotions_applied_selected_items_Event']) ? explode(
            ',',
            $data['ee_promotions_applied_selected_items_Event']
        ) : [];
        $evt_ids        = [];

        // existing pro_objects
        $pro_objects = $promotion->promotion_objects();

        // loop through existing and remove any that aren't present in the selected_items.
        foreach ($pro_objects as $pro_obj) {
            if (! in_array($pro_obj->OBJ_ID(), $selected_items)) {
                $promotion->delete_related('Promotion_Object', [['POB_ID' => $pro_obj->ID()]]);
            }
            $evt_ids[] = $pro_obj->OBJ_ID();
        }

        // k now let's make sure any that should be added are added.
        foreach ($selected_items as $EVT_ID) {
            if (in_array($EVT_ID, $evt_ids)) {
                continue;
            }
            $promotion_obj = EE_Promotion_Object::new_instance(
                [
                    'PRO_ID'   => $promotion->ID(),
                    'OBJ_ID'   => $EVT_ID,
                    'POB_type' => $this->slug,
                    'POB_used' => 0,
                ]
            );
            $promotion_obj->save();
        }

        // any filters to save?
        $set_filters = [
            'EVT_CAT_ID'                    => ! empty($data['EVT_CAT_ID']) ? $data['EVT_CAT_ID'] : null,
            'EVT_start_date_filter'         => ! empty($data['EVT_start_date_filter']) ? $data['EVT_start_date_filter']
                : null,
            'EVT_end_date_filter'           => ! empty($data['EVT_end_date_filter']) ? $data['EVT_end_date_filter']
                : null,
            'EVT_title_filter'              => ! empty($data['EVT_title_filter']) ? $data['EVT_title_filter'] : null,
            'include_expired_events_filter' => ! empty($data['include_expired_events_filter'])
                ? $data['include_expired_events_filter'] : null,
        ];

        $promotion->update_extra_meta('promo_saved_filters', $set_filters);
    }


    /**
     * get_promotion_line_item_type
     *
     * @param bool $apply_promos_before_tax
     * @return string
     */
    public function get_promotion_line_item_type(bool $apply_promos_before_tax = false): string
    {
        return $apply_promos_before_tax ? EEM_Line_Item::type_sub_line_item : EEM_Line_Item::type_line_item;
    }


    /**
     * is_applicable_item
     * control which events have promotions applied to them
     *
     * @param bool         $is_applicable_item
     * @param EE_Line_Item $object_type_line_item
     * @return bool
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function is_applicable_item(bool $is_applicable_item, EE_Line_Item $object_type_line_item): bool
    {
        return $object_type_line_item->totalWithTax() > 0 ? $is_applicable_item : false;
    }


    /**
     * @param EE_Line_Item $applicable_item
     * @return array|EE_Line_Item[]
     * @throws EE_Error
     * @throws ReflectionException
     * @since 1.0.17.p
     */
    protected function getApplicableItemChildren(EE_Line_Item $applicable_item): array
    {
        return EEH_Line_Item::get_ticket_line_items($applicable_item);
    }


    /**
     * @param callable     $add_promotion_line_item
     * @param EE_Line_Item $applicable_item
     * @param EE_Promotion $promotion
     * @param bool         $apply_promos_before_tax
     * @return bool
     * @throws EE_Error
     * @throws ReflectionException
     * @since 1.0.17.p
     */
    public function calculateAndApplyPromotion(
        callable $add_promotion_line_item,
        EE_Line_Item $applicable_item,
        EE_Promotion $promotion,
        bool $apply_promos_before_tax
    ): bool {
        return $apply_promos_before_tax
            ? $this->calculateAndApplyPromotionBeforeTaxes(
                $add_promotion_line_item,
                $applicable_item,
                $promotion
            )
            : $this->calculateAndApplyPromotionAfterTaxes(
                $add_promotion_line_item,
                $applicable_item,
                $promotion
            );
    }


    /**
     * this is the previous method for calculating and applying promotions
     *
     * @param callable     $add_promotion_line_item
     * @param EE_Line_Item $event_line_item
     * @param EE_Promotion $promotion
     * @return bool
     * @throws EE_Error
     * @throws ReflectionException
     * @since 1.0.17.p
     */
    private function calculateAndApplyPromotionAfterTaxes(
        callable $add_promotion_line_item,
        EE_Line_Item $event_line_item,
        EE_Promotion $promotion
    ): bool {
        $promotion_line_item = $this->generate_promotion_line_item(
            $event_line_item,
            $promotion,
            $promotion->name()
        );
        return $add_promotion_line_item(
            $event_line_item,
            $promotion_line_item,
            $promotion,
            $event_line_item->OBJ_ID()
        );
    }


    /**
     * applying promotions before taxes is a bit more complicated
     *
     * @param callable     $add_promotion_line_item
     * @param EE_Line_Item $event_line_item
     * @param EE_Promotion $promotion
     * @return bool
     * @throws EE_Error
     * @throws ReflectionException
     * @since 1.0.17.p
     */
    private function calculateAndApplyPromotionBeforeTaxes(
        callable $add_promotion_line_item,
        EE_Line_Item $event_line_item,
        EE_Promotion $promotion
    ): bool {
        $ticket_line_items = $this->getApplicableItemChildren($event_line_item);
        $ticket_count      = 0;
        foreach ($ticket_line_items as $ticket_line_item) {
            $ticket_count += $ticket_line_item->quantity();
        }
        $original_promo_amount = $promotion->amount();
        $promo_amount          = null;
        $remainder             = 0;
        if (! $promotion->is_percent()) {
            // fixed amount promotions need to be split between ALL the tickets
            $promo_amount = round($original_promo_amount / $ticket_count, 6, PHP_ROUND_HALF_DOWN);
            // calculate any leftovers like when dividing $10 by 3
            $remainder = round($original_promo_amount - ($promo_amount * $ticket_count), 6);
        }

        $counter = 0;
        $success = true;
        foreach ($ticket_line_items as $ticket_line_item) {
            if ($ticket_line_item instanceof EE_Line_Item) {
                $counter++;
                // percent amount promos need to be calculated for each ticket using its pretax total
                $amount = $promo_amount ?? $original_promo_amount / 100 * $ticket_line_item->pretaxTotal();
                // add remainder to first ticket
                $amount += $counter === 1 ? $remainder : 0;

                $promotion_line_item = $this->generatePromotionLineItem(
                    $ticket_line_item,
                    $promotion,
                    $amount,
                    true
                );
                $success             = $add_promotion_line_item(
                    $ticket_line_item,
                    $promotion_line_item,
                    $promotion,
                    $event_line_item->OBJ_ID()
                )
                    ? $success
                    : false;
            }
        }

        return $success;
    }


    public function reorderSubLineItems(array $line_items): array
    {
        $items = $line_items['items'] ?? [];
        $other = $line_items['other'] ?? [];
        foreach ($items as $sub_line_item) {
            if ($sub_line_item->OBJ_type() === EEM_Promotion::LINE_ITEM_OBJ_TYPE) {
                // move promotions into "others" array
                $other[ $sub_line_item->ID() ] = $sub_line_item;
                // remove from items array
                unset($items[ $sub_line_item->ID() ]);
            }
        }
        $line_items['items'] = $items;
        $line_items['other'] = $other;
        return $line_items;
    }
}
