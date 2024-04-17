<?php

use EventEspresso\core\domain\services\assets\EspressoLegacyAdminAssetManager;
use EventEspresso\core\services\loaders\LoaderFactory;
use EventEspresso\core\exceptions\InvalidDataTypeException;
use EventEspresso\core\exceptions\InvalidInterfaceException;
use EventEspresso\core\services\payment_methods\gateways\GatewayDataFormatter;
use EventEspresso\core\services\request\DataType;
use EventEspresso\Promotions\core\domain\services\admin\events\editor\ui\PromoCodeLink;

/**
 * Class  EED_Promotions
 *
 * @package     Event Espresso
 * @subpackage  espresso-promotions
 * @author      Brent Christensen
 */
class EED_Promotions extends EED_Module
{
    public static bool $shortcode_active = false;


    /**
     * @return EED_Promotions
     * @throws EE_Error
     * @throws ReflectionException
     */
    public static function instance(): EED_Promotions
    {
        return parent::get_instance(__CLASS__);
    }


    /**
     * set_hooks - for hooking into EE Core, other modules, etc
     *
     * @return void
     */
    public static function set_hooks()
    {
        EE_Config::register_route('promotions', 'EED_Promotions', 'run');
        add_action('wp_enqueue_scripts', ['EED_Promotions', 'translate_js_strings'], 1);
        add_action('wp_enqueue_scripts', ['EED_Promotions', 'enqueue_scripts']);
        add_action(
            'AHEE__ticket_selector_chart__template__before_ticket_selector',
            ['EED_Promotions', 'display_event_promotions_banner']
        );
        add_action(
            'FHEE__EE_Ticket_Selector__process_ticket_selections__before_redirecting_to_checkout',
            ['EED_Promotions', 'auto_process_promotions_in_cart']
        );
        add_filter(
            'FHEE__EE_SPCO_Reg_Step_Payment_Options___display_payment_options__before_payment_options',
            ['EED_Promotions', 'add_promotions_form_inputs']
        );
        // adjust SPCO
        add_filter(
            'FHEE__EE_SPCO_Line_Item_Display_Strategy__item_row__name',
            ['EED_Promotions', 'adjust_SPCO_line_item_display'],
            10,
            2
        );
        add_action(
            'AHEE__EE_System__initialize_last',
            ['EED_Promotions', 'detectPromoCodeLinks']
        );
        add_filter(
            'FHEE__EE_Session__reset_data__session_data_keys_to_reset',
            ['EED_Promotions', 'dontClearPromoCodeSession']
        );
    }


    /**
     * set_hooks_admin - for hooking into EE Admin Core, other modules, etc
     *
     * @return    void
     */
    public static function set_hooks_admin()
    {
        add_filter(
            'FHEE__EE_SPCO_Reg_Step_Payment_Options___display_payment_options__before_payment_options',
            ['EED_Promotions', 'add_promotions_form_inputs']
        );
        add_action(
            'FHEE__EE_Ticket_Selector__process_ticket_selections__before_redirecting_to_checkout',
            ['EED_Promotions', 'auto_process_promotions_in_cart']
        );
        // Enqueue scripts at Transactions page.
        add_action('admin_enqueue_scripts', ['EED_Promotions', 'enqueueAdminScripts']);
        // _get_promotions
        add_action('wp_ajax_espresso_get_promotions', ['EED_Promotions', '_get_promotions']);
        add_action('wp_ajax_nopriv_espresso_get_promotions', ['EED_Promotions', '_get_promotions']);
        // submit_promo_code
        add_action('wp_ajax_espresso_submit_promo_code', ['EED_Promotions', 'submit_promo_code']);
        add_action('wp_ajax_nopriv_espresso_submit_promo_code', ['EED_Promotions', 'submit_promo_code']);
        // submit_txn_promo_code
        add_action('wp_ajax_espresso_submit_txn_promo_code', ['EED_Promotions', 'submitTxnPromoCode']);
        add_action('wp_ajax_nopriv_espresso_submit_txn_promo_code', ['EED_Promotions', 'submitTxnPromoCode']);
        // adjust SPCO
        add_filter(
            'FHEE__EE_SPCO_Line_Item_Display_Strategy__item_row__name',
            ['EED_Promotions', 'adjust_SPCO_line_item_display'],
            10,
            2
        );
        add_filter(
            'FHEE__EE_gateway___line_item_name',
            ['EED_Promotions', 'adjust_promotion_line_item_gateway'],
            10,
            3
        );
        // TXN admin
        add_filter(
            'FHEE__EE_Admin_Transactions_List_Table__column_TXN_total__TXN_total',
            ['EED_Promotions', 'transactions_list_table_total'],
            10,
            2
        );
        add_filter(
            'FHEE__Transactions_Admin_Page___transaction_legend_items__items',
            ['EED_Promotions', 'transactions_list_table_legend'],
            10,
            2
        );
        // the filter got renamed and the old one was deprecated.
        if (
            version_compare(
                espresso_version(),
                '4.9.69.p',
                '>'
            )
        ) {
            $filter_name = 'FHEE__EventEspressoBatchRequest__JobHandlers__RegistrationsReport__reg_csv_array';
        } else {
            $filter_name = 'FHEE__EE_Export__report_registrations__reg_csv_array';
        }
        add_filter(
            $filter_name,
            ['EED_Promotions', 'add_promotions_column_to_reg_csv_report'],
            10,
            2
        );
        // when events are deleted
        add_action(
            'AHEE__EE_Base_Class__delete_permanently__end',
            ['EED_Promotions', 'delete_related_promotion_on_scope_item_delete'],
            10,
            2
        );
        // Display button at transactions actions area.
        add_action(
            'AHEE__txn_admin_details_main_meta_box_txn_details__after_actions_buttons',
            ['EED_Promotions', 'displayApplyDiscountAtTransactions']
        );
        // display promo code link button in EDTR
        add_action(
            'FHEE__EE_Admin_Page___load_page_dependencies__after_load__espresso_events__edit',
            ['EED_Promotions', 'displayPromoCodeLinkButton']
        );
        add_action(
            "AHEE__Single_Page_Checkout___initialize__after_final_verifications",
            ['EED_Promotions', 'applyPromoCodeFromSession'],
        );
        add_filter(
            'FHEE__EE_Session__reset_data__session_data_keys_to_reset',
            ['EED_Promotions', 'dontClearPromoCodeSession']
        );
    }


    /**
     * this configures this module to use the same config as the EE_Promotions class
     *
     * @return EE_Config_Base|EE_Promotions_Config
     */
    public function set_config(): EE_Promotions_Config
    {
        $this->set_config_section('addons');
        $this->set_config_class('EE_Promotions_Config');
        $this->set_config_name('promotions');
        return parent::config();
    }


