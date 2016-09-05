<?php if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}
/*
 * Event Espresso
 *
 * Event Registration and Management Plugin for WordPress
 *
 * @ package	Event Espresso
 * @ author		Event Espresso
 * @ copyright	(c) 2008-2014 Event Espresso  All Rights Reserved.
 * @ license	http://eventespresso.com/support/terms-conditions/   * see Plugin Licensing *
 * @ link		http://www.eventespresso.com
 * @ version	$VID:$
 *
 * ------------------------------------------------------------------------
 */



/**
 * Class  EED_Promotions
 *
 * @package     Event Espresso
 * @subpackage  espresso-promotions
 * @author      Brent Christensen
 *                        ------------------------------------------------------------------------
 */
class EED_Promotions extends EED_Module {

	/**
	 * @var        bool
	 * @access    public
	 */
	public static $shortcode_active = false;



	/**
	 * @return EED_Promotions
	 */
	public static function instance() {
		return parent::get_instance( __CLASS__ );
	}



	/**
	 *    set_hooks - for hooking into EE Core, other modules, etc
	 *
	 * @access    public
	 * @return    void
	 */
	public static function set_hooks() {
		EE_Config::register_route( 'promotions', 'EED_Promotions', 'run' );
		add_action( 'wp_enqueue_scripts', array( 'EED_Promotions', 'translate_js_strings' ), 1 );
		add_action( 'wp_enqueue_scripts', array( 'EED_Promotions', 'enqueue_scripts' ) );
		add_action(
			'AHEE__ticket_selector_chart__template__before_ticket_selector',
			array( 'EED_Promotions', 'display_event_promotions_banner' ),
			10,
			1
		);
		add_action(
			'FHEE__EE_Ticket_Selector__process_ticket_selections__before_redirecting_to_checkout',
			array( 'EED_Promotions', 'auto_process_promotions_in_cart' ),
			10,
			1
		);
		add_action(
			'FHEE__EE_SPCO_Reg_Step_Payment_Options___display_payment_options__before_payment_options',
			array( 'EED_Promotions', 'add_promotions_form_inputs' )
		);
		// adjust SPCO
		add_filter(
			'FHEE__EE_SPCO_Line_Item_Display_Strategy__item_row__name',
			array( 'EED_Promotions', 'adjust_SPCO_line_item_display' ),
			10,
			2
		);
	}



	/**
	 *    set_hooks_admin - for hooking into EE Admin Core, other modules, etc
	 *
	 * @access    public
	 * @return    void
	 */
	public static function set_hooks_admin() {
		add_action(
			'FHEE__EE_SPCO_Reg_Step_Payment_Options___display_payment_options__before_payment_options',
			array( 'EED_Promotions', 'add_promotions_form_inputs' )
		);
		add_action(
			'FHEE__EE_Ticket_Selector__process_ticket_selections__before_redirecting_to_checkout',
			array( 'EED_Promotions', 'auto_process_promotions_in_cart' ),
			10,
			1
		);
		// _get_promotions
		add_action( 'wp_ajax_espresso_get_promotions', array( 'EED_Promotions', '_get_promotions' ) );
		add_action( 'wp_ajax_nopriv_espresso_get_promotions', array( 'EED_Promotions', '_get_promotions' ) );
		// submit_promo_code
		add_action( 'wp_ajax_espresso_submit_promo_code', array( 'EED_Promotions', 'submit_promo_code' ) );
		add_action( 'wp_ajax_nopriv_espresso_submit_promo_code', array( 'EED_Promotions', 'submit_promo_code' ) );
		// adjust SPCO
		add_filter(
			'FHEE__EE_SPCO_Line_Item_Display_Strategy__item_row__name',
			array( 'EED_Promotions', 'adjust_SPCO_line_item_display' ),
			10,
			2
		);
		// TXN admin
		add_filter(
			'FHEE__EE_Admin_Transactions_List_Table__column_TXN_total__TXN_total',
			array( 'EED_Promotions', 'transactions_list_table_total' ),
			10,
			2
		);
		add_filter(
			'FHEE__Transactions_Admin_Page___transaction_legend_items__items',
			array( 'EED_Promotions', 'transactions_list_table_legend' ),
			10,
			2
		);
		add_filter(
			'FHEE__EE_Export__report_registrations__reg_csv_array',
			array( 'EED_Promotions', 'add_promotions_column_to_reg_csv_report' ),
			10,
			2
		);
		// when events are deleted
		add_action(
			'AHEE__EE_Base_Class__delete_permanently__end',
			array( 'EED_Promotions', 'delete_related_promotion_on_scope_item_delete' ),
			10,
			2
		);
	}



