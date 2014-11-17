<?php
/**
 * This file contains the Promotions_Admin_Page class
 *
 * @since 1.0.0
 * @package EE Promotions
 * @subpackage admin
 */
if ( ! defined( 'EVENT_ESPRESSO_VERSION' )) { exit('NO direct script access allowed'); }

 /**
 * Promotions_Admin_Page
 *
 * This contains the logic for setting up the Promotions Addon Admin related pages.  Any
 * methods without PHP doc comments have inline docs with parent class.
 *
 * @since 1.0.0
 *
 * @package		EE Promotions
 * @subpackage 	admin
 * @author		Darren Ethier, Brent Christensen
 *
 * ------------------------------------------------------------------------
 */
class Promotions_Admin_Page extends EE_Admin_Page {

	/**
	 * This will hold the promotion object on create_new and edit routes
	 * @var EE_Promotion
	 */
	protected $_promotion;


	protected function _init_page_props() {
		$this->page_slug = PROMOTIONS_PG_SLUG;
		$this->page_label = PROMOTIONS_LABEL;
		$this->_admin_base_url = EE_PROMOTIONS_ADMIN_URL;
		$this->_admin_base_path = EE_PROMOTIONS_ADMIN;
	}




	protected function _ajax_hooks() {}





	protected function _define_page_props() {
		$this->_admin_page_title = PROMOTIONS_LABEL;
		$this->_labels = array(
			'buttons' => array(
				'add' => __('Add New Promotion', 'event_espresso'),
				'edit' => __('Edit Promotion', 'event_espresso'),
				'trash_promotion' => __('Trash Promotion', 'event_espresso'),
				'restore_promotion' => __('Restore Promotion', 'event_espresso')
				),
			'publishbox' => array(
				'create_new' => __('Save New Promotion', 'event_espresso'),
				'edit' => __('Update Promotion', 'event_espresso'),
				'basic_settings' => __('Update Settings', 'event_espresso')
				)
		);
	}




	protected function _set_page_routes() {
		$this->_page_routes = array(
			'default' => '_list_table',
			'create_new' => array(
				'func' => '_promotion_details',
				'args' => array(TRUE)
				),
			'edit' => '_promotion_details',
			'update_promotion' => array(
				'func' => '_insert_update_promotions',
				'noheader' => TRUE
				),
			'insert_promotion' => array(
				'func' => '_insert_update_promotions',
				'noheader' => TRUE,
				'args' => array( TRUE )
				),
			'duplicate' => array(
				'func' => '_duplicate_promotion',
				'noheader' => TRUE
				),
			'trash_promotion' => array(
				'func' => '_trash_or_restore_promotion',
				'args' => array( TRUE ),
				'noheader' => TRUE
				),
			'trash_promotions' => array(
				'func' => '_trash_or_restore_promotions',
				'args' => array( TRUE ),
				'noheader' => TRUE
				),
			'restore_promotion' => array(
				'func' => '_trash_or_restore_promotion',
				'args' => array( FALSE ),
				'noheader' => TRUE
				),
			'restore_promotions' => array(
				'func' => '_trash_or_restore_promotions',
				'args' => array( FALSE ),
				'noheader' => TRUE
				),
			'delete_promotions' => array(
				'func' => '_delete_promotions',
				'noheader' => TRUE
				),
			'delete_promotion' => array(
				'func' => '_delete_promotion',
				'noheader' => TRUE
				),
			'basic_settings' => '_basic_settings',
			'update_settings' => array(
				'func' => '_update_settings',
				'noheader' => TRUE
			),
			'usage' => '_usage'
		);
	}





