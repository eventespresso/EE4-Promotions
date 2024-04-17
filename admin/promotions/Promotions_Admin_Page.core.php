<?php

/**
 * Promotions_Admin_Page
 * This contains the logic for setting up the Promotions Addon Admin related pages.  Any
 * methods without PHP doc comments have inline docs with parent class.
 *
 * @since       1.0.0
 * @package     EE Promotions
 * @subpackage  admin
 * @author      Darren Ethier, Brent Christensen
 */
class Promotions_Admin_Page extends EE_Admin_Page
{
    /**
     * This will hold the promotion object on create_new and edit routes
     */
    protected ?EE_Promotion $_promotion = null;

    protected ? EE_Promotions_Config $_config= null;


    protected function _init_page_props()
    {
        $this->page_slug        = PROMOTIONS_PG_SLUG;
        $this->page_label       = PROMOTIONS_LABEL;
        $this->_admin_base_url  = EE_PROMOTIONS_ADMIN_URL;
        $this->_admin_base_path = EE_PROMOTIONS_ADMIN;
    }


    protected function _ajax_hooks()
    {
    }


    protected function _define_page_props()
    {
        $this->_admin_page_title = PROMOTIONS_LABEL;
        $this->_labels           = [
            'buttons'    => [
                'add'               => esc_html__('Add New Promotion', 'event_espresso'),
                'edit'              => esc_html__('Edit Promotion', 'event_espresso'),
                'trash_promotion'   => esc_html__('Trash Promotion', 'event_espresso'),
                'restore_promotion' => esc_html__('Restore Promotion', 'event_espresso'),
            ],
            'publishbox' => [
                'create_new'          => esc_html__('Save New Promotion', 'event_espresso'),
                'edit'                => esc_html__('Update Promotion', 'event_espresso'),
                'promotions_settings' => esc_html__('Update Settings', 'event_espresso'),
            ],
        ];
    }


    protected function _set_page_routes()
    {
        $PRO_ID  = ! empty($this->_req_data['PRO_ID']) && ! is_array($this->_req_data['PRO_ID'])
            ? $this->_req_data['PRO_ID']
            : 0;

        $this->_page_routes = [
            'default'             => [
                'func'       => [$this, '_list_table'],
                'capability' => 'ee_read_promotions',
            ],
            'create_new'          => [
                'func'       => [$this, '_promotion_details'],
                'capability' => 'ee_edit_promotions',
                'args'       => [true],
            ],
            'edit'                => [
                'func'       => [$this, '_promotion_details'],
                'capability' => 'ee_edit_promotion',
                'obj_id'     => $PRO_ID,
            ],
            'update_promotion'    => [
                'func'       => [$this, '_insert_update_promotions'],
                'capability' => 'ee_edit_promotion',
                'obj_id'     => $PRO_ID,
                'noheader'   => true,
            ],
            'insert_promotion'    => [
                'func'       => [$this, '_insert_update_promotions'],
                'capability' => 'ee_edit_promotions',
                'noheader'   => true,
                'args'       => [true],
            ],
            'duplicate'           => [
                'func'       => [$this, '_duplicate_promotion'],
                'capability' => 'ee_edit_promotion',
                'obj_id'     => $PRO_ID,
                'noheader'   => true,
            ],
            'trash_promotion'     => [
                'func'       => [$this, '_trash_or_restore_promotion'],
                'capability' => 'ee_delete_promotion',
                'obj_id'     => $PRO_ID,
                'args'       => [true],
                'noheader'   => true,
            ],
            'trash_promotions'    => [
                'func'       => [$this, '_trash_or_restore_promotions'],
                'capability' => 'ee_delete_promotions',
                'args'       => [true],
                'noheader'   => true,
            ],
            'restore_promotion'   => [
                'func'       => [$this, '_trash_or_restore_promotion'],
                'capability' => 'ee_delete_promotion',
                'obj_id'     => $PRO_ID,
                'args'       => [false],
                'noheader'   => true,
            ],
            'restore_promotions'  => [
                'func'       => [$this, '_trash_or_restore_promotions'],
                'capability' => 'ee_delete_promotions',
                'args'       => [false],
                'noheader'   => true,
            ],
            'delete_promotions'   => [
                'func'       => [$this, '_delete_promotions'],
                'capability' => 'ee_delete_promotions',
                'noheader'   => true,
            ],
            'delete_promotion'    => [
                'func'       => [$this, '_delete_promotion'],
                'capability' => 'ee_delete_promotion',
                'obj_id'     => $PRO_ID,
                'noheader'   => true,
            ],
            'promotions_settings' => [
                'func'       => [$this, '_promotions_settings'],
                'capability' => 'manage_options',
            ],
            'update_settings'     => [
                'func'       => [$this, '_update_settings'],
                'capability' => 'manage_options',
                'noheader'   => true,
            ],
            'usage'               => [
                'func'       => [$this, '_usage'],
                'capability' => 'ee_read_promotions',
            ],
        ];
    }