	/**
	 *    set_config
	 * this configures this module to use the same config as the EE_Promotions class
	 *
	 * @return EE_Promotions_Config
	 */
	public function set_config() {
		$this->set_config_section( 'addons' );
		$this->set_config_class( 'EE_Promotions_Config' );
		$this->set_config_name( 'promotions' );
		return parent::config();
	}



	/**
	 * @return EE_Promotions_Config
	 */
	public function config() {
		if ( ! $this->_config instanceof EE_Promotions_Config ) {
			return $this->set_config();
		}
		return $this->_config;
	}



	/**
	 *    run - initial module setup
	 *
	 * @access    public
	 * @param  WP $WP
	 * @return    void
	 */
	public function run( $WP ) {
		EED_Promotions::instance()->set_config();
	}



	/**
	 *        translate_js_strings
	 *
	 * @access        public
	 * @return        void
	 */
	public static function translate_js_strings() {
		EE_Registry::$i18n_js_strings['no_promotions_code'] = __(
			'Please enter a valid Promotion Code.',
			'event_espresso'
		);
	}



	/**
	 *    enqueue_scripts - Load the scripts and css
	 *
	 * @access    public
	 * @return    void
	 */
	public static function enqueue_scripts() {
		//Check to see if the promotions css file exists in the '/uploads/espresso/' directory
		if ( is_readable( EVENT_ESPRESSO_UPLOAD_DIR . 'css' . DS . 'promotions.css' ) ) {
			//This is the url to the css file if available
			wp_register_style( 'espresso_promotions', EVENT_ESPRESSO_UPLOAD_URL . 'css' . DS . 'promotions.css' );
		} else {
			// EE promotions style
			wp_register_style( 'espresso_promotions', EE_PROMOTIONS_URL . 'css' . DS . 'promotions.css' );
		}
		// only load JS if SPCO is active
		if ( apply_filters( 'EED_Single_Page_Checkout__SPCO_active', false ) ) {
			// promotions script
			wp_register_script(
				'espresso_promotions',
				EE_PROMOTIONS_URL . 'scripts' . DS . 'promotions.js',
				array( 'single_page_checkout' ),
				EE_PROMOTIONS_VERSION,
				true
			);
		}
		if ( EED_Promotions::load_assets() ) {
			// load JS
			wp_enqueue_style( 'espresso_promotions' );
			wp_enqueue_script( 'espresso_promotions' );
		}
	}



	/**
	 *    load_assets
	 *
	 * @access        public
	 * @return        bool
	 */
	public static function load_assets() {
		return
			! is_admin()
			&& (
				is_singular( 'espresso_events' )
				|| is_post_type_archive( 'espresso_events' )
				|| apply_filters( 'EED_Single_Page_Checkout__SPCO_active', false )
				|| EED_Promotions::$shortcode_active
			)
				? true
				: false;
	}



	/********************************** TXN ADMIN PAGES ***********************************/
	/**
	 *    transactions_list_table_total
	 *
	 * @access    public
	 * @param    string         $TXN_total
	 * @param    EE_Transaction $transaction
	 * @return    string
	 * @throws \EE_Error
	 */
	public static function transactions_list_table_total( $TXN_total = '', EE_Transaction $transaction ) {
		$promotion_line_items = $transaction->line_items( array( array( 'OBJ_type' => 'Promotion' ) ) );
		$promotion_line_item = reset( $promotion_line_items );
		if ( $promotion_line_item instanceof EE_Line_Item ) {
			$edit_link = EEH_URL::add_query_args_and_nonce(
				array( 'action' => 'edit', 'PRO_ID' => $promotion_line_item->OBJ_ID() ),
				EE_PROMOTIONS_ADMIN_URL
			);
			$TXN_total = '<a href="'
			             . $edit_link
			             . '" title="'
			             . __(
				             'A Promotion was redeemed during this Transaction. Click to View Promotion',
				             'event_espresso'
			             )
			             . '">'
			             . ' <sup><span class="dashicons dashicons-tag green-icon ee-icon-size-12"></span></sup>'
			             . $TXN_total
			             . '</a>';
		}
		return $TXN_total;
	}



