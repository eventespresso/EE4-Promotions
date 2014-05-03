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
				'edit' => __('Edit Promotion', 'event_espresso')
				),
			'publishbox' => array(
				'basic_settings' => __('Update Settings', 'event_espresso')
				)
		);
	}




	protected function _set_page_routes() {
		$this->_page_routes = array(
			'default' => '_list_table',
			'create_new' => '_add_promotion',
			'edit' => '_edit_promotion',
			'trash_promotion' => array(
				'func' => '_trash_or_restore_promotion',
				'args' => array( 'promotion_status' => 'trash' ),
				'noheader' => TRUE
				),
			'trash_promotions' => array(
				'func' => '_trash_or_restore_promotions',
				'args' => array( 'promotion_status' => 'trash' ),
				'noheader' => TRUE
				),
			'restore_promotion' => array(
				'func' => '_trash_or_restore_promotion',
				'args' => array( 'promotion_status' => 'draft' ),
				'noheader' => TRUE
				),
			'restore_promotions' => array(
				'func' => '_trash_or_restore_promotions',
				'args' => array( 'promotion_status' => 'draft' ),
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

		$this->_page_config = array(
			'default' => array(
				'nav' => array(
					'label' => __('Promotions Overview', 'event_espresso'),
					'order' => 10
					),
				'list_table' => 'Promotions_Admin_List_Table',
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
		wp_register_script( 'espresso_promotions_admin', EE_PROMOTIONS_ADMIN_ASSETS_URL . 'espresso_promotions_admin.js', array( 'espresso_core' ), EE_PROMOTIONS_VERSION, TRUE );
		wp_enqueue_script( 'espresso_promotions_admin');

		EE_Registry::$i18n_js_strings['confirm_reset'] = __( 'Are you sure you want to reset ALL your Event Espresso Promotions Information? This cannot be undone.', 'event_espresso' );
		wp_localize_script( 'espresso_promotions_admin', 'eei18n', EE_Registry::$i18n_js_strings );
	}

	public function admin_init() {}
	public function admin_notices() {}
	public function admin_footer_scripts() {}



	protected function _set_list_table_views_default() {
		$this->_views = array(
			'all' => array(
				'slug' => 'all',
				'label' => __('All', 'event_espresso'),
				'count' => 0,
				'bulk_action' => array(
					//'restore_venues' => __('Restore_from Trash', 'event_espresso'),
					'trash_promotions' => __('Move to Trash', 'event_espresso')
					//'delete_venues' => __('Delete', 'event_espresso')
					)
				),
			'trash' => array(
				'slug' => 'trash',
				'label' => __('Trashed', 'event_espresso'),
				'count' => 0,
				'bulk_action' => array(
					'restore_promotions' => __('Restore_from Trash', 'event_espresso'),
					//'trash_venues' => __('Move to Trash', 'event_espresso'),
					'delete_promotions' => __('Delete', 'event_espresso')
					)
				)
		);
	}




	protected function _list_table() {
		$this->_admin_page_title .= $this->get_action_link_or_button('create_new', 'add', array(), 'add-new-h2' );
		$this->display_admin_list_table_page_with_no_sidebar();
	}




	protected function _basic_settings() {
		$this->_settings_page( 'promotions_basic_settings.template.php' );
	}




	/**
	 * _settings_page
	 * @param $template
	 */
	protected function _settings_page( $template ) {
		EE_Registry::instance()->load_helper( 'Form_Fields' );
		$this->_template_args['promotions_config'] = EE_Config::instance()->get_config( 'addons', 'EED_Espresso_Promotions', 'EE_Promotions_Config' );
		add_filter( 'FHEE__EEH_Form_Fields__label_html', '__return_empty_string' );
		$this->_template_args['yes_no_values'] = array(
			EE_Question_Option::new_instance( array( 'QSO_value' => 0, 'QSO_desc' => __('No', 'event_espresso'))),
			EE_Question_Option::new_instance( array( 'QSO_value' => 1, 'QSO_desc' => __('Yes', 'event_espresso')))
		);

		$this->_template_args['return_action'] = $this->_req_action;
		$this->_template_args['reset_url'] = EE_Admin_Page::add_query_args_and_nonce( array('action'=> 'reset_settings','return_action'=>$this->_req_action), EE_PROMOTIONS_ADMIN_URL );
		$this->_set_add_edit_form_tags( 'update_settings' );
		$this->_set_publish_post_box_vars( NULL, FALSE, FALSE, NULL, FALSE);
		$this->_template_args['admin_page_content'] = EEH_Template::display_template( EE_PROMOTIONS_ADMIN_TEMPLATE_PATH . $template, $this->_template_args, TRUE );
		$this->display_admin_page_with_sidebar();
	}


	protected function _usage() {
		$this->_template_args['admin_page_content'] = EEH_Template::display_template( EE_PROMOTIONS_ADMIN_TEMPLATE_PATH . 'promotions_usage_info.template.php', array(), TRUE );
		$this->display_admin_page_with_no_sidebar();
	}

	protected function _update_settings(){
		EE_Registry::instance()->load_helper( 'Class_Tools' );
		if(isset($_POST['reset_promotions']) && $_POST['reset_promotions'] == '1'){
			$config = new EE_Promotions_Config();
			$count = 1;
		}else{
			$config = EE_Config::instance()->get_config( 'addons', 'EED_Espresso_Promotions', 'EE_Promotions_Config' );
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
		EE_Config::instance()->_update_config( 'addons', 'EED_Espresso_Promotions', $config );
		$this->_redirect_after_action( $count, 'Settings', 'updated', array('action' => $this->_req_data['return_action']));
	}

	/**
	 * resets the promotions data and redirects to where they came from
	 */
//	protected function _reset_settings(){
//		EE_Config::instance()->addons['promotions'] = new EE_Promotions_Config();
//		EE_Config::instance()->update_espresso_config();
//		$this->_redirect_after_action(1, 'Settings', 'reset', array('action' => $this->_req_data['return_action']));
//	}
	private function _sanitize_config_input( $top_level_key, $second_level_key, $value ){
		$sanitization_methods = array(
			'display'=>array(
				'enable_promotions'=>'bool',
//				'promotions_height'=>'int',
//				'enable_promotions_filters'=>'bool',
//				'enable_category_legend'=>'bool',
//				'use_pickers'=>'bool',
//				'event_background'=>'plaintext',
//				'event_text_color'=>'plaintext',
//				'enable_cat_classes'=>'bool',
//				'disable_categories'=>'bool',
//				'show_attendee_limit'=>'bool',
			)
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