	protected function _set_page_config() {
		EE_Registry::instance()->load_helper('URL');
		$this->_page_config = array(
			'default' => array(
				'nav' => array(
					'label' => __('Promotions Overview', 'event_espresso'),
					'order' => 10
					),
				'qtips' => array( 'Promotions_List_Table_Tips' ),
				'list_table' => 'Promotions_Admin_List_Table',
				'require_nonce' => FALSE
			),
			'create_new' => array(
				'nav' => array(
					'label' => __('Create Promotion', 'event_espresso'),
					'order' => 15,
					'url' => EEH_URL::add_query_args_and_nonce(array('action' => 'create_new'), EE_PROMOTIONS_ADMIN_URL ),
					'persistent' => FALSE
					),
				'metaboxes' => array( '_promotions_metaboxes', '_publish_post_box' ),
				'require_nonce' => FALSE
				),
			'edit' => array(
				'nav' => array(
					'label' => __('Edit Promotion', 'event_espresso'),
					'order' => 15,
					'url' => EEH_URL::add_query_args_and_nonce(array('action' => 'edit', 'PRO_ID' => !empty( $this->_req_data['PRO_ID'] ) ? $this->_req_data['PRO_ID'] : 0 ) , EE_PROMOTIONS_ADMIN_URL ),
					'persistent' => FALSE
					),
				'metaboxes' => array( '_promotions_metaboxes', '_publish_post_box' ),
				'require_nonce' => FALSE
				),
			'basic_settings' => array(
				'nav' => array(
					'label' => __('Settings', 'event_espresso'),
					'order' => 20
					),
				'metaboxes' => array_merge( $this->_default_espresso_metaboxes, array( '_publish_post_box') ),
				'require_nonce' => FALSE
			),
			'usage' => array(
				'nav' => array(
					'label' => __('Promotions Usage', 'event_espresso'),
					'order' => 30
					),
				'require_nonce' => FALSE
			)
		);
	}


	protected function _add_screen_options() {}
	protected function _add_screen_options_default() {
		$this->_per_page_screen_option();
	}

	protected function _add_feature_pointers() {}




	public function load_scripts_styles() {
		wp_register_style( 'promotions-details-css', EE_PROMOTIONS_ADMIN_ASSETS_URL . 'promotions-details.css', array('ee-admin-css','espresso-ui-theme'), EE_PROMOTIONS_VERSION );
		wp_enqueue_style( 'promotions-details-css' );
		wp_register_script( 'espresso_promotions_admin', EE_PROMOTIONS_ADMIN_ASSETS_URL . 'espresso_promotions_admin.js', array( 'espresso_core', 'ee-datepicker', 'ee-parse-uri' ), EE_PROMOTIONS_VERSION, TRUE );
		wp_enqueue_script( 'espresso_promotions_admin');

		EE_Registry::$i18n_js_strings['confirm_reset'] = __( 'Are you sure you want to reset ALL your Event Espresso Promotions Information? This cannot be undone.', 'event_espresso' );
		EE_Registry::$i18n_js_strings['codefieldEmptyError'] = __( 'eePromotionsHelper.generate_code requires a selector for the codefield param. None was provided.', 'event_espresso' );
		EE_Registry::$i18n_js_strings['codefieldInvalidError'] = __( 'The codefield paramater sent to eePromotionsHelper.generate_code is invalid.  It must be a valid selector for the input field holding the generated coupon code.', 'event_espresso' );
		EE_Registry::$i18n_js_strings['toggledScopeItemMissingParam'] = __( 'eePromotionsHelper.scopeItemToggle requires the toggled checkbox dom element to be included as the argument.  Nothing was included.', 'event_espresso' );
		wp_localize_script( 'espresso_promotions_admin', 'eei18n', EE_Registry::$i18n_js_strings );
	}

	public function admin_init() {}
	public function admin_notices() {}
	public function admin_footer_scripts() {}




	/**
	 * _set_list_table_views_default
	 * @access protected
	 */
	protected function _set_list_table_views_default() {
		$this->_views = array(
			'all' => array(
				'slug' => 'all',
				'label' => __('All', 'event_espresso'),
				'count' => 0,
				'bulk_action' => array(
					'trash_promotions' => __('Move to Trash', 'event_espresso')
					)
				),
			'trash' => array(
				'slug' => 'trash',
				'label' => __('Trashed', 'event_espresso'),
				'count' => 0,
				'bulk_action' => array(
					'restore_promotions' => __('Restore from Trash', 'event_espresso'),
					'delete_promotions' => __('Delete', 'event_espresso')
					)
				)/**/
		);
	}




	/**
	 * _list_table
	 * @access protected
	 */
	protected function _list_table() {
		$this->_admin_page_title .= $this->get_action_link_or_button('create_new', 'add', array(), 'add-new-h2' );
		$this->_template_args['after_list_table'] = $this->_display_legend( $this->_promotion_legend_items() );
		$this->display_admin_list_table_page_with_no_sidebar();
	}