    protected function _set_page_config()
    {
        $this->_page_config = [
            'default'             => [
                'nav'           => [
                    'label' => esc_html__('Promotions Overview', 'event_espresso'),
                    'order' => 10,
                ],
                'qtips'         => ['Promotions_List_Table_Tips'],
                'list_table'    => 'Promotions_Admin_List_Table',
                'require_nonce' => false,
            ],
            'create_new'          => [
                'nav'           => [
                    'label'      => esc_html__('Create Promotion', 'event_espresso'),
                    'order'      => 15,
                    'url'        => EEH_URL::add_query_args_and_nonce(
                        ['action' => 'create_new'],
                        EE_PROMOTIONS_ADMIN_URL
                    ),
                    'persistent' => false,
                ],
                'metaboxes'     => ['_promotions_metaboxes', '_publish_post_box'],
                'require_nonce' => false,
            ],
            'edit'                => [
                'nav'           => [
                    'label'      => esc_html__('Edit Promotion', 'event_espresso'),
                    'order'      => 15,
                    'url'        => EEH_URL::add_query_args_and_nonce(
                        [
                            'action' => 'edit',
                            'PRO_ID' => ! empty($this->_req_data['PRO_ID']) ? $this->_req_data['PRO_ID'] : 0,
                        ],
                        EE_PROMOTIONS_ADMIN_URL
                    ),
                    'persistent' => false,
                ],
                'metaboxes'     => ['_promotions_metaboxes', '_publish_post_box'],
                'require_nonce' => false,
            ],
            'promotions_settings' => [
                'nav'           => [
                    'label' => esc_html__('Settings', 'event_espresso'),
                    'order' => 20,
                ],
                'metaboxes'     => array_merge($this->_default_espresso_metaboxes, ['_publish_post_box']),
                'require_nonce' => false,
            ],
            'usage'               => [
                'nav'           => [
                    'label' => esc_html__('Promotions Usage', 'event_espresso'),
                    'order' => 30,
                ],
                'require_nonce' => false,
            ],
        ];
    }


    protected function _add_screen_options()
    {
    }


    protected function _add_screen_options_default()
    {
        $this->_per_page_screen_option();
    }


    protected function _add_feature_pointers()
    {
    }


    public function load_scripts_styles()
    {
        wp_register_style(
            'promotions-details-css',
            EE_PROMOTIONS_ADMIN_ASSETS_URL . 'promotions-details.css',
            ['ee-admin-css', 'espresso-ui-theme'],
            EE_PROMOTIONS_VERSION
        );
        wp_enqueue_style('promotions-details-css');
        wp_register_script(
            'espresso_promotions_admin',
            EE_PROMOTIONS_ADMIN_ASSETS_URL . 'espresso_promotions_admin.js',
            ['espresso_core', 'ee-datepicker', 'ee-parse-uri'],
            EE_PROMOTIONS_VERSION,
            true
        );
        wp_enqueue_script('espresso_promotions_admin');
    }


    public function admin_init()
    {
        EE_Registry::$i18n_js_strings['confirm_reset']                = esc_html__(
            'Are you sure you want to reset ALL your Event Espresso Promotions Information? This cannot be undone.',
            'event_espresso'
        );
        EE_Registry::$i18n_js_strings['codefieldEmptyError']          = esc_html__(
            'eePromotionsHelper.generate_code requires a selector for the codefield param. None was provided.',
            'event_espresso'
        );
        EE_Registry::$i18n_js_strings['codefieldInvalidError']        = esc_html__(
            'The codefield parameter sent to eePromotionsHelper.generate_code is invalid.  It must be a valid selector for the input field holding the generated coupon code.',
            'event_espresso'
        );
        EE_Registry::$i18n_js_strings['toggledScopeItemMissingParam'] = esc_html__(
            'eePromotionsHelper.scopeItemToggle requires the toggled checkbox dom element to be included as the argument.  Nothing was included.',
            'event_espresso'
        );
    }


    /**
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function admin_notices()
    {
        // is this a non-global promotion?  If so, then if there are no uses then let's show a notice that the promotion is
        // not active until a scope item is selected.
        if (
            $this->_promotion instanceof EE_Promotion
            && $this->_promotion->ID() !== 0
            && ! $this->_promotion->is_global()
            && $this->_promotion->get_scope_object_count() === 0
        ) {
            EE_Error::add_attention(
                sprintf(
                    esc_html__(
                        'This promotion is currently not active because you have selected %s as the scope for the promotion but have not applied the promotion to any %s.',
                        'event_espresso'
                    ),
                    $this->_promotion->scope_obj()->label->singular,
                    strtolower($this->_promotion->scope_obj()->label->plural)
                )
            );
            echo(EE_Error::get_notices());
        }
    }


    public function admin_footer_scripts()
    {
    }


    /**
     * _set_list_table_views_default
     *
     * @access protected
     */
    protected function _set_list_table_views_default()
    {
        $this->_views = [
            'all'   => [
                'slug'        => 'all',
                'label'       => esc_html__('All', 'event_espresso'),
                'count'       => 0,
                'bulk_action' => [],
            ],
            'trash' => [
                'slug'        => 'trash',
                'label'       => esc_html__('Trashed', 'event_espresso'),
                'count'       => 0,
                'bulk_action' => [
                    'restore_promotions' => esc_html__('Restore from Trash', 'event_espresso'),
                    'delete_promotions'  => esc_html__('Delete', 'event_espresso'),
                ],
            ]/**/
        ];


        if (
            EE_Registry::instance()->CAP->current_user_can(
                'ee_delete_promotions',
                'espresso_promotions_delete_promotions'
            )
        ) {
            $this->_views['trash']              = [
                'slug'        => 'trash',
                'label'       => esc_html__('Trashed', 'event_espresso'),
                'count'       => 0,
                'bulk_action' => [
                    'restore_promotions' => esc_html__('Restore from Trash', 'event_espresso'),
                    'delete_promotions'  => esc_html__('Delete', 'event_espresso'),
                ],
            ];
            $this->_views['all']['bulk_action'] = [
                'trash_promotions' => esc_html__('Move to Trash', 'event_espresso'),
            ];
        }
    }