	/**
	 *    transactions_list_table_legend
	 *
	 * @access    public
	 * @param    array $legend_items
	 * @return    array
	 */
	public static function transactions_list_table_legend( $legend_items = array() ) {
		$legend_items['promotion_redeemed'] = array(
			'class' => 'dashicons dashicons-tag green-icon ee-icon-size-12',
			'desc'  => __( 'Promotion was redeemed during Transaction', 'event_espresso' ),
		);
		return $legend_items;
	}



	/********************************** DISPLAY PROMOTIONS  ***********************************/
	/**
	 *    display_promotions
	 *
	 * @access    public
	 * @param    array $attributes
	 * @return    string
	 * @throws \EE_Error
	 */
	public static function display_promotions( $attributes = array() ) {
		EED_Promotions::instance()->set_config();
		return EED_Promotions::instance()->_display_promotions( $attributes );
	}



	/**
	 *    _display_promotions
	 *
	 * @access    private
	 * @param    array $attributes
	 * @return    string
	 * @throws \EE_Error
	 */
	private function _display_promotions( $attributes = array() ) {
		$html = '';
		/** @type EEM_Promotion $EEM_Promotion */
		$EEM_Promotion = EE_Registry::instance()->load_model( 'Promotion' );
		EE_Registry::instance()->load_helper( 'Template' );
		$active_promotions = $EEM_Promotion->get_all_active_codeless_promotions( $attributes );
		foreach ( $active_promotions as $promotion ) {
			if ( $promotion instanceof EE_Promotion ) {
				$scope_objects = $promotion->get_objects_promo_applies_to();
				$html .= EEH_Template::locate_template(
					apply_filters(
						'FHEE__EED_Promotions___display_promotions__banner_template',
						EE_PROMOTIONS_PATH . 'templates' . DS . 'upcoming-promotions-grid.template.php'
					),
					array(
						'PRO_ID'         => $promotion->ID(),
						'promo_bg_color' => ! empty( $this->config()->ribbon_banner_color )
							? $this->config()->ribbon_banner_color
							: 'lite-blue',        // lite-blue 		blue 	pink 	green 		red
						'promo_header'   => $promotion->name(),
						'promo_desc'     => $promotion->description() !== ''
							? $promotion->description() . '<br />'
							: '',
						'promo_amount'   => $promotion->pretty_amount(),
						'promo_dates'    => $promotion->promotion_date_range(),
						'promo_scopes'   => $promotion->get_promo_applies_to_link_array( $scope_objects ),
					)
				);
			}
		}
		return $html;
	}



	/********************************** DISPLAY PROMOTIONS BANNER ***********************************/



	/**
	 *    display_promotions_banner
	 *
	 * @access    public
	 * @param    \EE_Event $event
	 * @return    void
	 * @throws \EE_Error
	 */
	public static function display_event_promotions_banner( $event ) {
		EED_Promotions::instance()->set_config();
		EED_Promotions::instance()->_display_event_promotions_banner( $event );
	}



	/**
	 *    _display_event_promotions_banner
	 *
	 * @access    private
	 * @param    \EE_Event $event
	 * @return    void
	 * @throws \EE_Error
	 */
	private function _display_event_promotions_banner( $event ) {
		if ( $event instanceof EE_Event ) {
			$banner_text = array();
			/** @type EEM_Promotion $EEM_Promotion */
			$EEM_Promotion = EE_Registry::instance()->load_model( 'Promotion' );
			EE_Registry::instance()->load_helper( 'Template' );
			$active_promotions = $EEM_Promotion->get_all_active_codeless_promotions();
			foreach ( $active_promotions as $promotion ) {
				if ( $promotion instanceof EE_Promotion ) {
					// get all promotion objects that can still be redeemed
					$redeemable_scope_promos = $promotion->scope_obj()->get_redeemable_scope_promos(
						$promotion,
						true,
						$event
					);
					foreach ( $redeemable_scope_promos as $scope => $promo_obj_IDs ) {
						if (
							$scope === 'Event'
							&& $promotion->description() !== ''
							&& in_array( $event->ID(), $promo_obj_IDs )
						) {
							$banner_text[] = $promotion->description();
						}
					}
				}
			}
			if ( ! empty( $banner_text ) && ! empty( $this->config()->banner_template ) ) {
				EEH_Template::locate_template(
					apply_filters(
						'FHEE__EED_Promotions___display_event_promotions_banner__banner_template',
						EE_PROMOTIONS_PATH . 'templates' . DS . $this->config()->banner_template
					),
					array(
						'EVT_ID'        => $event->ID(),
						'banner_header' => apply_filters(
							'FHEE__EED_Promotions___display_event_promotions_banner__banner_header',
							__( 'Current Promotions', 'event_espresso' )
						),
						'banner_text'   => implode( '<div class="ee-promo-separator-dv">+</div>', $banner_text ),
						'ribbon_color'  => ! empty( $this->config()->ribbon_banner_color )
							? $this->config()->ribbon_banner_color
							: 'lite-blue'        // lite-blue 		blue 		pink 	green 		red
					),
					true,
					false
				);
			}
		}
	}