	/**
	 * _promotion_legend_items
	 * @return array
	 */
	protected function _promotion_legend_items() {
		$scope_legend = array();
		foreach ( EE_Registry::instance()->CFG->addons->promotions->scopes as $scope ) {
			if ( $scope instanceof EE_Promotion_Scope ) {
				$scope_legend[$scope->slug] = array(
					'class' => $scope->get_scope_icon(TRUE),
					'desc' => $scope->label->singular
				);
			}
		}
		$items = array(
			'active_status' => array(
				'class' => 'ee-status-legend ee-status-legend-' . EE_Promotion::active,
				'desc' => EEH_Template::pretty_status( EE_Promotion::active, FALSE, 'sentence')
				),
			'upcoming_status' => array(
				'class' => 'ee-status-legend ee-status-legend-' . EE_Promotion::upcoming,
				'desc' => EEH_Template::pretty_status( EE_Promotion::upcoming, FALSE, 'sentence')
				),
			'expired_status' => array(
				'class' => 'ee-status-legend ee-status-legend-' . EE_Promotion::expired,
				'desc' => EEH_Template::pretty_status( EE_Promotion::expired, FALSE, 'sentence')
				),
			'unavailable_status' => array(
				'class' => 'ee-status-legend ee-status-legend-' . EE_Promotion::unavailable,
				'desc' => EEH_Template::pretty_status( EE_Promotion::unavailable, FALSE, 'sentence')
				),
			array(
				'class' => '',
				'desc' => '<span class="ee-infinity-sign">&#8734;</span>   ' . __('Unlimited', 'event_espresso')
			)
		);
		return array_merge( $scope_legend, $items);
	}




	/**
	 * _set_promotion_object
	 * @access protected
	 */
	protected function _set_promotion_object() {
		//only set if not set already
		if ( $this->_promotion instanceof EE_Promotion )
			return;

		$this->_promotion = !empty( $this->_req_data['PRO_ID'] ) ? EEM_Promotion::instance()->get_one_by_ID( $this->_req_data['PRO_ID'] ) : EEM_Promotion::instance()->create_default_object();

		//verify we have a promotion object
		if ( ! $this->_promotion instanceof EE_Promotion )
			throw new EE_Error( sprintf( __('Something might be wrong with the models or the given Promotion ID in the request (%s) is not for a valid Promotion in the DB.', 'event_espresso'), $this->_req_data['PRO_ID'] ) );
	}



	/**
	 * metaboxes callback for create_new and edit promotion routes.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function _promotions_metaboxes() {
		$this->_set_promotion_object();
		add_meta_box( 'promotion-details-mbox', __('Promotions', 'event-espresso'), array( $this, 'promotion_details_metabox'), $this->wp_page_slug, 'normal', 'high' );
		add_meta_box( 'promotions-applied-to-mbox', __('Promotion applies to...', 'event_espresso'), array( $this, 'promotions_applied_to_metabox'), $this->wp_page_slug, 'side', 'default');
	}

	/**
	 * Callback for the create new promotion route.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $new Whether promotion is new or not. Default TRUE.
	 * @return void
	 */
	protected function _promotion_details( $new = FALSE ) {
		$id = $new ? '' : $this->_promotion->ID();
		$redirect = EEH_URL::add_query_args_and_nonce( array('action' => 'default'), $this->_admin_base_url );
		$view = $new ? 'insert_promotion' : 'update_promotion';
		$this->_set_add_edit_form_tags($view);
		$delete_route = $this->_promotion->get('PRO_deleted') ? 'restore_promotion' : 'trash_promotion';
		$this->_set_publish_post_box_vars( 'PRO_ID', $id, 'trash_promotion', $redirect);
		$this->display_admin_page_with_sidebar();
	}



	/**
	 * promotion_details_metabox
	 */
	public function promotion_details_metabox() {
		$promotion_uses = $this->_promotion->uses();
		$form_args = array(
			'promotion' => $this->_promotion,
			'promotion_uses' => $promotion_uses !== EE_INF_IN_DB ? $promotion_uses : '',
			'price_type_selector' => $this->_get_price_type_selector(),
			'scope_selector' => $this->_get_promotion_scope_selector()
			);
		$form_template = EE_PROMOTIONS_ADMIN_TEMPLATE_PATH . 'promotion_details_form.template.php';
		EEH_Template::display_template( $form_template, $form_args );
	}



	/**
	 * promotions_applied_to_metabox
	 */
	public function promotions_applied_to_metabox() {
		//we use the scope to get the metabox content.
		$scope = $this->_promotion->scope_obj();

		//if there is no scope then this is a default promotion object so the content will for promotions metabox will be generic.
		$content =  empty( $scope ) ? __('When you select a scope for the promotion this area will have options related to the selection.', 'event_espresso') : $scope->get_admin_applies_to_selector( $this->_promotion->ID() );
		echo $content;
	}