    /**
     * @throws EE_Error
     * @throws ReflectionException
     */
    protected function _list_table()
    {
        $this->_config = $this->_get_config();
        if (
            EE_Registry::instance()->CAP->current_user_can(
                'ee_edit_promotions',
                'espresso_promotions_create_new_promotion'
            )
        ) {
            $this->_admin_page_title .= ' ' . $this->get_action_link_or_button(
                'create_new',
                'add',
                [],
                'add-new-h2'
            );
        }
        $this->_template_args['after_list_table'] = $this->_display_legend($this->_promotion_legend_items());
        $this->display_admin_list_table_page_with_no_sidebar();
    }


    /**
     * @return array
     * @throws EE_Error
     */
    protected function _promotion_legend_items(): array
    {
        $items = [
            'active_status'      => [
                'class' => 'ee-status-legend ee-status-legend-' . EE_Promotion::active,
                'desc'  => EEH_Template::pretty_status(EE_Promotion::active, false, 'sentence'),
            ],
            'upcoming_status'    => [
                'class' => 'ee-status-legend ee-status-legend-' . EE_Promotion::upcoming,
                'desc'  => EEH_Template::pretty_status(EE_Promotion::upcoming, false, 'sentence'),
            ],
            'expired_status'     => [
                'class' => 'ee-status-legend ee-status-legend-' . EE_Promotion::expired,
                'desc'  => EEH_Template::pretty_status(EE_Promotion::expired, false, 'sentence'),
            ],
            'unavailable_status' => [
                'class' => 'ee-status-legend ee-status-legend-' . EE_Promotion::unavailable,
                'desc'  => EEH_Template::pretty_status(EE_Promotion::unavailable, false, 'sentence'),
            ],
            ''                   => [
                'class' => '',
                'desc'  => '',
            ],
        ];
        foreach ($this->_config->scopes as $scope) {
            if ($scope instanceof EE_Promotion_Scope) {
                $items[ $scope->slug ] = [
                    'class' => $scope->get_scope_icon(true),
                    'desc'  => sprintf(
                        esc_html__('%1$s Scope Promotion - applies to %2$s only', 'event_espresso'),
                        $scope->label->singular,
                        $scope->label->plural
                    ),
                ];
            }
        }
        $items['global']    = [
            'class' => 'dashicons dashicons-admin-site',
            'desc'  => esc_html__('Global Promotion - applies to ALL scope items', 'event_espresso'),
        ];
        $items['exclusive'] = [
            'class' => 'dashicons dashicons-awards',
            'desc'  => esc_html__('Exclusive Promotion - can NOT be combined with others', 'event_espresso'),
        ];
        $items['unlimited'] = [
            'class' => '<span class="ee-infinity-sign" style="font-weight:600; margin-block-start:-.33rem;">&#8734;</span>',
            'desc'  => esc_html__('Unlimited', 'event_espresso'),
        ];

        return $items;
    }


    /**
     * _set_promotion_object
     *
     * @access protected
     * @throws EE_Error
     * @throws ReflectionException
     */
    protected function _set_promotion_object()
    {
        // only set if not set already
        if ($this->_promotion instanceof EE_Promotion) {
            return;
        }
        $this->_promotion = ! empty($this->_req_data['PRO_ID'])
            ? EEM_Promotion::instance()->get_one_by_ID($this->_req_data['PRO_ID'])
            : EEM_Promotion::instance()->create_default_object();

        // verify we have a promotion object
        if (! $this->_promotion instanceof EE_Promotion) {
            throw new EE_Error(
                sprintf(
                    esc_html__(
                        'Something might be wrong with the models or the given Promotion ID in the request (%s) is not for a valid Promotion in the DB.',
                        'event_espresso'
                    ),
                    $this->_req_data['PRO_ID']
                )
            );
        }
    }


    /**
     * metaboxes callback for create_new and edit promotion routes.
     *
     * @return void
     * @throws EE_Error
     * @throws ReflectionException
     * @since 1.0.0
     */
    protected function _promotions_metaboxes()
    {
        $this->_config = $this->_get_config();
        add_meta_box(
            'promotion-details-mbox',
            esc_html__('Promotions', 'event_espresso'),
            [$this, 'promotion_details_metabox'],
            $this->_wp_page_slug,
            'normal',
            'high'
        );
        add_meta_box(
            'promotions-applied-to-mbox',
            esc_html__('Promotion applies to...', 'event_espresso'),
            [$this, 'promotions_applied_to_metabox'],
            $this->_wp_page_slug,
            'side'
        );
    }


    /**
     * Callback for the create new promotion route.
     *
     * @param bool $new Whether promotion is new or not. Default TRUE.
     * @return void
     * @throws EE_Error
     * @throws ReflectionException
     * @since 1.0.0
     */
    protected function _promotion_details(bool $new = false)
    {
        $this->_set_promotion_object();
        $PRO_ID   = $new ? 0 : $this->_promotion->ID();
        $redirect = EEH_URL::add_query_args_and_nonce(['action' => 'default'], $this->_admin_base_url);
        $view     = $new ? 'insert_promotion' : 'update_promotion';
        $this->_set_add_edit_form_tags($view);
        $route = $this->_promotion->deleted() ? 'restore_promotion' : 'trash_promotion';

        /** add new button */
        if (
            EE_Registry::instance()->CAP->current_user_can(
                'ee_edit_promotions',
                'espresso_promotions_create_new_promotion'
            )
        ) {
            $this->_admin_page_title .= ' ' . $this->get_action_link_or_button(
                'create_new',
                'add',
                [],
                'add-new-h2'
            );
        }

        $this->_set_publish_post_box_vars('PRO_ID', $PRO_ID, $route, $redirect);
        $this->display_admin_page_with_sidebar();
    }