	/********************************** AUTO PROCESS PROMOTIONS IN CART ***********************************/
	/**
	 *    auto_process_promotions_in_cart
	 *
	 * @access    public
	 * @param    \EE_Cart $cart
	 * @return    void
	 * @throws \EE_Error
	 */
	public static function auto_process_promotions_in_cart( $cart ) {
		EED_Promotions::instance()->set_config();
		EED_Promotions::instance()->_auto_process_promotions_in_cart( $cart );
	}



	/**
	 *    _auto_process_promotions_in_cart
	 *
	 * @access    private
	 * @param    \EE_Cart $cart
	 * @return    void
	 * @throws \EE_Error
	 */
	private function _auto_process_promotions_in_cart( $cart ) {
		/** @type EEM_Promotion $EEM_Promotion */
		$EEM_Promotion = EE_Registry::instance()->load_model( 'Promotion' );
		$active_promotions = $EEM_Promotion->get_all_active_codeless_promotions();
		foreach ( $active_promotions as $promotion ) {
			if ( $promotion instanceof EE_Promotion ) {
				// determine if the promotion can be applied to an item in the current cart
				$applicable_items = $this->get_applicable_items( $promotion, $cart );
				// add line item
				if (
					! empty( $applicable_items )
					&& $this->generate_promotion_line_items(
						$promotion,
						$applicable_items,
						$this->config()->affects_tax()
					)
				) {
					$cart->get_grand_total()->recalculate_total_including_taxes();
					$cart->save_cart( false );
				}
			}
		}
	}



	/********************************** ADD PROMOTIONS FORM INPUTS ***********************************/
	/**
	 *    add_promotions_form_inputs
	 *
	 * @access    public
	 * @param    EE_Form_Section_Proper $before_payment_options
	 * @return    EE_Form_Section_Proper
	 */
	public static function add_promotions_form_inputs( $before_payment_options ) {
		EED_Promotions::instance()->set_config();
		return EED_Promotions::instance()->_add_promotions_form_inputs( $before_payment_options );
	}



	/**
	 *    _add_promotions_form_inputs
	 *
	 * @access        private
	 * @param    EE_Form_Section_Proper $before_payment_options
	 * @return        EE_Form_Section_Proper
	 */
	private function _add_promotions_form_inputs( $before_payment_options ) {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		EE_Registry::instance()->load_helper( 'HTML' );
		$before_payment_options->add_subsections(
			array(
				'promotions_form' => new EE_Form_Section_Proper(
					array(
						'layout_strategy' => new EE_No_Layout(),
						'subsections'     => array(
							'ee_promotion_code_input'  => new EE_Text_Input(
								array(
									'default'         => '',
									'html_id'         => 'ee-promotion-code-input',
									'html_class'      => 'ee-promotion-code-input ee-reg-qstn',
									'html_name'       => 'ee_promotion_code_input',
									'html_label_text' => apply_filters(
										'FHEE__EED_Promotions___add_promotions_form_inputs__ee_promotion_code_input__html_label_text',
										EEH_HTML::h4( $this->config()->label->singular )
									),
								)
							),
							'ee_promotion_code_submit' => new EE_Submit_Input(
								array(
									'html_id'   => 'ee-promotion-code',
									'html_name' => 'ee_promotion_code_submit',
									'default'   => apply_filters(
										'FHEE__EED_Promotions___add_promotions_form_inputs__ee_promotion_code_submit__default',
										sprintf( __( 'Submit %s', 'event_espresso' ), $this->config()->label->singular )
									),
								)
							),
							'ee_promotion_code_header' => new EE_Form_Section_HTML(
								EEH_HTML::div( ' ', '', 'clear-float' )
							),
						),
					)
				),
			)
		);
		return $before_payment_options;
	}