	/**
	 * _get_price_type_selector
	 * @return string
	 */
	protected function _get_price_type_selector() {
		//get Price Types for discount base price type.
		$price_types = EEM_Price_Type::instance()->get_all(  array( array( 'PBT_ID' => EEM_Price_Type::base_type_discount ) ) );
		$values = array();
		foreach ( $price_types as $ID => $pt ) {
			$values[] = array(
				'text' => $pt->name(),
				'id' => $ID
				);
		}

		$default = $this->_promotion->price_type_id();

		return EEH_Form_Fields::select_input( 'PRT_ID', $values, $default );
	}




	/**
	 * _get_promotion_scope_selector
	 * @access protected
	 */
	protected function _get_promotion_scope_selector() {
		$values = array();
		foreach ( EE_Registry::instance()->CFG->addons->promotions->scopes as $scope_name => $scope ) {
			$values[] = array(
				'text' => $scope->label->singular,
				'id' => $scope_name
			);
		}
		$redeemed = $this->_promotion->redeemed();
		$name = $redeemed > 0 ? 'PRO_scope_disabled' : 'PRO_scope';
		$default = $this->_promotion->scope();
		$extra_params = $redeemed > 0 ? 'disabled="disabled"' : '';
		$content = EEH_Form_Fields::select_input( $name, $values, $default, $extra_params );
		if ( $redeemed > 0 ) {
			$content .= '<input type="hidden" name="PRO_scope" value=' . $default . '">';
		}
		return $content;
	}



	/**
	 * Takes care of inserting/updating promotions.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $new Default false. Whether inserting or not.
	 * @return void
	 */
	protected function _insert_update_promotions( $new = FALSE ) {
		$promotion_values = array(
			'PRO_ID' => !empty( $this->_req_data['PRO_ID'] ) ? $this->_req_data['PRO_ID'] : 0,
			'PRO_code' => !empty( $this->_req_data['PRO_code'] ) ? $this->_req_data['PRO_code'] : NULL,
			'PRO_scope' => !empty( $this->_req_data['PRO_scope'] ) ? $this->_req_data['PRO_scope'] : 'Event',
			'PRO_start' => !empty( $this->_req_data['PRO_start'] ) ? $this->_req_data['PRO_start'] : NULL,
			'PRO_end' => ! empty( $this->_req_data['PRO_end'] ) ? $this->_req_data['PRO_end'] : NULL,
			'PRO_uses' => ! empty( $this->_req_data['PRO_uses'] ) ? $this->_req_data['PRO_uses'] : EE_INF_IN_DB,
			'PRO_accept_msg' => ! empty( $this->_req_data['PRO_accept_msg'] ) ? $this->_req_data['PRO_accept_msg'] : '',
			'PRO_decline_msg' => !empty( $this->_req_data['PRO_decline_msg'] ) ? $this->_req_data['PRO_decline_msg'] : ''
		);
		$promo_price_values = array(
			'PRC_ID' => !empty( $this->_req_data['PRC_ID'] ) ? $this->_req_data['PRC_ID'] : 0,
			'PRC_name' => !empty( $this->_req_data['PRC_name'] ) ? $this->_req_data['PRC_name'] : __('Special Promotion', 'event_espresso'),
			'PRT_ID' => !empty( $this->_req_data['PRT_ID'] ) ? $this->_req_data['PRT_ID'] : 0,
			'PRC_amount' => !empty( $this->_req_data['PRC_amount'] ) ? $this->_req_data['PRC_amount'] : 0,
			'PRC_desc' => !empty( $this->_req_data['PRC_desc'] ) ? $this->_req_data['PRC_desc'] : ''
		);

		//first handle the price object
		$price = empty( $promo_price_values['PRC_ID'] ) ? EE_Price::new_instance( $promo_price_values ) : EEM_Price::instance()->get_one_by_ID( $promo_price_values['PRC_ID'] );

		if ( ! empty( $promo_price_values['PRC_ID'] ) ) {
			//PRE-EXISTING PRICE so let's update the values.
			foreach( $promo_price_values as $field => $value ) {
				$price->set( $field, $value );
			}
		}

		//save price
		$price->save();

		//next handle the promotions
		$promotion = empty( $promotion_values['PRO_ID'] ) ? EE_Promotion::new_instance( $promotion_values ) : EEM_Promotion::instance()->get_one_by_ID( $promotion_values['PRO_ID'] );

		if ( !empty( $promotion_values['PRO_ID'] ) ) {
			//PRE-EXISTING promotion so let's update the values
			foreach ( $promotion_values as $field => $value ) {
				$promotion->set( $field, $value );
			}
		} else {
			//new promotion so let's add the price id for the price relation
			$promotion->set( 'PRC_ID', $price->ID() );
		}

		//save promotion
		$promotion->save();

		//hook for scopes and others to do their stuff.
		do_action( 'AHEE__Promotions_Admin_Page___insert_update_promotion__after', $promotion, $this->_req_data );

		if ( $promotion instanceof EE_Promotion && $new ) {
			EE_Error::add_success( __('Promotion has been successfully created.', 'event_espresso') );
		} else if ( $promotion instanceof EE_Promotion && ! $new ) {
			EE_Error::add_success( __('Promotion has been successfully updated.', 'event_espresso') );
		} else {
			EE_Error::add_error( __('Something went wrong with saving the promotion', 'event_espresso' ), __FILE__, __FUNCTION__, __LINE__ );
		}

		$query_args = array(
			'PRO_ID' => $promotion->ID(),
			'action' => 'edit'
			);
		$this->_redirect_after_action( NULL, '', '', $query_args, TRUE );
	}