    /**
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function promotion_details_metabox()
    {
        $promotion_uses        = $this->_promotion->uses();
        $promotion_global_uses = $this->_promotion->global_uses();
        $form_args             = [
            'promotion'               => $this->_promotion,
            'promotion_global'        => EEH_Form_Fields::select_input(
                'PRO_global',
                $this->_yes_no_values,
                $this->_promotion->is_global()
            ),
            'promotion_exclusive'     => EEH_Form_Fields::select_input(
                'PRO_exclusive',
                $this->_yes_no_values,
                $this->_promotion->is_exclusive()
            ),
            'promotion_uses'          => $promotion_uses !== EE_INF_IN_DB ? $promotion_uses : '',
            'promotion_global_uses'   => $promotion_global_uses !== EE_INF_IN_DB ? $promotion_global_uses : '',
            'price_type_selector'     => $this->_get_price_type_selector(),
            'scope_selector'          => $this->_get_promotion_scope_selector(),
            'promo_starts_datepicker' => new PromotionsDatepicker(
                $this->_promotion->start('Y-m-d', 'h:i a'),
                'start',
                '#promotion-details-mbox',
                'PRO_start',
                esc_html__('enter promotion start date', 'event_espresso')
            ),
            'promo_ends_datepicker'   => new PromotionsDatepicker(
                $this->_promotion->end('Y-m-d', 'h:i a'),
                'end',
                '#promotion-details-mbox',
                'PRO_end',
                esc_html__('enter promotion end date', 'event_espresso')
            ),
        ];
        EEH_Template::display_template(
            EE_PROMOTIONS_ADMIN_TEMPLATE_PATH . 'promotion_details_form.template.php',
            $form_args
        );
    }


    /**
     * promotions_applied_to_metabox
     *
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function promotions_applied_to_metabox()
    {
        // we use the scope to get the metabox content.
        $scope = $this->_promotion->scope_obj();

        // if there is no scope then this is a default promotion object so the content will for promotions metabox will be generic.
        $content = empty($scope)
            ? esc_html__(
                'When you select a scope for the promotion this area will have options related to the selection.',
                'event_espresso'
            )
            : $scope->get_admin_applies_to_selector($this->_promotion->ID());
        echo $content;
    }


    /**
     * _get_price_type_selector
     *
     * @return string
     * @throws EE_Error
     * @throws ReflectionException
     */
    protected function _get_price_type_selector(): string
    {
        // get Price Types for discount base price type.
        $price_types = EEM_Price_Type::instance()->get_all([['PBT_ID' => EEM_Price_Type::base_type_discount]]);
        $values      = [];
        foreach ($price_types as $ID => $pt) {
            $values[] = [
                'text' => $pt->name(),
                'id'   => $ID,
            ];
        }

        $default = $this->_promotion->price_type_id();

        return EEH_Form_Fields::select_input('PRT_ID', $values, $default);
    }


    /**
     * _get_promotion_scope_selector
     *
     * @access protected
     */
    protected function _get_promotion_scope_selector(): string
    {
        $values = [];
        foreach ($this->_config->scopes as $scope_name => $scope) {
            $values[] = [
                'text' => $scope->label->singular,
                'id'   => $scope_name,
            ];
        }
        $redeemed     = $this->_promotion->redeemed();
        $name         = $redeemed > 0 ? 'PRO_scope_disabled' : 'PRO_scope';
        $default      = $this->_promotion->scope();
        $extra_params = $redeemed > 0 ? 'disabled="disabled"' : '';
        $content      = EEH_Form_Fields::select_input($name, $values, $default, $extra_params);
        if ($redeemed > 0) {
            $content .= '<input type="hidden" name="PRO_scope" value="' . $default . '">';
        }

        return $content;
    }