	/********************************** SUBMIT PROMO CODE ***********************************/
	/**
	 *    submit_promo_code
	 *
	 * @access    public
	 * @return    void
	 * @throws \EE_Error
	 */
	public static function submit_promo_code() {
		EED_Promotions::instance()->set_config();
		EED_Promotions::instance()->_submit_promo_code();
	}



	/**
	 *    _submit_promo_code
	 *
	 * @access        private
	 * @return        void
	 * @throws \EE_Error
	 */
	private function _submit_promo_code() {
		$return_data = array();
		// get the EE_Cart object being used for the current transaction
		/** @type EE_Cart $cart */
		$cart = EE_Registry::instance()->SSN->cart();
		if ( $cart instanceof EE_Cart ) {
			// and make sure the model cache is
			$cart->get_grand_total()->get_model()->refresh_entity_map_with(
				$cart->get_grand_total()->ID(),
				$cart->get_grand_total()
			);
			$promotion = $this->get_promotion_details_from_request();
			if ( $promotion instanceof EE_Promotion ) {
				// determine if the promotion can be applied to an item in the current cart
				$applicable_items = $this->get_applicable_items( $promotion, $cart, false, true );
				if ( ! empty( $applicable_items ) ) {
					// add line item
					if ( $this->generate_promotion_line_items(
						$promotion,
						$applicable_items,
						$this->config()->affects_tax()
					)
					) {
						// ensure cart totals have been recalculated and saved
						$cart->get_grand_total()->recalculate_total_including_taxes();
						$cart->get_grand_total()->save_this_and_descendants();
						/** @type EE_Registration_Processor $registration_processor */
						$registration_processor = EE_Registry::instance()->load_class( 'Registration_Processor' );
						$registration_processor->update_registration_final_prices(
							$cart->get_grand_total()->transaction()
						);
						$cart->save_cart( false );
						$return_data = $this->_get_payment_info( $cart );
						$return_data['success'] = $promotion->accept_message();
						EED_Single_Page_Checkout::update_checkout();
					} else {
						EE_Error::add_attention( $promotion->decline_message(), __FILE__, __FUNCTION__, __LINE__ );
					}
				}
			}
		} else {
			EE_Error::add_error(
				sprintf(
					apply_filters(
						'FHEE__EED_Promotions___submit_promo_code__invalid_cart_notice',
						__(
							'We\'re sorry, but the %1$s could not be applied because the event cart could not be retrieved.',
							'event_espresso'
						)
					),
					strtolower( $this->config()->label->singular )
				),
				__FILE__,
				__FUNCTION__,
				__LINE__
			);
		}
		$this->generate_JSON_response( $return_data );
	}



	/**
	 *    get_promotion_details_from_request
	 *
	 * @access    public
	 * @param string $promo_code
	 * @return    EE_Promotion
	 */
	public function get_promotion_details_from_request( $promo_code = '' ) {
		// get promo code from $_REQUEST or use incoming default value
		$promo_code = sanitize_text_field( EE_Registry::instance()->REQ->get( 'promo_code', $promo_code ) );
		/** @type EEM_Promotion $EEM_Promotion */
		$EEM_Promotion = EE_Registry::instance()->load_model( 'Promotion' );
		// $EEM_Promotion->show_next_x_db_queries();
		$promo = $EEM_Promotion->get_promotion_details_via_code( $promo_code );
		if ( ! $promo instanceof EE_Promotion ) {
			EE_Error::add_attention(
				sprintf(
					apply_filters(
						'FHEE__EED_Promotions__get_promotion_details_from_request__invalid_promotion_notice',
						__(
							'We\'re sorry, but the %1$s "%2$s" appears to be invalid.%3$sYou are welcome to try a different %1$s or to try this one again to ensure it was entered correctly.',
							'event_espresso'
						)
					),
					strtolower( $this->config()->label->singular ),
					$promo_code,
					'<br />'
				),
				__FILE__,
				__FUNCTION__,
				__LINE__
			);
			return null;
		}
		return $promo;
	}