	/**
	 * Duplicates a promotion.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	protected function _duplicate_promotion() {
		$success = TRUE;
		//first verify we have a promotion id
		$pro_id = !empty( $this->_req_data['PRO_ID'] ) ? $this->_req_data['PRO_ID'] : 0;
		if ( empty( $pro_id ) ) {
			$success = FALSE;
			EE_Error::add_error( __('Unable to duplicate the promotion because there was no ID present in the request.', 'event_espresso' ) );
		}

		if ( $success ) {
			$orig_promo = EEM_Promotion::instance()->get_one_by_ID( $pro_id );
			$new_promo = $orig_promo instanceof EE_Promotion ? clone $orig_promo : NULL;

			if ( ! $new_promo instanceof EE_Promotion ) {
				$success = FALSE;
				EE_Error::add_error( __('Unable to duplicate the promotion because for some reason there isn\'t a promotion in the database for the given id.', 'event_espresso') );
			}

			if ( $success ) {
				$new_promo->set('PRO_ID', 0);
				$new_promo->save();
				//we have to clone the promotion objects as well and then attach them to the new promo.
				$promo_objects = $orig_promo->promotion_objects();
				foreach ( $promo_objects as $promo_obj ) {
					$new_promo_obj = clone $promo_obj;
					$new_promo_obj->set('POB_ID', 0);
					$new_promo_obj->set('PRO_ID', $new_promo->ID() );
					$new_promo_obj->save();
				}

				//clone price obj
				$price_obj = $orig_promo->get_first_related('Price');
				$new_price = clone $price_obj;
				$new_price->set('PRC_ID', 0 );
				$new_price->save();
				$new_promo->set('PRC_ID', $new_price->ID() );
				$new_promo->save();
			}
		}

		if ( $success ) {
			EE_Error::add_success( __('Promotion successfully duplicated, make any additional edits and update.', 'event_espresso' ) );
		}

		$query_args = array(
			'PRO_ID' => $pro_id,
			'action' => !empty( $pro_id ) ? 'edit' : 'default'
			);
		$this->_redirect_after_action( NULL, '', '', $query_args, TRUE );
	}



	/**
	 * Handles either trashing or restoring a given promotion.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $trash if true trashing, otherwise restoring
	 * @param int    $PRO_ID if set then this will be used for the promotion id to trash/restore.
	 * @param bool $redirect if true then redirect otherwise don't
	 * @return bool value of $success
	 */
	protected function _trash_or_restore_promotion( $trash = FALSE, $PRO_ID = 0, $redirect = TRUE ) {
		$success = TRUE;
		$PRO_ID = !empty( $PRO_ID ) ? $PRO_ID : 0;
		$PRO_ID = !empty( $this->_req_data['PRO_ID'] ) && empty( $PRO_ID ) ? $this->_req_data['PRO_ID'] : $PRO_ID;
		if ( empty( $PRO_ID )  ) {
			$success = FALSE;
			if ( $trash ) {
				EE_Error::add_error( __('Unable to trash a promotion because no promotion id is accessible.', 'event_espresso'), __FILE__, __FUNCTION__, __LINE__ );
			} else {
				EE_Error::add_error( __('Unable to restore a promotion because no promotion id is accessible.', 'event_espresso'), __FILE__, __FUNCTION__, __LINE__ );
			}
		}

		if ( $success ) {
			$success = $trash ?  EEM_Promotion::instance()->delete_by_ID( $PRO_ID ) : EEM_Promotion::instance()->restore_by_ID( $PRO_ID );
		}

		if ( $success )
			$trash ? EE_Error::add_success( __('Promotion successfully trashed.', 'event_espresso' ) ) : EE_Error::add_success( __('Promotion successfully restored.', 'event_espresso') );
		else
			$trash ? EE_Error::add_error( __('Promotion did not get trashed.', 'event_espresso'), __FILE__, __FUNCTION__, __LINE__ ) : EE_Error::add_error( __('Promotion did not get restored.', 'event_espresso'), __FILE__, __FUNCTION__, __LINE__ );

		if ( $redirect ) {
			$query_args = array(
				'action' => !empty( $this->_req_data['update_promotion_nonce'] ) ? 'edit' : 'default',
				'PRO_ID' => $PRO_ID
				);
			$this->_redirect_after_action( NULL, '', '', $query_args, TRUE );
		} else {
			return $success;
		}
	}