    /**
     * Takes care of inserting/updating promotions.
     *
     * @param bool $new Default false. Whether inserting or not.
     * @return void
     * @throws EE_Error
     * @throws ReflectionException
     * @since 1.0.0
     */
    protected function _insert_update_promotions(bool $new = false)
    {
        $promotion_values   = [
            'PRO_ID'          => ! empty($this->_req_data['PRO_ID'])
                ? absint($this->_req_data['PRO_ID'])
                : 0,
            'PRO_code'        => ! empty($this->_req_data['PRO_code'])
                ? $this->_req_data['PRO_code']
                : null,
            'PRO_scope'       => ! empty($this->_req_data['PRO_scope'])
                ? $this->_req_data['PRO_scope']
                : EE_Promotion_Scope::SCOPE_EVENT,
            'PRO_start'       => ! empty($this->_req_data['PRO_start'])
                ? $this->_req_data['PRO_start']
                : null,
            'PRO_end'         => ! empty($this->_req_data['PRO_end'])
                ? $this->_req_data['PRO_end']
                : null,
            'PRO_global'      => $this->_req_data['PRO_global'] ?? false,
            'PRO_exclusive'   => $this->_req_data['PRO_exclusive'] ?? true,
            'PRO_uses'        => ! empty($this->_req_data['PRO_uses'])
                ? $this->_req_data['PRO_uses']
                : EE_INF_IN_DB,
            'PRO_global_uses' => ! empty($this->_req_data['PRO_global_uses'])
                ? $this->_req_data['PRO_global_uses']
                : EE_INF_IN_DB,
            'PRO_accept_msg'  => ! empty($this->_req_data['PRO_accept_msg'])
                ? $this->_req_data['PRO_accept_msg']
                : '',
            'PRO_decline_msg' => ! empty($this->_req_data['PRO_decline_msg'])
                ? $this->_req_data['PRO_decline_msg']
                : '',
        ];
        $promo_price_values = [
            'PRC_ID'     => ! empty($this->_req_data['PRC_ID'])
                ? $this->_req_data['PRC_ID']
                : 0,
            'PRC_name'   => ! empty($this->_req_data['PRC_name'])
                ? $this->_req_data['PRC_name']
                : esc_html__(
                    'Special Promotion',
                    'event_espresso'
                ),
            'PRT_ID'     => ! empty($this->_req_data['PRT_ID'])
                ? $this->_req_data['PRT_ID']
                : 0,
            'PRC_amount' => ! empty($this->_req_data['PRC_amount'])
                ? $this->_req_data['PRC_amount']
                : 0,
            'PRC_desc'   => ! empty($this->_req_data['PRC_desc'])
                ? $this->_req_data['PRC_desc']
                : '',
        ];
        // first handle the price object
        $price = empty($promo_price_values['PRC_ID'])
            ? EE_Price::new_instance($promo_price_values)
            : EEM_Price::instance()->get_one_by_ID($promo_price_values['PRC_ID']);
        if (! empty($promo_price_values['PRC_ID'])) {
            // PRE-EXISTING PRICE so let's update the values.
            foreach ($promo_price_values as $field => $value) {
                $price->set($field, $value);
            }
        }
        // save price
        $price->save();
        // next handle the promotions
        if (empty($promotion_values['PRO_ID'])) {
            $promotion = EE_Promotion::new_instance($promotion_values, '', ['Y-m-d', 'g:i a']);
        } else {
            $promotion = EEM_Promotion::instance()->get_one_by_ID($promotion_values['PRO_ID']);
        }
        if (! empty($promotion_values['PRO_ID'])) {
            $promotion->set_date_format('Y-m-d');
            $promotion->set_time_format('g:i a');
            // PRE-EXISTING promotion so let's update the values
            foreach ($promotion_values as $field => $value) {
                $promotion->set($field, $value);
            }
        } else {
            // new promotion so let's add the price id for the price relation
            $promotion->set('PRC_ID', $price->ID());
        }
        // save promotion
        $promotion->save();
        // hook for scopes and others to do their stuff.
        do_action('AHEE__Promotions_Admin_Page___insert_update_promotion__after', $promotion, $this->_req_data);
        if ($promotion instanceof EE_Promotion) {
            $PRO_ID = $promotion->ID();
            if ($new) {
                EE_Error::add_success(__('Promotion has been successfully created.', 'event_espresso'));
            } else {
                EE_Error::add_success(__('Promotion has been successfully updated.', 'event_espresso'));
            }
        } else {
            $PRO_ID = 0;
            EE_Error::add_error(
                esc_html__('Something went wrong with saving the promotion', 'event_espresso'),
                __FILE__,
                __FUNCTION__,
                __LINE__
            );
        }

        $query_args = [
            'PRO_ID' => $PRO_ID,
            'action' => 'edit',
        ];
        $this->_redirect_after_action(null, '', '', $query_args, true);
    }


    /**
     * Duplicates a promotion.
     *
     * @return void
     * @throws EE_Error
     * @throws ReflectionException
     * @since  1.0.0
     */
    protected function _duplicate_promotion()
    {
        $new_promo = null;
        $success   = true;
        // first verify we have a promotion id
        $PRO_ID = ! empty($this->_req_data['PRO_ID']) ? $this->_req_data['PRO_ID'] : 0;
        if (empty($PRO_ID)) {
            $success = false;
            EE_Error::add_error(
                esc_html__(
                    'Unable to duplicate the promotion because there was no ID present in the request.',
                    'event_espresso'
                ),
                __FILE__,
                __FUNCTION__,
                __LINE__
            );
        }

        if ($success) {
            $orig_promo = EEM_Promotion::instance()->get_one_by_ID($PRO_ID);
            $new_promo  = $orig_promo instanceof EE_Promotion ? clone $orig_promo : null;

            if (! $new_promo instanceof EE_Promotion) {
                $success = false;
                EE_Error::add_error(
                    esc_html__(
                        'Unable to duplicate the promotion because for some reason there isn\'t a promotion in the database for the given id.',
                        'event_espresso'
                    ),
                    __FILE__,
                    __FUNCTION__,
                    __LINE__
                );
            }

            if ($success) {
                $new_promo->set('PRO_ID', 0);
                $new_promo->save();
                // we have to clone the promotion objects as well and then attach them to the new promo.
                $promo_objects = $orig_promo->promotion_objects();
                foreach ($promo_objects as $promo_obj) {
                    $new_promo_obj = clone $promo_obj;
                    $new_promo_obj->set('POB_ID', 0);
                    $new_promo_obj->set('PRO_ID', $new_promo->ID());
                    $new_promo_obj->set('POB_used', 0);
                    $new_promo_obj->save();
                }

                // clone price obj
                $price_obj = $orig_promo->get_first_related('Price');
                $new_price = clone $price_obj;
                $new_price->set('PRC_ID', 0);
                $new_price->save();
                $new_promo->set('PRC_ID', $new_price->ID());
                $new_promo->save();
            }
        }

        if ($success) {
            EE_Error::add_success(
                esc_html__('Promotion successfully duplicated, make any additional edits and update.', 'event_espresso')
            );
        }

        $query_args = [
            'PRO_ID' => $new_promo instanceof EE_Promotion ? $new_promo->ID() : 0,
            'action' => ! empty($PRO_ID) ? 'edit' : 'default',
        ];
        $this->_redirect_after_action(null, '', '', $query_args, true);
    }