	/**
	 *    validate_promotion
	 *    determine if the promotion has global uses left and can be applied to a valid item in the current cart
	 *
	 * @access    public
	 * @param \EE_Promotion $promotion
	 * @param \EE_Cart      $cart
	 * @param bool          $suppress_notices
	 * @param bool          $get_events
	 * @return \EE_Line_Item[]
	 * @throws \EE_Error
	 */
	public function get_applicable_items(
		EE_Promotion $promotion,
		EE_Cart $cart,
		$suppress_notices = true,
		$get_events = false
	) {
		$applicable_items = array();
		// verify EE_Promotion
		if ( $promotion instanceof EE_Promotion ) {
			EE_Registry::instance()->load_helper( 'Line_Item' );
			$events = $get_events ? $this->get_events_from_cart( $cart ) : array();
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
		}
		if ( empty( $applicable_items ) && ! $suppress_notices ) {
			EE_Error::add_attention(
				sprintf(
					apply_filters(
						'FHEE__EED_Promotions__get_applicable_items__no_applicable_items_notice',
						__(
							'We\'re sorry, but the %1$s "%2$s" could not be applied to any %4$s.%3$sYou are welcome to try a different %1$s or to try this one again to ensure it was entered correctly.',
							'event_espresso'
						)
					),
					strtolower( $this->config()->label->singular ),
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
	 * @since     1.0.4
	 * @access    public
	 * @param    \EE_Cart $cart
	 * @return    \EE_Event[]
	 */
	public function get_events_from_cart( EE_Cart $cart ) {
		$event_line_items = $object_type_line_items = EEH_Line_Item::get_event_subtotals( $cart->get_grand_total() );
		$events = array();
		foreach ( $event_line_items as $event_line_item ) {
			if ( $event_line_item instanceof EE_Line_Item ) {
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
	 * @access    public
	 * @param \EE_Promotion   $promotion
	 * @param \EE_Line_Item[] $applicable_items
	 * @param bool            $affects_tax
	 * @return bool
	 * @throws \EE_Error
	 */
	public function generate_promotion_line_items(
		EE_Promotion $promotion,
		$applicable_items = array(),
		$affects_tax = false
	) {
		$success = false;
		// verify EE_Promotion
		if ( $promotion instanceof EE_Promotion ) {
			foreach ( $applicable_items as $applicable_item ) {
				if (
					$applicable_item instanceof EE_Line_Item
					&& $this->verify_no_existing_promotion_line_items( $applicable_item, $promotion )
					&& $this->verify_no_exclusive_promotions_combined( $applicable_item, $promotion )
				) {
					$promotion_line_item = $promotion->scope_obj()->generate_promotion_line_item(
						$applicable_item,
						$promotion,
						$promotion->name(),
						$affects_tax
					);
					if ( $promotion_line_item instanceof EE_Line_Item ) {
						$success = $this->add_promotion_line_item(
							$applicable_item,
							$promotion_line_item,
							$promotion
						)
							? true
							: $success;
					}
				}
			}
		}
		return $success;
	}



	/**
	 * get_redeemable_scope_promos
	 * searches the cart for any items that this promotion applies to
	 *
	 * @since   1.0.0
	 * @param EE_Line_Item $parent_line_item
	 * @param EE_Promotion $promotion
	 * @return boolean
	 */
	public function verify_no_existing_promotion_line_items( EE_Line_Item $parent_line_item, EE_Promotion $promotion ) {
		/** @type EEM_Line_Item $EEM_Line_Item */
		$EEM_Line_Item = EE_Registry::instance()->load_model( 'Line_Item' );
		// check that promotion hasn't already been applied
		$existing_promotion_line_item = $EEM_Line_Item->get_existing_promotion_line_item(
			$parent_line_item,
			$promotion
		);
		if ( $existing_promotion_line_item instanceof EE_Line_Item ) {
			if ( $promotion->code() ) {
				EE_Error::add_attention(
					sprintf(
						apply_filters(
							'FHEE__EED_Promotions__verify_no_existing_promotion_line_items__existing_promotion_code_notice',
							__(
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
		return true;
	}



	/**
	 * verify_no_exclusive_promotions_combined
	 * verifies that no exclusive promotions are being combined together
	 *
	 * @since   1.0.0
	 * @param EE_Line_Item $parent_line_item
	 * @param EE_Promotion $promotion
	 * @return EE_Line_Item
	 */
	public function verify_no_exclusive_promotions_combined( EE_Line_Item $parent_line_item, EE_Promotion $promotion ) {
		/** @type EEM_Line_Item $EEM_Line_Item */
		$EEM_Line_Item = EE_Registry::instance()->load_model( 'Line_Item' );
		// get all existing promotions that have already been added to the cart
		$existing_promotion_line_items = $EEM_Line_Item->get_all_promotion_line_items( $parent_line_item );
		if ( ! empty( $existing_promotion_line_items ) ) {
			// can't apply this new promotion if it is exclusive
			if ( $promotion->is_exclusive() ) {
				EE_Error::add_attention(
					sprintf(
						apply_filters(
							'FHEE__EED_Promotions__verify_no_exclusive_promotions_combined__new_promotion_is_exclusive_notice',
							__(
								'We\'re sorry, but %3$s have already been added to the cart and the "%1$s%2$s" promotion can not be combined with others.',
								'event_espresso'
							)
						),
						$promotion->code() ? $promotion->code() . ' : ' : '',
						$promotion->name(),
						strtolower( $this->config()->label->plural )
					),
					__FILE__,
					__FUNCTION__,
					__LINE__
				);
				return false;
			}
			// new promotion is not exclusive...
			// so now determine if any existing ones are
			foreach ( $existing_promotion_line_items as $existing_promotion_line_item ) {
				if ( $existing_promotion_line_item instanceof EE_Line_Item ) {
					$existing_promotion = $this->get_promotion_from_line_item( $existing_promotion_line_item );
					if ( $existing_promotion instanceof EE_Promotion && $existing_promotion->is_exclusive() ) {
						EE_Error::add_attention(
							sprintf(
								apply_filters(
									'FHEE__EED_Promotions__verify_no_exclusive_promotions_combined__existing_promotion_is_exclusive_notice',
									__(
										'We\'re sorry, but the "%1$s%2$s" %3$s has already been added to the cart and can not be combined with others.',
										'event_espresso'
									)
								),
								$existing_promotion->code() ? $existing_promotion->code() . ' : ' : '',
								$existing_promotion->name(),
								strtolower( $this->config()->label->singular )
							),
							__FILE__,
							__FUNCTION__,
							__LINE__
						);
						return false;
					}
				}
			}
		}
		return true;
	}



	/**
	 *    get_promotion_from_line_item
	 *
	 * @access    public
	 * @param EE_Line_Item $promotion_line_item the line item representing the new promotion
	 * @return    EE_Promotion | null
	 */
	public function get_promotion_from_line_item( EE_Line_Item $promotion_line_item ) {
		$promotion = EEM_Promotion::instance()->get_one_by_ID( $promotion_line_item->OBJ_ID() );
		if ( ! $promotion instanceof EE_Promotion ) {
			EE_Error::add_error(
				sprintf(
					apply_filters(
						'FHEE__EED_Promotions__get_promotion_from_line_item__invalid_promotion_notice',
						__(
							'We\'re sorry, but the %1$s could not be applied because information pertaining to it could not be retrieved from the database.',
							'event_espresso'
						)
					),
					strtolower( $this->config()->label->singular )
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
	 * @access    public
	 * @param EE_Line_Item $parent_line_item    the line item that the new promotion was added to as a child line item
	 * @param EE_Line_Item $promotion_line_item the line item representing the new promotion
	 * @param EE_Promotion $promotion           the promotion object that the line item was created for
	 * @return    boolean
	 * @throws \EE_Error
	 */
	public function add_promotion_line_item(
		EE_Line_Item $parent_line_item,
		EE_Line_Item $promotion_line_item,
		EE_Promotion $promotion
	) {
		EE_Registry::instance()->load_helper( 'Line_Item' );
		// add it to the cart
		if ( $parent_line_item->add_child_line_item( $promotion_line_item, false ) ) {
			try {
				if (
					$promotion->scope_obj()->increment_promotion_scope_uses(
						$promotion,
						$parent_line_item->OBJ_ID()
					)
				) {
					return true;
				}
			} catch ( Exception $e ) {
				EE_Error::add_error( $e->getMessage(), __FILE__, __FUNCTION__, __LINE__ );
			}
		}
		return false;
	}



	/**
	 *    _get_payment_info
	 *
	 * @access    public
	 * @param EE_Cart $cart
	 * @return    array
	 * @throws \EE_Error
	 */
	public function _get_payment_info( EE_Cart $cart ) {
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
		//$this->checkout->line_item_filters();
		$Line_Item_Display = new EE_Line_Item_Display( 'spco' );
		return array(
			'payment_info' => $Line_Item_Display->display_line_item( $filtered_line_item_tree ),
			'cart_total'   => $filtered_line_item_tree->total(),
		);
	}



	/**
	 *    generate_JSON_response
	 *        allows you to simply echo or print an EE_SPCO_JSON_Response object to produce a  JSON encoded string
	 *        ie: $json_response = new EE_SPCO_JSON_Response();
	 *        echo $json_response;
	 *
	 * @access    public
	 * @param array $return_data
	 * @return    void
	 */
	public function generate_JSON_response( $return_data = array() ) {
		$JSON_response = array();
		// grab notices
		$notices = EE_Error::get_notices( false );
		// add notices to JSON response, but only if they exist
		if ( isset( $notices['attention'] ) ) {
			$JSON_response['attention'] = $notices['attention'];
		}
		if ( isset( $notices['errors'] ) ) {
			$JSON_response['errors'] = $notices['errors'];
		}
		if ( isset( $notices['success'] ) ) {
			$JSON_response['success'] = $notices['success'];
		}
		if ( empty( $JSON_response ) && empty( $return_data ) ) {
			$JSON_response['errors'] = sprintf(
				__(
					'The %1$s entered could not be processed for an unknown reason.%2$sYou are welcome to try a different %1$s or to try this one again to ensure it was entered correctly.',
					'event_espresso'
				),
				strtolower( $this->config()->label->singular ),
				'<br />'
			);
		}
		// add return_data array to main JSON response array, IF it contains anything
		$JSON_response['return_data'] = $return_data;
		// filter final array
		$JSON_response = apply_filters( 'FHEE__EED_Promotions__generate_JSON_response__JSON_response', $JSON_response );
		// return encoded array
		echo json_encode( $JSON_response );
		exit();
	}



	/**
	 *    adjust_SPCO_line_item_display
	 *   allows promotions to adjust the line item name in EE_SPCO_Line_Item_Display_Strategy
	 *
	 * @access    public
	 * @param string        $line_item_name
	 * @param \EE_Line_Item $line_item
	 * @return float
	 */
	public static function adjust_SPCO_line_item_display( $line_item_name, EE_Line_Item $line_item ) {
		// is this a promotion ?
		if ( $line_item->OBJ_type() === 'Promotion' ) {
			$line_item_name = sprintf( __( 'Discount: %1$s', 'event_espresso' ), $line_item->name() );
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
	 * @throws \EE_Error
	 */
	public static function add_promotions_column_to_reg_csv_report( array $csv_row, $reg_db_row ) {
		$promo_rows = (array)EEM_Price::instance()->get_all_wpdb_results(
			array(
				array(
					'Promotion.Line_Item.TXN_ID' => $reg_db_row['Registration.TXN_ID'],
				),
			)
		);
		$promos_for_csv_col = array();
		foreach ( $promo_rows as $promo_row ) {
			if ( $promo_row['Promotion.PRO_code'] ) {
				$promos_for_csv_col[] = sprintf(
					'%1$s [%2$s]',
					$promo_row['Price.PRC_name'],
					$promo_row['Promotion.PRO_code']
				);
			} else {
				$promos_for_csv_col[] = $promo_row['Price.PRC_name'];
			}
		}
		$csv_row[ (string)__( 'Transaction Promotions', 'event_espresso' ) ] = implode( ',', $promos_for_csv_col );
		return $csv_row;
	}



	/**
	 * Callback for AHEE__EE_Base_Class__delete_before hook so we can ensure
	 * any promotion relationships for an item being deleted are also handled.
	 *
	 * @param EE_Base_Class $model_object
	 * @param               $successfully_deleted
	 * @throws \EE_Error
	 */
	public static function delete_related_promotion_on_scope_item_delete(
		EE_Base_Class $model_object,
		$successfully_deleted
	) {
		if ( ! $successfully_deleted ) {
			return;
		}
		$OBJ_type = str_replace( 'EE_', '', get_class( $model_object ) );
		EEM_Promotion_Object::instance()->delete(
			array(
				array(
					'OBJ_ID'   => $model_object->ID(),
					'POB_type' => $OBJ_type,
				),
			)
		);
	}


}
// End of file EED_Promotions.module.php
// Location: /wp-content/plugins/espresso-promotions/EED_Promotions.module.php