	/**
	 * Trash or restore multiple promotions.  Expecting an array of promotion ids in the request.
	 *
	 * @since  1.0.0
	 *
	 * @param bool $trash if true then trash otherwise restore.
	 * @return void
	 */
	protected function _trash_or_restore_promotions( $trash = FALSE ) {
		$success = TRUE;
		$pro_ids = !empty( $this->_req_data['PRO_ID'] ) && is_array( $this->_req_data['PRO_ID'] ) ? $this->_req_data['PRO_ID'] : array();

		if ( empty( $pro_ids ) ) {
			$success = FALSE;
			$trash ? EE_Error::add_error( __('Unable to trash promotions because none were selected.', 'event_espresso'), __FILE__, __FUNCTION__, __LINE__  ) : EE_Error::add_error( __('Unable to trash promotions because none were selected.', 'event_espresso'), __FILE__, __FUNCTION__, __LINE__  );
		}

		$has_success = 0;
		foreach ( $pro_ids as $pro_id ) {
			$success = $this->_trash_or_restore_promotion( $trash, $pro_id, FALSE );
			if ( $success )
			 	$has_success + 1;
			else
				$trash ? EE_Error::add_error( sprintf( __('Promotion (%s) did not get trashed.', 'event_espresso'), $pro_id ), __FILE__, __FUNCTION__, __LINE__ ) : EE_Error::add_error( sprintf( __('Promotion (%s) did not get restored.', 'event_espresso'), $pro_id ), __FILE__, __FUNCTION__, __LINE__ );
		}

		if ( $has_success > 0 ) {
			$trash ? EE_Error::add_success( _n( '1 promotion was successfully trashed', '%s promotions were successfully trashed', $has_success, 'event_espresso' ) ) : EE_Error::add_success( _n( '1 promotion was successfully restored', '%s promotions were successfully restored', $has_success, 'event_espresso' ) );
		}

		$query_args = array(
			'action' => 'default'
			);
		$this->_redirect_after_action( NULL, '', '', $query_args, TRUE );
	}




	/**
	 * Delete permanently a promotion.
	 *
	 * @since  1.0.0
	 *
	 * @param int  $PRO_ID    if given this is the id for the promotion to be permanently deleted.
	 * @param bool $redirect if true then redirect, otherwise don't
	 * @return bool value of $success;
	 */
	protected function _delete_promotion( $PRO_ID = 0, $redirect = TRUE ){
		$success = TRUE;
		$PRO_ID = !empty( $PRO_ID ) ? $PRO_ID : 0;
		$PRO_ID = empty( $PRO_ID ) && ! empty( $this->_req_data['PRO_ID'] ) ? $this->_req_data['PRO_ID'] : $PRO_ID;

		if ( empty( $PRO_ID )  ) {
			$success = FALSE;
			EE_Error::add_error( __('Unable to delete permanently the promotion because no promotion id is accessible.', 'event_espresso'), __FILE__, __FUNCTION__, __LINE__ );
		}


		if ( $success ) {
			//first we need to determine if the given promotion has uses.  If it does it CANNOT be deleted.
			$promotion = EEM_Promotion::instance()->get_one_by_ID( $PRO_ID );
			if ( ! $promotion instanceof EE_Promotion ) {
				$success = FALSE;
				EE_Error::add_error( sprintf( __('There is no promotion in the db matching the id of %s.', 'event_espresso'), $PRO_ID ) );
			} else {
				if ( $promotion->redeemed() > 0 ) {
					$success = FALSE;
					EE_Error::add_error( sprintf( __('Unable to delete %s because it has been redeemed.  For archival and relational purposes this data must be retained in the db.', 'event_espresso'), $promotion->name() ) );
				} else {
					//first delete permanently the related prices
					$promotion->delete_related_permanently( 'Price' );
					//next delete related promotion objects permanently
					$promotion->delete_related_permanently( 'Promotion_Object' );

					//now delete the promotion permanently
					$success = $promotion->delete_permanently();
				}
			}
		}

		if ( $success )
			EE_Error::add_success( sprintf( __('Promotion %s has been permanently deleted.', 'event_espresso'), $promotion->name() ) );
		else
			EE_Error::add_error( sprintf( __('Unable to permanently delete the %s promotion.', 'event_espresso'), $promotion->name() ), __FILE__, __FUNCTION__, __LINE__ );

		if ( $redirect ) {
			$query_args = array(
				'action' => !empty( $this->_req_data['update_promotion_nonce'] ) ? 'edit' : 'default',
				'PRO_ID' => $PRO_ID
				);
			$this->_redirect_after_action( NULL, '', '', $query_args, TRUE );
		} else {
			return $success;
		}

	}