    /**
     * Handles either trashing or restoring a given promotion.
     *
     * @param bool $trash    if true trashing, otherwise restoring
     * @param int  $PRO_ID   if set then this will be used for the promotion id to trash/restore.
     * @param bool $redirect if true then redirect otherwise don't
     * @return bool value of $success
     * @throws EE_Error
     * @throws ReflectionException
     * @since 1.0.0
     */
    protected function _trash_or_restore_promotion(bool $trash = false, int $PRO_ID = 0, bool $redirect = true): bool
    {
        $success = true;
        $PRO_ID  = ! empty($PRO_ID) ? $PRO_ID : 0;
        $PRO_ID  = ! empty($this->_req_data['PRO_ID']) && empty($PRO_ID) ? $this->_req_data['PRO_ID'] : $PRO_ID;
        if (empty($PRO_ID)) {
            $success = false;
            if ($trash) {
                EE_Error::add_error(
                    esc_html__('Unable to trash a promotion because no promotion id is accessible.', 'event_espresso'),
                    __FILE__,
                    __FUNCTION__,
                    __LINE__
                );
            } else {
                EE_Error::add_error(
                    esc_html__(
                        'Unable to restore a promotion because no promotion id is accessible.',
                        'event_espresso'
                    ),
                    __FILE__,
                    __FUNCTION__,
                    __LINE__
                );
            }
        }

        if ($success) {
            $success = $trash
                ? EEM_Promotion::instance()->delete_by_ID($PRO_ID)
                : EEM_Promotion::instance()->restore_by_ID(
                    $PRO_ID
                );
        }

        if ($success) {
            $trash ? EE_Error::add_success(__('Promotion successfully trashed.', 'event_espresso'))
                : EE_Error::add_success(__('Promotion successfully restored.', 'event_espresso'));
        } else {
            $trash
                ? EE_Error::add_error(
                    esc_html__('Promotion did not get trashed.', 'event_espresso'),
                    __FILE__,
                    __FUNCTION__,
                    __LINE__
                )
                : EE_Error::add_error(
                    esc_html__('Promotion did not get restored.', 'event_espresso'),
                    __FILE__,
                    __FUNCTION__,
                    __LINE__
                );
        }
        if ($redirect) {
            $query_args = [
                'action' => ! empty($this->_req_data['update_promotion_nonce']) ? 'edit' : 'default',
                'PRO_ID' => $PRO_ID,
            ];
            $this->_redirect_after_action(null, '', '', $query_args, true);
        }

        return $success;
    }



    // phpcs:disable WordPress.WP.I18n.MissingSingularPlaceholder


    /**
     * Trash or restore multiple promotions.  Expecting an array of promotion ids in the request.
     *
     * @param bool $trash if true then trash otherwise restore.
     * @return void
     * @throws EE_Error
     * @throws ReflectionException
     * @since  1.0.0
     */
    protected function _trash_or_restore_promotions(bool $trash = false)
    {
        $success = 0;
        $PRO_IDs = ! empty($this->_req_data['PRO_ID']) && is_array($this->_req_data['PRO_ID'])
            ? $this->_req_data['PRO_ID']
            : [];

        if (! empty($PRO_IDs)) {
            foreach ($PRO_IDs as $PRO_ID) {
                if ($this->_trash_or_restore_promotion($trash, $PRO_ID, false)) {
                    $success++;
                } else {
                    $trash
                        ? EE_Error::add_error(
                            sprintf(__('Promotion (%s) did not get trashed.', 'event_espresso'), $PRO_ID),
                            __FILE__,
                            __FUNCTION__,
                            __LINE__
                        )
                        : EE_Error::add_error(
                            sprintf(__('Promotion (%s) did not get restored.', 'event_espresso'), $PRO_ID),
                            __FILE__,
                            __FUNCTION__,
                            __LINE__
                        );
                }
            }
        } else {
            $trash
                ? EE_Error::add_error(
                    esc_html__('Unable to trash promotions because none were selected.', 'event_espresso'),
                    __FILE__,
                    __FUNCTION__,
                    __LINE__
                )
                : EE_Error::add_error(
                    esc_html__('Unable to restore promotions because none were selected.', 'event_espresso'),
                    __FILE__,
                    __FUNCTION__,
                    __LINE__
                );
        }

        if ($success > 0) {
            $trash
                ? EE_Error::add_success(
                    _n(
                        '1 promotion was successfully trashed',
                        '%s promotions were successfully trashed',
                        $success,
                        'event_espresso'
                    )
                )
                : EE_Error::add_success(
                    _n(
                        '1 promotion was successfully restored',
                        '%s promotions were successfully restored',
                        $success,
                        'event_espresso'
                    )
                );
        }

        $this->_redirect_after_action(null, '', '', ['action' => 'default'], true);
    }
    // phpcs:enable