    /**
     * @return EE_Promotions_Config
     */
    public function config(): EE_Promotions_Config
    {
        if (! $this->_config instanceof EE_Promotions_Config) {
            $this->_config = $this->set_config();
            $this->_config->init();
        }
        return $this->_config;
    }


    /**
     *    run - initial module setup
     *
     * @param WP $WP
     * @return    void
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function run($WP)
    {
        EED_Promotions::instance()->set_config();
    }


    /**
     *        translate_js_strings
     *
     * @return        void
     */
    public static function translate_js_strings()
    {
        EE_Registry::$i18n_js_strings['no_promotions_code'] = esc_html__(
            'Please enter a valid Promotion Code.',
            'event_espresso'
        );
    }


    /**
     *    enqueue_scripts - Load the scripts and css
     *
     * @return    void
     */
    public static function enqueueAdminScripts()
    {
        // Enqueue specific code to Transactions page.
        wp_register_script(
            'eventespresso-txn-promotions-admin',
            EE_PROMOTIONS_URL . 'scripts/txn-promotions.admin.js',
            [],
            EE_PROMOTIONS_VERSION,
            true
        );

        if (EED_Promotions::loadAdminAssets()) {
            // load the assets.
            wp_enqueue_script('eventespresso-txn-promotions-admin');
        }
    }


    /**
     *    enqueue_scripts - Load the scripts and css
     *
     * @return    void
     */
    public static function enqueue_scripts()
    {
        // Check to see if the promotions css file exists in the '/uploads/espresso/' directory
        if (is_readable(EVENT_ESPRESSO_UPLOAD_DIR . 'css/promotions.css')) {
            // This is the url to the css file if available
            wp_register_style(
                'espresso_promotions',
                EVENT_ESPRESSO_UPLOAD_URL . 'css/promotions.css',
                [],
                EE_PROMOTIONS_VERSION
            );
        } else {
            // EE promotions style
            wp_register_style(
                'espresso_promotions',
                EE_PROMOTIONS_URL . 'css/promotions.css',
                [],
                EE_PROMOTIONS_VERSION
            );
        }
        // only load JS if SPCO is active
        if (apply_filters('EED_Single_Page_Checkout__SPCO_active', false)) {
            // promotions script
            wp_register_script(
                'espresso_promotions',
                EE_PROMOTIONS_URL . 'scripts/promotions.js',
                ['single_page_checkout'],
                EE_PROMOTIONS_VERSION,
                true
            );
        }
        if (EED_Promotions::load_assets()) {
            // load JS
            wp_enqueue_style('espresso_promotions');
            wp_enqueue_script('espresso_promotions');
        }
    }


    /**
     * @return bool
     * @throws InvalidArgumentException
     * @throws InvalidDataTypeException
     * @throws InvalidInterfaceException
     * @since 1.0.15.p
     */
    public static function loadAdminAssets(): bool
    {
        if (is_admin()) {
            /** @var EventEspresso\core\services\request\RequestInterface $request */
            $request = LoaderFactory::getLoader()->getShared(
                'EventEspresso\core\services\request\RequestInterface'
            );
            return $request->getRequestParam('page') === 'espresso_transactions'
                && $request->getRequestParam('TXN_ID', 0, DataType::INT) !== 0;
        }
        return false;
    }


    /**
     *    load_assets
     *
     * @return        bool
     */
    public static function load_assets(): bool
    {
        return
            ! is_admin()
            && (
                EED_Promotions::$shortcode_active
                || is_singular('espresso_events')
                || is_post_type_archive('espresso_events')
                || apply_filters('EED_Single_Page_Checkout__SPCO_active', false)
            );
    }



    /********************************** TXN ADMIN PAGES ***********************************/