	/**
	 * Delete permanently multiple promotions.  The promotion IDs are expected to be in the request.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	protected function _delete_promotions(){
		$success = TRUE;
		$pro_ids = !empty( $this->_req_data['PRO_ID'] ) && is_array( $this->_req_data['PRO_ID'] ) ? $this->_req_data['PRO_ID'] : array();

		if ( empty( $pro_ids ) ) {
			$success = FALSE;
			EE_Error::add_error( __('Unable to delete permanently any promotions because none were selected.', 'event_espresso'), __FILE__, __FUNCTION__, __LINE__  );
		}

		$has_success = 0;
		foreach ( $pro_ids as $pro_id ) {
			$success = $this->_delete_promotion( $pro_id, FALSE );
			if ( $success )
			 	$has_success + 1;
			 //if no success the error message has already been generated.
		}

		if ( $has_success > 0 ) {
			EE_Error::add_success( _n( '1 promotion was successfully deleted.', '%s promotions were successfully deleted.', $has_success, 'event_espresso' ) );
		}

		$query_args = array(
			'action' => 'default'
			);
		$this->_redirect_after_action( NULL, '', '', $query_args, TRUE );
	}




	/**
	 * _basic_settings
	 * @access protected
	 */
	protected function _basic_settings() {
		$this->_settings_page( 'promotions_basic_settings.template.php' );
	}




	/**
	 * _settings_page
	 * @access protected
	 * @param $template
	 */
	protected function _settings_page( $template ) {
		EE_Registry::instance()->load_helper( 'Form_Fields' );
		EED_Promotions::instance()->set_config();
		$this->_template_args['config'] = EE_Registry::instance()->CFG->addons->promotions;

		add_filter( 'FHEE__EEH_Form_Fields__label_html', '__return_empty_string' );

		$this->_template_args['yes_no_values'] = array(
			EE_Question_Option::new_instance( array( 'QSO_value' => 0, 'QSO_desc' => __('No', 'event_espresso'))),
			EE_Question_Option::new_instance( array( 'QSO_value' => 1, 'QSO_desc' => __('Yes', 'event_espresso')))
		);

		$this->_template_args['banner_template'] = array(
			EE_Question_Option::new_instance( array( 'QSO_value' => 'promo-banner-ribbon.template.php', 'QSO_desc' => __('Ribbon Banner', 'event_espresso'))),
			EE_Question_Option::new_instance( array( 'QSO_value' => 'promo-banner-plain-text.template.php', 'QSO_desc' => __('Plain Text', 'event_espresso'))),
			EE_Question_Option::new_instance( array( 'QSO_value' => '', 'QSO_desc' => __('Do Not Display Promotions', 'event_espresso')))
		);

		$this->_template_args['ribbon_banner_color'] = array(
			EE_Question_Option::new_instance( array( 'QSO_value' => 'lite-blue', 'QSO_desc' => __('lite-blue', 'event_espresso'))),
			EE_Question_Option::new_instance( array( 'QSO_value' => 'blue', 'QSO_desc' => __('blue', 'event_espresso'))),
			EE_Question_Option::new_instance( array( 'QSO_value' => 'green', 'QSO_desc' => __('green', 'event_espresso'))),
			EE_Question_Option::new_instance( array( 'QSO_value' => 'pink', 'QSO_desc' => __('pink', 'event_espresso'))),
			EE_Question_Option::new_instance( array( 'QSO_value' => 'red', 'QSO_desc' => __('red', 'event_espresso')))
		);

		$this->_template_args = add_filter( 'FHEE__Promotions_Admin_Page___settings_page___template_args', $this->_template_args );

		$this->_template_args['return_action'] = $this->_req_action;
		$this->_template_args['reset_url'] = EE_Admin_Page::add_query_args_and_nonce( array('action'=> 'reset_settings','return_action'=>$this->_req_action), EE_PROMOTIONS_ADMIN_URL );
		$this->_set_add_edit_form_tags( 'update_settings' );
		$this->_set_publish_post_box_vars( NULL, FALSE, FALSE, NULL, FALSE);
		$this->_template_args['admin_page_content'] = EEH_Template::display_template( EE_PROMOTIONS_ADMIN_TEMPLATE_PATH . $template, $this->_template_args, TRUE );
		$this->display_admin_page_with_sidebar();
	}