    /**
     * Delete permanently a promotion.
     *
     * @param int  $PRO_ID   if given this is the id for the promotion to be permanently deleted.
     * @param bool $redirect if true then redirect, otherwise don't
     * @return bool value of $success;
     * @throws EE_Error
     * @throws ReflectionException
     * @since  1.0.0
     */
    protected function _delete_promotion(int $PRO_ID = 0, bool $redirect = true): bool
    {
        $success = true;
        $PRO_ID  = ! empty($PRO_ID) ? $PRO_ID : 0;
        $PRO_ID  = empty($PRO_ID) && ! empty($this->_req_data['PRO_ID']) ? $this->_req_data['PRO_ID'] : $PRO_ID;

        if (empty($PRO_ID)) {
            $success = false;
            EE_Error::add_error(
                esc_html__(
                    'Unable to delete permanently the promotion because no promotion id is accessible.',
                    'event_espresso'
                ),
                __FILE__,
                __FUNCTION__,
                __LINE__
            );
        }


        if ($success) {
            // first we need to determine if the given promotion has uses.  If it does it CANNOT be deleted.
            $promotion = EEM_Promotion::instance()->get_one_by_ID($PRO_ID);
            if ($promotion instanceof EE_Promotion) {
                if ($promotion->redeemed() > 0) {
                    $success = false;
                    EE_Error::add_error(
                        sprintf(
                            esc_html__(
                                'Unable to delete %s because it has been redeemed.  For archival and relational purposes this data must be retained in the db.',
                                'event_espresso'
                            ),
                            $promotion->name()
                        ),
                        __FILE__,
                        __FUNCTION__,
                        __LINE__
                    );
                } else {
                    // first delete permanently the related prices
                    $promotion->delete_related_permanently('Price');
                    // next delete related promotion objects permanently
                    $promotion->delete_related_permanently('Promotion_Object');

                    // now delete the promotion permanently
                    $success = $promotion->delete_permanently();
                }
                if ($success) {
                    EE_Error::add_success(
                        sprintf(__('Promotion %s has been permanently deleted.', 'event_espresso'), $promotion->name())
                    );
                } else {
                    EE_Error::add_error(
                        sprintf(
                            esc_html__('Unable to permanently delete the %s promotion.', 'event_espresso'),
                            $promotion->name()
                        ),
                        __FILE__,
                        __FUNCTION__,
                        __LINE__
                    );
                }
            } else {
                $success = false;
                EE_Error::add_error(
                    sprintf(__('There is no promotion in the db matching the id of %s.', 'event_espresso'), $PRO_ID),
                    __FILE__,
                    __FUNCTION__,
                    __LINE__
                );
            }
        }

        if ($redirect) {
            $query_args = [
                'action' => ! empty($this->_req_data['update_promotion_nonce']) ? 'edit' : 'default',
                'PRO_ID' => $PRO_ID,
            ];
            $this->_redirect_after_action(null, '', '', $query_args, true);
        }

        return $success;
    }



    // phpcs:disable WordPress.WP.I18n.MissingSingularPlaceholder


    /**
     * Delete permanently multiple promotions.  The promotion IDs are expected to be in the request.
     *
     * @return void
     * @throws EE_Error
     * @throws ReflectionException
     * @since  1.0.0
     */
    protected function _delete_promotions()
    {
        $success = 0;
        $PRO_IDs = ! empty($this->_req_data['PRO_ID']) && is_array($this->_req_data['PRO_ID'])
            ? $this->_req_data['PRO_ID']
            : [];
        if (! empty($PRO_IDs)) {
            foreach ($PRO_IDs as $PRO_ID) {
                if ($this->_delete_promotion($PRO_ID, false)) {
                    $success++;
                }
            }
        } else {
            $success = false;
            EE_Error::add_error(
                esc_html__('Unable to permanently delete any promotions because none were selected.', 'event_espresso'),
                __FILE__,
                __FUNCTION__,
                __LINE__
            );
        }

        if ($success > 0) {
            EE_Error::add_success(
                _n(
                    '1 promotion was successfully deleted.',
                    '%s promotions were successfully deleted.',
                    $success,
                    'event_espresso'
                )
            );
        }
        $this->_redirect_after_action(null, '', '', ['action' => 'default'], true);
    }
    // phpcs:enable


    /**
     * _get_config
     *
     * @access protected
     * @return EE_Promotions_Config
     * @throws EE_Error
     * @throws ReflectionException
     */
    protected function _get_config(): EE_Promotions_Config
    {
        return EED_Promotions::instance()->set_config();
    }


    /**
     * promotions_settings
     *
     * @access protected
     * @throws EE_Error
     * @throws ReflectionException
     */
    protected function _promotions_settings()
    {
        $this->_set_add_edit_form_tags('update_settings');
        $this->_set_publish_post_box_vars('', false, false);
        $this->_template_args['admin_page_content'] = $this->_generate_promo_settings_form()->get_html_and_js();
        $this->display_admin_page_with_sidebar();
    }