    /**
     *    transactions_list_table_total
     *
     * @param string         $TXN_total
     * @param EE_Transaction $transaction
     * @return    string
     * @throws EE_Error
     * @throws ReflectionException
     */
    public static function transactions_list_table_total(string $TXN_total, EE_Transaction $transaction): string
    {
        $promotion_line_items = $transaction->line_items([['OBJ_type' => 'Promotion']]);
        $promotion_line_item  = reset($promotion_line_items);
        if ($promotion_line_item instanceof EE_Line_Item) {
            $edit_link = EEH_URL::add_query_args_and_nonce(
                ['action' => 'edit', 'PRO_ID' => $promotion_line_item->OBJ_ID()],
                EE_PROMOTIONS_ADMIN_URL
            );
            $TXN_total = '
            <a href="' . $edit_link . '"
               title="' . esc_html__(
                    'A Promotion was redeemed during this Transaction. Click to View Promotion',
                    'event_espresso'
                ) . '"
            >
                <sup><span class="dashicons dashicons-tag green-icon ee-icon-size-12"></span></sup>' . $TXN_total . '
            </a>';
        }
        return $TXN_total;
    }


    /**
     *    transactions_list_table_legend
     *
     * @param array $legend_items
     * @return    array
     */
    public static function transactions_list_table_legend(array $legend_items = []): array
    {
        $legend_items['promotion_redeemed'] = [
            'class' => 'dashicons dashicons-tag green-icon ee-icon-size-12',
            'desc'  => esc_html__('Promotion was redeemed during Transaction', 'event_espresso'),
        ];
        return $legend_items;
    }



    /********************************** DISPLAY PROMOTIONS  ***********************************/
    /**
     *    display_promotions
     *
     * @param array $attributes
     * @return    string
     * @throws EE_Error
     * @throws ReflectionException
     */
    public static function display_promotions(array $attributes = []): string
    {
        EED_Promotions::instance()->set_config();
        return EED_Promotions::instance()->_display_promotions($attributes);
    }


    /**
     *    _display_promotions
     *
     * @param array $attributes
     * @return    string
     * @throws EE_Error
     * @throws ReflectionException
     */
    private function _display_promotions(array $attributes = []): string
    {
        $html = '';
        /** @type EEM_Promotion $EEM_Promotion */
        $EEM_Promotion     = EE_Registry::instance()->load_model('Promotion');
        $active_promotions = $EEM_Promotion->get_all_active_codeless_promotions($attributes);
        foreach ($active_promotions as $promotion) {
            if ($promotion instanceof EE_Promotion) {
                $scope_objects = $promotion->get_objects_promo_applies_to();
                $html          .= EEH_Template::locate_template(
                    apply_filters(
                        'FHEE__EED_Promotions___display_promotions__banner_template',
                        EE_PROMOTIONS_PATH . 'templates/upcoming-promotions-grid.template.php'
                    ),
                    [
                        'PRO_ID'          => $promotion->ID(),
                        'promo_bg_color'  => ! empty($this->config()->ribbon_banner_color)
                            ? $this->config()->ribbon_banner_color
                            : 'lite-blue',        // lite-blue      blue    pink    green       red
                        'promo_header'    => $promotion->name(),
                        'promo_desc'      => $promotion->description() !== ''
                            ? $promotion->description() . '<br />'
                            : '',
                        'promo_amount'    => $promotion->pretty_amount(),
                        'promo_dates'     => $promotion->promotion_date_range(),
                        'promo_scopes'    => $promotion->get_promo_applies_to_link_array($scope_objects),
                        'promo_is_global' => $promotion->is_global(),
                    ]
                );
            }
        }
        return $html;
    }


    /**
     *    displayApplyDiscountAtTransactions
     *
     * @param bool $can_edit_payments Flag to tell us if user can edit payments.
     * @return    void
     */
    public static function displayApplyDiscountAtTransactions(bool $can_edit_payments)
    {
        if (! $can_edit_payments) {
            return;
        }

        echo EEH_Template::locate_template(
            apply_filters(
                'FHEE__EED_Promotions__displayApplyDiscountAtTransactions',
                EE_PROMOTIONS_PATH . 'templates/txn-apply-discount.template.php'
            )
        );
    }



    /********************************** DISPLAY PROMOTIONS BANNER ***********************************/


    /**
     *    display_promotions_banner
     *
     * @param EE_Event $event
     * @return    void
     * @throws EE_Error
     * @throws ReflectionException
     */
    public static function display_event_promotions_banner(EE_Event $event)
    {
        EED_Promotions::instance()->set_config();
        EED_Promotions::instance()->_display_event_promotions_banner($event);
    }


    /**
     * @param EE_Event|null $event
     * @return    void
     * @throws EE_Error
     * @throws ReflectionException
     */
    private function _display_event_promotions_banner(?EE_Event $event)
    {
        if ($event instanceof EE_Event) {
            $banner_text = [];
            /** @type EEM_Promotion $EEM_Promotion */
            $EEM_Promotion     = EE_Registry::instance()->load_model('Promotion');
            $active_promotions = $EEM_Promotion->get_all_active_codeless_promotions();
            foreach ($active_promotions as $promotion) {
                if ($promotion instanceof EE_Promotion) {
                    // get all promotion objects that can still be redeemed
                    $redeemable_scope_promos = $promotion->scope_obj()->get_redeemable_scope_promos(
                        $promotion,
                        true,
                        [$event]
                    );
                    foreach ($redeemable_scope_promos as $scope => $promo_obj_IDs) {
                        if (
                            $scope === EE_Promotion_Scope::SCOPE_EVENT
                            && $promotion->description() !== ''
                            && in_array($event->ID(), $promo_obj_IDs)
                        ) {
                            $banner_text[] = $promotion->description();
                        }
                    }
                }
            }
            if (! empty($banner_text) && ! empty($this->config()->banner_template)) {
                EEH_Template::locate_template(
                    apply_filters(
                        'FHEE__EED_Promotions___display_event_promotions_banner__banner_template',
                        EE_PROMOTIONS_PATH . 'templates' . DS . $this->config()->banner_template
                    ),
                    [
                        'EVT_ID'        => $event->ID(),
                        'banner_header' => apply_filters(
                            'FHEE__EED_Promotions___display_event_promotions_banner__banner_header',
                            esc_html__('Current Promotions', 'event_espresso')
                        ),
                        'banner_text'   => implode('<div class="ee-promo-separator-dv">+</div>', $banner_text),
                        'ribbon_color'  => ! empty($this->config()->ribbon_banner_color)
                            ? $this->config()->ribbon_banner_color
                            : 'lite-blue',        // lite-blue       blue        pink    green       red
                    ],
                    true,
                    false
                );
            }
        }
    }



    /********************************** AUTO PROCESS PROMOTIONS IN CART ***********************************/
    /**
     * @param EE_Cart $cart
     * @return    void
     * @throws EE_Error
     * @throws ReflectionException
     */
    public static function auto_process_promotions_in_cart(EE_Cart $cart)
    {
        EED_Promotions::instance()->set_config();
        EED_Promotions::instance()->_auto_process_promotions_in_cart($cart);
    }


    /**
     *    _auto_process_promotions_in_cart
     *
     * @param EE_Cart $cart
     * @return    void
     * @throws EE_Error
     * @throws ReflectionException
     */
    private function _auto_process_promotions_in_cart(EE_Cart $cart)
    {
        /** @type EEM_Promotion $EEM_Promotion */
        $EEM_Promotion     = EE_Registry::instance()->load_model('Promotion');
        $active_promotions = $EEM_Promotion->get_all_active_codeless_promotions();
        $grand_total = $cart->get_grand_total();
        $grand_total->recalculate_total_including_taxes();
        foreach ($active_promotions as $promotion) {
            if ($promotion instanceof EE_Promotion) {
                // determine if the promotion can be applied to an item in the current cart
                $applicable_items = $this->get_applicable_items($promotion, $cart);
                // add line item
                if (
                    ! empty($applicable_items)
                    && $this->generate_promotion_line_items(
                        $promotion,
                        $applicable_items,
                        $this->config()->affects_tax()
                    )
                ) {
                    $cart->get_grand_total()->recalculate_total_including_taxes();
                    $cart->save_cart(false);
                }
            }
        }
    }



    /********************************** ADD PROMOTIONS FORM INPUTS ***********************************/
    /**
     *    add_promotions_form_inputs
     *
     * @param EE_Form_Section_Proper $before_payment_options
     * @return    EE_Form_Section_Proper
     * @throws EE_Error
     * @throws ReflectionException
     */
    public static function add_promotions_form_inputs(
        EE_Form_Section_Proper $before_payment_options
    ): EE_Form_Section_Proper {
        EED_Promotions::instance()->set_config();
        return EED_Promotions::instance()->_add_promotions_form_inputs($before_payment_options);
    }


    /**
     *    hasApplicablePromotionsAtCart
     *
     * @return bool
     * @throws EE_Error
     * @throws ReflectionException
     */
    private function hasApplicablePromotionsAtCart(): bool
    {
        // get current Cart instance to get events from.
        $cart = EE_Registry::instance()->SSN->cart();
        // if we didn't get an instance ofEE_Cart from SSN, try pulling it from EE_Checkout.
        if (! $cart instanceof EE_Cart) {
            $cart = EE_Registry::instance()->SSN->checkout()->cart;
        }
        if ($cart instanceof EE_Cart) {
            // get all events.
            $events = $this->get_events_from_cart($cart);
            // if we got events...
            if (! empty($events)) {
                /** @var EEM_Promotion $EEM_Promotion */
                $EEM_Promotion = EE_Registry::instance()->load_model('Promotion');
                // check if any promotions apply to the events or any global promotions.
                $active_promotions = $EEM_Promotion->getAllActiveCodePromotions(
                    [
                        [
                            'PRO_scope' => EE_Promotion_Scope::SCOPE_EVENT,
                            'OR'        => [
                                'Promotion_Object.OBJ_ID' => [
                                    'in',
                                    array_keys(
                                        $events
                                    ),
                                ],
                                'PRO_global'              => true,
                            ],
                        ],
                        'limit' => 1,
                    ]
                );

                return ! empty($active_promotions);
            }
        }
        return false;
    }


    /**
     *    _add_promotions_form_inputs
     *
     * @param EE_Form_Section_Proper $before_payment_options
     * @return        EE_Form_Section_Proper
     * @throws EE_Error
     * @throws ReflectionException
     */
    private function _add_promotions_form_inputs(EE_Form_Section_Proper $before_payment_options): EE_Form_Section_Proper
    {
        // flag controlling either active promos should be checked.
        $check_for_applicable_promotions = apply_filters(
            'FHEE__EED_Promotions___add_promotions_form_inputs__checkForApplicablePromotions',
            true
        );

        if ($check_for_applicable_promotions) {
            // checks if any promotion applies to current cart.
            $has_applicable_promotions = $this->hasApplicablePromotionsAtCart();

            // if no active promotions are found, we do not display the section field.
            if (! $has_applicable_promotions) {
                return $before_payment_options;
            }
        }

        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

        $singular_label = isset($this->config()->label->singular)
            ?
            $this->config()->label->singular
            :
            esc_html__('Promotion Code', 'event_espresso');

        $before_payment_options->add_subsections(
            [
                'promotions_form' => new EE_Form_Section_Proper(
                    [
                        'layout_strategy' => new EE_No_Layout(['use_break_tags' => false]),
                        'subsections'     => [
                            'ee_promotion_code_input_wrap_open' => new EE_Form_Section_HTML(
                                EEH_HTML::h5(
                                    apply_filters(
                                        'FHEE__EED_Promotions___add_promotions_form_inputs__ee_promotion_code_input__html_label_text',
                                        $singular_label
                                    ),
                                    '',
                                    'ee-promotion-code-header',
                                    'margin-bottom: .5rem;'
                                )
                                . EEH_HTML::div('', '', 'ee-promotion-code-input__wrapper')
                            ),
                            'ee_promotion_code_input'  => new EE_Text_Input(
                                [
                                    'default'          => '',
                                    'html_id'          => 'ee-promotion-code-input',
                                    'html_class'       => 'ee-promotion-code-input ee-reg-qstn',
                                    'html_name'        => 'ee_promotion_code_input',
                                    'html_label_text'  => $singular_label,
                                    'html_label_class' => 'screen-reader-text',
                                ]
                            ),
                            'ee_promotion_code_submit' => new EE_Button_Input(
                                [
                                    'html_id'               => 'ee-promotion-code-submit',
                                    'html_name'             => 'ee_promotion_code_submit',
                                    'button_content'        => apply_filters(
                                        'FHEE__EED_Promotions___add_promotions_form_inputs__ee_promotion_code_submit__default',
                                        sprintf(esc_html__('Submit %s', 'event_espresso'), $singular_label)
                                    ),
                                    'other_html_attributes' => 'type="button"',
                                    'no_label'  => true,
                                ]
                            ),
                            'ee_promotion_code_input_wrap_close' => new EE_Form_Section_HTML(EEH_HTML::divx()),
                            'ee_promotion_code_header' => new EE_Form_Section_HTML(
                                EEH_HTML::div(' ', '', 'clear-float')
                            ),
                        ],
                    ]
                ),
            ]
        );
        return $before_payment_options;
    }



    /********************************** SUBMIT PROMO CODE ***********************************/
    /**
     *    submit_promo_code
     *
     * @return    void
     * @throws EE_Error
     * @throws ReflectionException
     */
    public static function submit_promo_code()
    {
        EED_Promotions::instance()->set_config();
        EED_Promotions::instance()->submitPromoCode();
    }


    /**
     * @return void
     * @throws EE_Error
     * @throws ReflectionException
     */
    private function submitPromoCode(bool $process_ajax = true)
    {
        // get the EE_Cart object being used for the current transaction
        /** @type EE_Cart $cart */
        $cart = EE_Registry::instance()->SSN->cart();
        if (! $cart instanceof EE_Cart) {
            $this->processInvalidCart();
        }
        $return_data = [];
        $grand_total = $cart->get_grand_total();
        // and make sure the model cache is
        $grand_total->get_model()->refresh_entity_map_with(
            $grand_total->ID(),
            $grand_total
        );

        $promotion = $this->get_promotion_details_from_request();
        if ($promotion instanceof EE_Promotion) {
            // determine if the promotion can be applied to an item in the current cart
            $applicable_items = $this->get_applicable_items($promotion, $cart, false, true);
            if (! empty($applicable_items)) {
                // add line item
                if (
                    $this->generate_promotion_line_items(
                        $promotion,
                        $applicable_items,
                        $this->config()->affects_tax()
                    )
                ) {
                    // ensure cart totals have been recalculated and saved
                    $grand_total->recalculate_total_including_taxes();
                    $grand_total->save_this_and_descendants();

                    /** @type EE_Registration_Processor $registration_processor */
                    $registration_processor = EE_Registry::instance()->load_class('Registration_Processor');
                    $registration_processor->update_registration_final_prices($grand_total->transaction());
                    $cart->save_cart(false);
                    $return_data            = $this->_get_payment_info($cart);
                    $return_data['success'] = $promotion->accept_message();
                    if ($process_ajax) {
                        EED_Single_Page_Checkout::update_checkout();
                    }
                } else {
                    EE_Error::add_attention($promotion->decline_message(), __FILE__, __FUNCTION__, __LINE__);
                }
            }
        }
        if ($process_ajax) {
            $this->generate_JSON_response($return_data);
        }
    }


    /********************************** SUBMIT TRANSACTION PROMO CODE ***********************************/
    /**
     *    submitTxnPromoCode
     *
     * @return    void
     * @throws EE_Error
     * @throws ReflectionException
     */
    public static function submitTxnPromoCode()
    {
        EED_Promotions::instance()->set_config();
        EED_Promotions::instance()->applyPromoCodeToTransaction();
    }


    /**
     * @return void
     * @throws EE_Error
     * @throws ReflectionException
     */
    private function applyPromoCodeToTransaction()
    {
        $return_data = [];
        $promotion   = $this->get_promotion_details_from_request();

        if ($promotion instanceof EE_Promotion) {
            $request = LoaderFactory::getLoader()->getShared(
                'EventEspresso\core\services\request\RequestInterface'
            );
            // get the current transaction.
            $transaction_id = $request->getRequestParam('txn_id', 0, DataType::INT);
            /** @var EEM_Transaction $EEM_Transaction */
            $EEM_Transaction = EE_Registry::instance()->load_model('Transaction');
            /** @var EE_Transaction $transaction */
            $transaction = $EEM_Transaction->get_one_by_ID($transaction_id);

            // Determine if the promotion can be applied to an item in the current txn.
            $applicable_items = $this->getApplicableItemsFromTransaction($promotion, $transaction);
            if (! empty($applicable_items)) {
                // add line item
                if (
                    $this->generate_promotion_line_items(
                        $promotion,
                        $applicable_items,
                        $this->config()->affects_tax()
                    )
                ) {
                    $success = $transaction->recalculateLineItems();

                    /** @type EE_Registration_Processor $registration_processor */
                    $registration_processor = EE_Registry::instance()->load_class('Registration_Processor');
                    $registration_processor->update_registration_final_prices($transaction);
                    // Add success message.
                    if ($success) {
                        $return_data['success'] = esc_html__(
                            'Discount applied successfully!',
                            'event_espresso'
                        );
                    } else {
                        // recalculateLineItems failed.
                        EE_Error::add_attention(
                            esc_html__(
                                'Recalculating line items failed, please re-check transaction total after the page reloads.',
                                'event_espresso'
                            ),
                            __FILE__,
                            __FUNCTION__,
                            __LINE__
                        );
                    }
                } else {
                    $return_data['warning'] = $promotion->decline_message();
                }
            }
        } else {
            $return_data['error'] = esc_html__(
                'Sorry, but the discount code could not be applied because the promotion could not be retrieved.',
                'event_espresso'
            );
        }
        $this->generate_JSON_response($return_data);
    }


    /**
     *    get_promotion_details_from_request
     *
     * @param string $promo_code
     * @return    EE_Promotion
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function get_promotion_details_from_request(string $promo_code = ''): ?EE_Promotion
    {
        $request = EED_Promotions::getRequest();
        // get promo code from request or use incoming default value
        $promo_code = $request->getRequestParam('promo_code', $promo_code);
        /** @type EEM_Promotion $EEM_Promotion */
        $EEM_Promotion = EE_Registry::instance()->load_model('Promotion');
        $promo = $EEM_Promotion->get_promotion_details_via_code($promo_code);
        if ($promo instanceof EE_Promotion) {
            return $promo;
        }
        EE_Error::add_attention(
            sprintf(
                apply_filters(
                    'FHEE__EED_Promotions__get_promotion_details_from_request__invalid_promotion_notice',
                    esc_html__(
                        'We\'re sorry, but the %1$s "%2$s" appears to be invalid.%3$sYou are welcome to try a different %1$s or to try this one again to ensure it was entered correctly.',
                        'event_espresso'
                    )
                ),
                strtolower($this->config()->label->singular),
                $promo_code,
                '<br />'
            ),
            __FILE__,
            __FUNCTION__,
            __LINE__
        );
        return null;
    }


    /**
     *    getApplicableItemsFromTransaction
     *    determine if the promotion has global uses left and can be applied to a valid item in the transaction
     *
     * @param EE_Promotion|null $promotion
     * @param EE_Transaction    $transaction
     * @return EE_Line_Item[]
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function getApplicableItemsFromTransaction(
        ?EE_Promotion $promotion,
        EE_Transaction $transaction
    ): array {
        $applicable_items = [];
        // verify EE_Promotion
        if ($promotion instanceof EE_Promotion) {
            // get events from transaction.
            $events = $this->getEventsFromTransaction($transaction);

            // get all promotion objects that can still be redeemed
            $redeemable_scope_promos = $promotion->scope_obj()->get_redeemable_scope_promos(
                $promotion,
                true,
                $events
            );

            // then find line items in the cart that match the above
            $applicable_items = $promotion->scope_obj()->get_object_line_items_from_cart(
                $transaction->total_line_item(),
                $redeemable_scope_promos
            );

            /**
             * Filters the $applicable_items array containing all the line items that the promotion applies to
             *
             * @param array          $applicable_items
             * @param EE_Promotion   $promotion
             * @param array          $redeemable_scope_promos multidimensional array with mixed values
             * @param EE_Event[]     $events
             * @param EE_Transaction $transaction
             */
            $applicable_items = apply_filters(
                'FHEE__EED_Promotions__getApplicableItemsFromTransaction__applicable_items',
                $applicable_items,
                $promotion,
                $redeemable_scope_promos,
                $events,
                $transaction
            );
        }

        return $applicable_items;
    }


    /**
     *    validate_promotion
     *    determine if the promotion has global uses left and can be applied to a valid item in the current cart
     *
     * @param EE_Promotion|null $promotion
     * @param EE_Cart           $cart
     * @param bool              $suppress_notices
     * @param bool              $get_events
     * @return EE_Line_Item[]
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function get_applicable_items(
        ?EE_Promotion $promotion,
        EE_Cart $cart,
        bool $suppress_notices = true,
        bool $get_events = false
    ): array {
        $applicable_items = [];
        // verify EE_Promotion
        if ($promotion instanceof EE_Promotion) {
            $events = $get_events ? $this->get_events_from_cart($cart) : [];
            // get all promotion objects that can still be redeemed
            $redeemable_scope_promos = $promotion->scope_obj()->get_redeemable_scope_promos(
                $promotion,
                true,
                $events
            );
            // then find line items in the cart that match the above
            $applicable_items = $promotion->scope_obj()->get_object_line_items_from_cart(
                $cart->get_grand_total(),
                $redeemable_scope_promos
            );
            /**
             * Filters the $applicable_items array containing all the line items that the promotion applies to
             *
             * @param array        $applicable_items
             * @param EE_Promotion $promotion
             * @param array        $redeemable_scope_promos multidimensional array with mixed values
             * @param EE_Event[]   $events
             * @param EE_Cart      $cart
             */
            $applicable_items = apply_filters(
                'FHEE__EED_Promotions__get_applicable_items__applicable_items',
                $applicable_items,
                $promotion,
                $redeemable_scope_promos,
                $events,
                $cart
            );
        }
        if (empty($applicable_items) && ! $suppress_notices) {
            EE_Error::add_attention(
                sprintf(
                    apply_filters(
                        'FHEE__EED_Promotions__get_applicable_items__no_applicable_items_notice',
                        esc_html__(
                            'We\'re sorry, but the %1$s "%2$s" could not be applied to any %4$s.%3$sYou are welcome to try a different %1$s or to try this one again to ensure it was entered correctly.',
                            'event_espresso'
                        )
                    ),
                    strtolower($this->config()->label->singular),
                    $promotion->code(),
                    '<br />',
                    $promotion->scope_obj()->label->plural
                ),
                __FILE__,
                __FUNCTION__,
                __LINE__
            );
        }
        return $applicable_items;
    }


    /**
     * generate_promotion_line_items
     * if the promotion in question has not already been redeemed for the given line item,
     * then have a line item generated by the promotion scope object, and increment
     *
     * @param EE_Cart $cart
     * @return    EE_Event[]
     * @throws EE_Error
     * @throws ReflectionException
     * @since     1.0.4
     */
    public function get_events_from_cart(EE_Cart $cart): array
    {
        $event_line_items = EEH_Line_Item::get_event_subtotals($cart->get_grand_total());
        $events           = [];
        foreach ($event_line_items as $event_line_item) {
            if ($event_line_item instanceof EE_Line_Item) {
                $events[ $event_line_item->OBJ_ID() ] = $event_line_item->get_object();
            }
        }
        return $events;
    }


    /**
     * Get events from a specific Transaction
     *
     * @param EE_Transaction $transaction
     * @return    EE_Event[]
     * @throws EE_Error
     * @throws ReflectionException
     * @since     1.0.4
     */
    public function getEventsFromTransaction(EE_Transaction $transaction): array
    {
        $event_line_items = EEH_Line_Item::get_event_subtotals($transaction->total_line_item());
        $events           = [];
        foreach ($event_line_items as $event_line_item) {
            if ($event_line_item instanceof EE_Line_Item) {
                $events[ $event_line_item->OBJ_ID() ] = $event_line_item->get_object();
            }
        }
        return $events;
    }


    /**
     * generate_promotion_line_items
     * if the promotion in question has not already been redeemed for the given line item,
     * then have a line item generated by the promotion scope object, and increment
     *
     * @param EE_Promotion|null $promotion
     * @param EE_Line_Item[]    $applicable_items
     * @param bool              $affects_tax
     * @return bool
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function generate_promotion_line_items(
        ?EE_Promotion $promotion,
        array $applicable_items = [],
        bool $affects_tax = false
    ): bool {
        if (empty($applicable_items)) {
            return false;
        }
        $success = true;
        foreach ($applicable_items as $applicable_item) {
            if (
                $applicable_item instanceof EE_Line_Item
                && $this->verify_no_existing_promotion_line_items($applicable_item, $promotion)
                && $this->verify_no_exclusive_promotions_combined($applicable_item, $promotion)
                && $promotion->global_uses_left()
            ) {
                $success = $promotion->scope_obj()->calculateAndApplyPromotion(
                    [$this, 'add_promotion_line_item'],
                    $applicable_item,
                    $promotion,
                    $affects_tax
                )
                    ? $success
                    : false;
            }
        }
        return $success;
    }


    /**
     * get_redeemable_scope_promos
     * searches the cart for any items that this promotion applies to
     *
     * @param EE_Line_Item $parent_line_item
     * @param EE_Promotion $promotion
     * @return boolean
     * @throws EE_Error
     * @throws ReflectionException
     * @since   1.0.0
     */
    public function verify_no_existing_promotion_line_items(
        EE_Line_Item $parent_line_item,
        EE_Promotion $promotion
    ): bool {
        /** @type EEM_Line_Item $EEM_Line_Item */
        $EEM_Line_Item = EE_Registry::instance()->load_model('Line_Item');
        // check promotion hasn't already been applied
        $existing_promotion_line_item = $EEM_Line_Item->get_existing_promotion_line_item(
            $parent_line_item,
            $promotion
        );
        if (! $existing_promotion_line_item instanceof EE_Line_Item) {
            // check children
            $children = $parent_line_item->children();
            if (! empty($children)) {
                foreach ($children as $child) {
                    if (! $this->verify_no_existing_promotion_line_items($child, $promotion)) {
                        return false;
                    }
                }
            }
            return true;
        }
        if ($promotion->code()) {
            EE_Error::add_attention(
                sprintf(
                    apply_filters(
                        'FHEE__EED_Promotions__verify_no_existing_promotion_line_items__existing_promotion_code_notice',
                        esc_html__(
                            'We\'re sorry, but the "%1$s" %4$s has already been applied to the "%2$s" %3$s, and can not be applied more than once per %3$s.',
                            'event_espresso'
                        )
                    ),
                    $existing_promotion_line_item->name(),
                    $parent_line_item->name(),
                    $parent_line_item->OBJ_type_i18n(),
                    $existing_promotion_line_item->OBJ_type_i18n()
                ),
                __FILE__,
                __FUNCTION__,
                __LINE__
            );
        }
        return false;
    }


    /**
     * verify_no_exclusive_promotions_combined
     * verifies that no exclusive promotions are being combined
     *
     * @param EE_Line_Item $parent_line_item
     * @param EE_Promotion $promotion
     * @return bool
     * @throws EE_Error
     * @throws ReflectionException
     * @since   1.0.0
     */
    public function verify_no_exclusive_promotions_combined(
        EE_Line_Item $parent_line_item,
        EE_Promotion $promotion
    ): bool {
        /** @type EEM_Line_Item $EEM_Line_Item */
        $EEM_Line_Item = EE_Registry::instance()->load_model('Line_Item');
        // get all existing promotions that have already been added to the cart
        $existing_promotion_line_items = $EEM_Line_Item->get_all_promotion_line_items($parent_line_item);
        if (empty($existing_promotion_line_items)) {
            // check children
            $children = $parent_line_item->children();
            if (! empty($children)) {
                foreach ($children as $child) {
                    if (! $this->verify_no_exclusive_promotions_combined($child, $promotion)) {
                        return false;
                    }
                }
            }
            return true;
        }
        $promo_config = $this->config();
        // can't apply this new promotion if it is exclusive
        if ($promotion->is_exclusive()) {
            $promo_name               = $promotion->name();
            $promo_code               = $promotion->code();
            $code_and_name_dont_match = $promo_code !== $promo_name;
            EE_Error::add_attention(
                sprintf(
                    apply_filters(
                        'FHEE__EED_Promotions__verify_no_exclusive_promotions_combined__new_promotion_is_exclusive_notice',
                        esc_html__(
                            'We\'re sorry, but %3$s have already been added to the cart and the "%1$s%2$s" promotion can not be combined with others.',
                            'event_espresso'
                        )
                    ),
                    $promo_code && $promo_name && $code_and_name_dont_match
                        ? $promo_code . ' : '
                        : $promo_code,
                    $promo_name && $code_and_name_dont_match
                        ? $promo_name
                        : '',
                    strtolower($promo_config->label->plural)
                ),
                __FILE__,
                __FUNCTION__,
                __LINE__
            );
            return false;
        }
        // new promotion is not exclusive...
        // so now determine if any existing ones are
        foreach ($existing_promotion_line_items as $existing_promotion_line_item) {
            if ($existing_promotion_line_item instanceof EE_Line_Item) {
                $existing_promotion = $this->get_promotion_from_line_item($existing_promotion_line_item);
                if ($existing_promotion instanceof EE_Promotion && $existing_promotion->is_exclusive()) {
                    $promo_name               = $existing_promotion->name();
                    $promo_code               = $existing_promotion->code();
                    $code_and_name_dont_match = $promo_code !== $promo_name;
                    EE_Error::add_attention(
                        sprintf(
                            apply_filters(
                                'FHEE__EED_Promotions__verify_no_exclusive_promotions_combined__existing_promotion_is_exclusive_notice',
                                esc_html__(
                                    'We\'re sorry, but the "%1$s%2$s" %3$s has already been added to the cart and can not be combined with others.',
                                    'event_espresso'
                                )
                            ),
                            $promo_code && $promo_name && $code_and_name_dont_match
                                ? $promo_code . ' : '
                                : $promo_code,
                            $promo_name && $code_and_name_dont_match
                                ? $promo_name
                                : '',
                            strtolower($promo_config->label->singular)
                        ),
                        __FILE__,
                        __FUNCTION__,
                        __LINE__
                    );
                    return false;
                }
            }
        }
        // we found one or more existing promos but they weren't exclusive
        return true;
    }


    /**
     *    get_promotion_from_line_item
     *
     * @param EE_Line_Item $promotion_line_item the line item representing the new promotion
     * @return    EE_Promotion | null
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function get_promotion_from_line_item(EE_Line_Item $promotion_line_item): ?EE_Promotion
    {
        $promotion = EEM_Promotion::instance()->get_one_by_ID($promotion_line_item->OBJ_ID());
        if (! $promotion instanceof EE_Promotion) {
            EE_Error::add_error(
                sprintf(
                    apply_filters(
                        'FHEE__EED_Promotions__get_promotion_from_line_item__invalid_promotion_notice',
                        esc_html__(
                            'We\'re sorry, but the %1$s could not be applied because information pertaining to it could not be retrieved from the database.',
                            'event_espresso'
                        )
                    ),
                    strtolower($this->config()->label->singular)
                ),
                __FILE__,
                __FUNCTION__,
                __LINE__
            );
            return null;
        }
        return $promotion;
    }


    /**
     *    add_promotion_line_item
     *
     * @param EE_Line_Item $parent_line_item    the line item that the new promotion was added to as a child line item
     * @param EE_Line_Item $promotion_line_item the line item representing the new promotion
     * @param EE_Promotion $promotion           the promotion object that the line item was created for
     * @param int|null     $promo_scope_obj_ID  ID for the promotion scope object parent line item
     * @return    boolean
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function add_promotion_line_item(
        EE_Line_Item $parent_line_item,
        EE_Line_Item $promotion_line_item,
        EE_Promotion $promotion,
        ?int $promo_scope_obj_ID
    ): bool {
        // add it to the cart
        if ($parent_line_item->add_child_line_item($promotion_line_item)) {
            if (
                /**
                 * Filter switch for bypassing the incrementation of promotion scope uses
                 *
                 * @param boolean      $bypass_increment_promotion_scope_uses
                 * @param EE_Line_Item $parent_line_item
                 * @param EE_Promotion $promotion
                 * @param EE_Line_Item $promotion_line_item
                 */
            apply_filters(
                'FHEE__EED_Promotions__add_promotion_line_item__bypass_increment_promotion_scope_uses',
                false,
                $parent_line_item,
                $promotion,
                $promotion_line_item
            )
            ) {
                return true;
            }
            $promo_scope_obj_ID = $promo_scope_obj_ID ?? $parent_line_item->OBJ_ID();
            try {
                if ($promotion->scope_obj()->increment_promotion_scope_uses($promotion, $promo_scope_obj_ID)) {
                    return true;
                }
            } catch (Exception $e) {
                EE_Error::add_error($e->getMessage(), __FILE__, __FUNCTION__, __LINE__);
            }
        }
        return false;
    }


    /**
     *    _get_payment_info
     *
     * @param EE_Cart $cart
     * @return    array
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function _get_payment_info(EE_Cart $cart): array
    {
        EEH_Autoloader::register_line_item_filter_autoloaders();
        $line_item_filter_processor = new EE_Line_Item_Filter_Processor(
            apply_filters(
                'FHEE__SPCO__EE_Line_Item_Filter_Collection',
                new EE_Line_Item_Filter_Collection()
            ),
            $cart->get_grand_total()
        );
        /** @var EE_Line_Item $filtered_line_item_tree */
        $filtered_line_item_tree = $line_item_filter_processor->process();
        // autoload Line_Item_Display classes
        EEH_Autoloader::register_line_item_display_autoloaders();
        // $this->checkout->line_item_filters();
        $Line_Item_Display = new EE_Line_Item_Display('spco');
        return [
            'payment_info' => $Line_Item_Display->display_line_item(
                $filtered_line_item_tree,
                ['display_event_row' => true]
            ),
            'cart_total'   => $filtered_line_item_tree->total(),
        ];
    }


    /**
     *    generate_JSON_response
     *        allows you to simply echo or print an EE_SPCO_JSON_Response object to produce a JSON encoded string
     *        ie: $json_response = new EE_SPCO_JSON_Response();
     *        echo $json_response;
     *
     * @param array $return_data
     * @return    void
     */
    public function generate_JSON_response(array $return_data = [])
    {
        $JSON_response = [];
        // grab notices
        $notices = EE_Error::get_notices(false);
        // add notices to JSON response, but only if they exist
        if (isset($notices['attention'])) {
            $JSON_response['attention'] = $notices['attention'];
        }
        if (isset($notices['errors'])) {
            $JSON_response['errors'] = $notices['errors'];
        }
        if (isset($notices['success'])) {
            $JSON_response['success'] = $notices['success'];
        }
        if (empty($JSON_response) && empty($return_data)) {
            $JSON_response['errors'] = sprintf(
                esc_html__(
                    'The %1$s entered could not be processed for an unknown reason.%2$sYou are welcome to try a different %1$s or to try this one again to ensure it was entered correctly.',
                    'event_espresso'
                ),
                strtolower($this->config()->label->singular),
                '<br />'
            );
        }
        // add return_data array to main JSON response array, IF it contains anything
        $JSON_response['return_data'] = $return_data;
        // filter final array
        $JSON_response = apply_filters('FHEE__EED_Promotions__generate_JSON_response__JSON_response', $JSON_response);
        // return encoded array
        wp_send_json($JSON_response);
    }


    /**
     *    adjust_SPCO_line_item_display
     *   allows promotions to adjust the line item name in EE_SPCO_Line_Item_Display_Strategy
     *
     * @param string       $line_item_name
     * @param EE_Line_Item $line_item
     * @return string
     * @throws EE_Error
     * @throws ReflectionException
     */
    public static function adjust_SPCO_line_item_display(string $line_item_name, EE_Line_Item $line_item): string
    {
        // is this a promotion ?
        if ($line_item->OBJ_type() === 'Promotion') {
            $line_item_name = sprintf(esc_html__('Discount: %1$s', 'event_espresso'), $line_item->name());
        }
        return $line_item_name;
    }


    /**
     *   adjust_promotion_line_item_gateway
     *   allows promotions to adjust the line item name sent to gateway
     *
     * @param string               $line_item_name
     * @param GatewayDataFormatter $gateway
     * @param EE_Line_Item         $line_item
     * @return string
     * @throws EE_Error
     * @throws ReflectionException
     */
    public static function adjust_promotion_line_item_gateway(
        string $line_item_name,
        GatewayDataFormatter $gateway,
        EE_Line_Item $line_item
    ): string {
        // is this a promotion ?
        if ($line_item->OBJ_type() === 'Promotion') {
            $line_item_name = sprintf(
                esc_html__('Discount: %1$s', 'event_espresso'),
                $line_item->name()
            );
        }
        return $line_item_name;
    }


    /**
     * Alters the registration csv report generated from the normal registration list table.
     * Add a column
     *
     * @param array $csv_row
     * @param array $reg_db_row
     * @return array
     * @throws EE_Error
     * @throws ReflectionException
     */
    public static function add_promotions_column_to_reg_csv_report(array $csv_row, array $reg_db_row): array
    {
        $promo_rows         = (array) EEM_Price::instance()->get_all_wpdb_results(
            [
                [
                    'Promotion.Line_Item.TXN_ID' => $reg_db_row['Registration.TXN_ID'],
                ],
            ]
        );
        $promos_for_csv_col = [];
        foreach ($promo_rows as $promo_row) {
            if ($promo_row['Promotion.PRO_code']) {
                $promos_for_csv_col[] = sprintf(
                    '%1$s [%2$s]',
                    $promo_row['Price.PRC_name'],
                    $promo_row['Promotion.PRO_code']
                );
            } else {
                $promos_for_csv_col[] = $promo_row['Price.PRC_name'];
            }
        }
        $csv_row[ esc_html__('Transaction Promotions', 'event_espresso') ] = implode(',', $promos_for_csv_col);
        return $csv_row;
    }


    /**
     * Callback for AHEE__EE_Base_Class__delete_before hook so we can ensure
     * any promotion relationships for an item being deleted are also handled.
     *
     * @param EE_Base_Class $model_object
     * @param               $successfully_deleted
     * @throws EE_Error
     * @throws ReflectionException
     */
    public static function delete_related_promotion_on_scope_item_delete(
        EE_Base_Class $model_object,
        $successfully_deleted
    ) {
        if (! $successfully_deleted) {
            return;
        }
        $OBJ_type = str_replace('EE_', '', get_class($model_object));
        EEM_Promotion_Object::instance()->delete(
            [
                [
                    'OBJ_ID'   => $model_object->ID(),
                    'POB_type' => $OBJ_type,
                ],
            ]
        );
    }


    private function processInvalidCart()
    {
        EE_Error::add_error(
            sprintf(
                apply_filters(
                    'FHEE__EED_Promotions___submit_promo_code__invalid_cart_notice',
                    esc_html__(
                        'We\'re sorry, but the %1$s could not be applied because the event cart could not be retrieved.',
                        'event_espresso'
                    )
                ),
                strtolower($this->config()->label->singular)
            ),
            __FILE__,
            __FUNCTION__,
            __LINE__
        );
        $this->generate_JSON_response();
    }


    public static function displayPromoCodeLinkButton()
    {
        PromoCodeLink::addEventEditorPermalinkButton(15);
        wp_enqueue_style(
            'espresso_promo_code_link',
            EE_PROMOTIONS_URL . 'css/promo-code-link-event-editor.css',
            [EspressoLegacyAdminAssetManager::CSS_HANDLE_EE_ADMIN],
            EE_PROMOTIONS_VERSION
        );
    }


    public static function getSession(): EE_Session
    {
        $session = EE_Session::instance();
        if ($session instanceof EE_Session) {
            return $session;
        }
        throw new RuntimeException(
            esc_html__('Could not retrieve session object for promo code link processing.', 'event_espresso')
        );
    }


    public static function detectPromoCodeLinks()
    {
        $request = EED_Promotions::getRequest();
        if ($request->isFrontend() && $request->requestParamIsSet(PromoCodeLink::REQUEST_PARAM)) {
            $promo_code = $request->getRequestParam(PromoCodeLink::REQUEST_PARAM);
            $promo_code = trim(sanitize_text_field($promo_code));
            $saved = EED_Promotions::getSession()->set_session_data([PromoCodeLink::REQUEST_PARAM => $promo_code]);
            add_action(
                'AHEE__ticket_selector_chart__template__after_ticket_selector',
                ['EED_Promotions', 'displayPromoCodeNoticeAtTicketSelector']
            );
        }
    }


    public static function displayPromoCodeNoticeAtTicketSelector()
    {
        $promo_code = EED_Promotions::getSession()->get_session_data(PromoCodeLink::REQUEST_PARAM);
        if (! $promo_code) {
            return;
        }
        $notice = sprintf(
            esc_html__(
                'The "%1$s" promotion code will be applied to your purchase at checkout.',
                'event_espresso'
            ),
            $promo_code
        );
        $styles = '
        background: hsla(140, 85%, 35%, .1);
        border: 2px solid hsla(140, 85%, 35%, 1);
        font-size: 1rem;
        padding: .75rem 1.5rem; margin: 1rem 0;';
        echo "
        <div class='ee-promo-code-link-notice' style='$styles'>
            <p>$notice</p>
        </div>";
    }


    /**
     * @throws ReflectionException
     * @throws EE_Error
     * @return void
     */
    public static function applyPromoCodeFromSession(EE_Checkout $checkout)
    {
        if (
            ! $checkout->current_step instanceof EE_SPCO_Reg_Step_Payment_Options
            || ! $checkout->transaction instanceof EE_Transaction
        ) {
            return;
        }
        $promo_code = EED_Promotions::getSession()->get_session_data(PromoCodeLink::REQUEST_PARAM);
        if ( ! $promo_code) {
            return;
        }
        remove_filter(
            'FHEE__EE_Session__reset_data__session_data_keys_to_reset',
            ['EED_Promotions', 'dontClearPromoCodeSession']
        );
        EE_Session::instance()->reset_data([PromoCodeLink::REQUEST_PARAM]);

        /** @type EEM_Line_Item $EEM_Line_Item */
        $EEM_Line_Item                = EE_Registry::instance()->load_model('Line_Item');
        $existing_promotion_line_item = $EEM_Line_Item->getExistingPromotionLineItemForTransactionByCode(
            $checkout->transaction->ID(),
            $promo_code
        );
        if ($existing_promotion_line_item instanceof EE_Line_Item) {
            return;
        }

        $request = EED_Promotions::getRequest();
        $request->setRequestParam(PromoCodeLink::REQUEST_PARAM, $promo_code);
        EED_Promotions::instance()->set_config();
        EED_Promotions::instance()->submitPromoCode(false);
    }


    public static function dontClearPromoCodeSession(array $session_data_keys): array
    {
        if (($key = array_search(PromoCodeLink::REQUEST_PARAM, $session_data_keys)) !== false) {
            unset($session_data_keys[$key]);
        }
        return $session_data_keys;
    }
}