	/**
	 * 	_usage
	 * @access protected
	 */
	protected function _usage() {
		$this->_template_args['admin_page_content'] = EEH_Template::display_template( EE_PROMOTIONS_ADMIN_TEMPLATE_PATH . 'promotions_usage_info.template.php', array(), TRUE );
		$this->display_admin_page_with_no_sidebar();
	}



	/**
	 * 	_update_settings
	 * @access protected
	 */
	protected function _update_settings(){

		EE_Registry::instance()->load_helper( 'Class_Tools' );

		if ( isset( $_POST['reset_promotions'] ) && $_POST['reset_promotions'] == '1' ){
			$config = new EE_Promotions_Config();
			$count = 1;
		} else {

			$config =  EE_Registry::instance()->CFG->addons->promotions;
			$count=0;
			//otherwise we assume you want to allow full html
			foreach($this->_req_data['promotions'] as $top_level_key => $top_level_value){
				if(is_array($top_level_value)){
					foreach($top_level_value as $second_level_key => $second_level_value){
						if ( EEH_Class_Tools::has_property( $config, $top_level_key ) && EEH_Class_Tools::has_property( $config->$top_level_key, $second_level_key ) && $second_level_value != $config->$top_level_key->$second_level_key ) {
							$config->$top_level_key->$second_level_key = $this->_sanitize_config_input( $top_level_key, $second_level_key, $second_level_value );
							$count++;
						}
					}
				}else{
					if ( EEH_Class_Tools::has_property($config, $top_level_key) && $top_level_value != $config->$top_level_key){
						$config->$top_level_key = $this->_sanitize_config_input($top_level_key, NULL, $top_level_value);
						$count++;
					}
				}
			}
		}
		EE_Registry::instance()->CFG->update_config( 'addons', 'promotions', $config );
		$this->_redirect_after_action( $count, 'Settings', 'updated', array('action' => $this->_req_data['return_action']));
	}



	/**
	 * _sanitize_config_input
	 * @param $top_level_key
	 * @param $second_level_key
	 * @param $value
	 * @return int|null
	 */
	private function _sanitize_config_input( $top_level_key, $second_level_key, $value ){
		$sanitization_methods = array(
			'display'=>array(
				'enable_promotions'=>'bool',
			),
			'banner_template'=>'plaintext',
			'ribbon_banner_color'=>'plaintext',
		);
		$sanitization_method = NULL;
		if(isset($sanitization_methods[$top_level_key]) &&
				$second_level_key === NULL &&
				! is_array($sanitization_methods[$top_level_key]) ){
			$sanitization_method = $sanitization_methods[$top_level_key];
		}elseif(is_array($sanitization_methods[$top_level_key]) && isset($sanitization_methods[$top_level_key][$second_level_key])){
			$sanitization_method = $sanitization_methods[$top_level_key][$second_level_key];
		}
//		echo "$top_level_key [$second_level_key] with value $value will be sanitized as a $sanitization_method<br>";
		switch($sanitization_method){
			case 'bool':
				return (boolean)intval($value);
			case 'plaintext':
				return wp_strip_all_tags($value);
			case 'int':
				return intval($value);
			case 'html':
				return $value;
			default:
				$input_name = $second_level_key == NULL ? $top_level_key : $top_level_key."[".$second_level_key."]";
				EE_Error::add_error(sprintf(__("Could not sanitize input '%s' because it has no entry in our sanitization methods array", "event_espresso"),$input_name));
				return NULL;

		}
	}





}
// End of file Promotions_Admin_Page.core.php
// Location: /wp-content/plugins/espresso-promotions/admin/promotions/Promotions_Admin_Page.core.php