    /**
     *    _generate_promo_settings_form
     *
     * @access protected
     * @return EE_Form_Section_Proper
     * @throws EE_Error
     * @throws ReflectionException
     */
    protected function _generate_promo_settings_form(): EE_Form_Section_Proper
    {
        $this->_config = $this->_get_config();

        return new EE_Form_Section_Proper(
            [
                'name'            => 'promotions_settings',
                'html_id'         => 'promotions_settings',
                'html_class'      => 'form-table',
                'layout_strategy' => new EE_Admin_Two_Column_Layout(),
                'subsections'     => apply_filters(
                    'FHEE__Promotions_Admin_Page___generate_promo_settings_form__form_subsections',
                    [
                        'affects_tax'         => new EE_Yes_No_Input(
                            [
                                'default'         => $this->_config->affects_tax(),
                                'html_label_text' => esc_html__('Apply Promotions Before Taxes', 'event_espresso'),
                                'html_help_text'  => sprintf(
                                    esc_html__(
                                        'If set to "Yes" then all promotions will be applied before taxes are calculated,%1$smeaning that the taxes will be applied to the discounted total.%1$s example: $10 total - $5 discount + 10%% tax = $5.50%1$sIf set to "No" then taxes will applied to transaction totals first, followed by promotions.%1$s example: $10 total + 10%% tax - $5 discount = $6.00',
                                        'event_espresso'
                                    ),
                                    '<br />'
                                ),
                            ]
                        ),
                        'banner_template'     => new EE_Select_Input(
                            [
                                'promo-banner-ribbon.template.php'     => esc_html__('Ribbon Banner', 'event_espresso'),
                                'promo-banner-plain-text.template.php' => esc_html__('Plain Text', 'event_espresso'),
                                'none'                                 => esc_html__(
                                    'Do Not Display Promotions',
                                    'event_espresso'
                                ),
                            ],
                            [
                                'default'         => $this->_config->banner_template ?? false,
                                'html_label_text' => esc_html__('Promotion Banners', 'event_espresso'),
                                'html_help_text'  => sprintf(
                                    esc_html__(
                                        'How Non-Code Promotions* are advertised and displayed above the Ticket Selector.%1$s* "Non-Code Promotions" are promotions that do not use a text code, and are applied automatically when all of the promotion\'s qualifying requirements are met (ie: start date, selected events, etc).%2$s',
                                        'event_espresso'
                                    ),
                                    '<br /><span class="smaller-text">',
                                    '</span>'
                                ),
                            ]
                        ),
                        'ribbon_banner_color' => new EE_Select_Input(
                            [
                                'red'       => esc_html__('red', 'event_espresso'),
                                'orange'    => esc_html__('orange', 'event_espresso'),
                                'yellow'    => esc_html__('yellow', 'event_espresso'),
                                'olive'     => esc_html__('olive', 'event_espresso'),
                                'green'     => esc_html__('green', 'event_espresso'),
                                'aqua'      => esc_html__('aqua', 'event_espresso'),
                                'lite-blue' => esc_html__('lite-blue', 'event_espresso'),
                                'blue'      => esc_html__('blue', 'event_espresso'),
                                'violet'    => esc_html__('violet', 'event_espresso'),
                                'purple'    => esc_html__('purple', 'event_espresso'),
                                'pink'      => esc_html__('pink', 'event_espresso'),
                            ],
                            [
                                'default'         => $this->_config->ribbon_banner_color ?? false,
                                'html_label_text' => esc_html__('Ribbon Banner Color', 'event_espresso'),
                                'html_help_text'  => esc_html__(
                                    'If "Ribbon Banner" is selected above, then this determines the color of the ribbon banner.',
                                    'event_espresso'
                                ),
                            ]
                        ),
                        'reset_promotions'    => new EE_Yes_No_Input(
                            [
                                'default'         => 0,
                                'html_label_text' => esc_html__('Reset Promotions Settings?', 'event_espresso'),
                                'html_help_text'  => esc_html__(
                                    'Set to "Yes" and then click "Save" to confirm reset all basic and advanced Event Espresso Promotions settings to their plugin defaults.',
                                    'event_espresso'
                                ),
                            ]
                        ),
                    ]
                ),
            ]
        );
    }


    /**
     *    _update_settings
     *
     * @access protected
     * @throws EE_Error
     * @throws ReflectionException
     */
    protected function _update_settings()
    {
        $count               = 0;
        $this->_config       = $this->_get_config();
        $promo_settings_form = $this->_generate_promo_settings_form();
        if ($promo_settings_form->was_submitted()) {
            // capture form data
            $promo_settings_form->receive_form_submission();
            // validate form data
            if ($promo_settings_form->is_valid()) {
                // grab validated data from form
                $valid_data = $promo_settings_form->valid_data();
                if (isset($valid_data['reset_promotions']) && $valid_data['reset_promotions'] === true) {
                    $this->_config = new EE_Promotions_Config();
                    $count++;
                } else {
                    foreach ($valid_data as $property => $value) {
                        $setter = 'set_' . $property;
                        if (method_exists($this->_config, $setter)) {
                            $this->_config->$setter($value);
                            $count++;
                        } elseif (
                            property_exists($this->_config, $property)
                            && $this->_config->{$property}
                               !== $value
                        ) {
                            $this->_config->$property = $value;
                            $count++;
                        }
                    }
                }
                EE_Registry::instance()->CFG->update_config('addons', 'promotions', $this->_config);
            }
        }
        $this->_redirect_after_action($count, 'Settings', 'updated', ['action' => 'promotions_settings']);
    }


    /**
     *    _usage
     *
     * @access protected
     * @throws EE_Error
     */
    protected function _usage()
    {
        $this->_template_args['admin_page_content'] = EEH_Template::display_template(
            EE_PROMOTIONS_ADMIN_TEMPLATE_PATH . 'promotions_usage_info.template.php',
            [],
            true
        );
        $this->display_admin_page_with_no_sidebar();
    }
}
