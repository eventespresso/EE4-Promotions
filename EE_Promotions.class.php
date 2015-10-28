<?php if ( ! defined( 'EVENT_ESPRESSO_VERSION' )) { exit(); }

// define the plugin directory path and URL
define( 'EE_PROMOTIONS_BASENAME', plugin_basename( EE_PROMOTIONS_PLUGIN_FILE ));
define( 'EE_PROMOTIONS_PATH', plugin_dir_path( __FILE__ ));
define( 'EE_PROMOTIONS_URL', plugin_dir_url( __FILE__ ));
define( 'EE_PROMOTIONS_ADMIN', EE_PROMOTIONS_PATH . 'admin' . DS . 'promotions' . DS );
define( 'EE_PROMOTIONS_CORE', EE_PROMOTIONS_PATH . 'core' . DS);

/**
 * ------------------------------------------------------------------------
 *
 * Class  EE_Promotions
 *
 * @package			Event Espresso
 * @subpackage		espresso-promotions
 * @author			    Brent Christensen
 * @ version		 	$VID:$
 *
 * ------------------------------------------------------------------------
 */
Class  EE_Promotions extends EE_Addon {



	/**
	 * register_addon
	 */
	public static function register_addon() {
		// register addon via Plugin API
		EE_Register_Addon::register(
		'Promotions',
			array(
				'version' 					=> EE_PROMOTIONS_VERSION,
				'min_core_version' 	=> EE_PROMOTIONS_CORE_VERSION_REQUIRED,
				'main_file_path' 	=> EE_PROMOTIONS_PLUGIN_FILE,
				'admin_path' 		=> EE_PROMOTIONS_ADMIN,
				'plugin_slug' 		=> 'espresso_promotions',
				'config_class' 		=> 'EE_Promotions_Config',
				'config_name' 		=> 'promotions',
				//'plugins_page_row'	=> EE_Promotions::plugins_page_row(),
				'dms_paths' 		=> array( EE_PROMOTIONS_CORE . 'data_migration_scripts' . DS ),
				'module_paths' 		=> array( EE_PROMOTIONS_PATH . 'EED_Promotions.module.php' ),
				'shortcode_paths' 	=> array( EE_PROMOTIONS_PATH . 'EES_Espresso_Promotions.shortcode.php' ),
				'widget_paths' 		=> array( EE_PROMOTIONS_PATH . 'EEW_Promotions.widget.php' ),
				// register autoloaders
				'autoloader_paths' => array(
					'EE_Promotions_Config' 			=> EE_PROMOTIONS_PATH . 'EE_Promotions_Config.php',
					'Promotions_Admin_Page_Init' 	=> EE_PROMOTIONS_ADMIN . 'Promotions_Admin_Page_Init.core.php',
					'Promotions_Admin_Page' 		=> EE_PROMOTIONS_ADMIN . 'Promotions_Admin_Page.core.php',
					'Promotions_Admin_List_Table' 	=> EE_PROMOTIONS_ADMIN . 'Promotions_Admin_List_Table.class.php',
					'EE_Promotion_Scope' 			=> EE_PROMOTIONS_PATH . 'lib' . DS . 'scopes' . DS . 'EE_Promotion_Scope.lib.php'
				),
				'autoloader_folders' => array(
					'Promotions_Plugin_API' 	=> EE_PROMOTIONS_PATH . 'lib' . DS . 'plugin_api',
				),
				'pue_options'			=> array(
					'pue_plugin_slug' 	=> 'eea-promotions',
					'checkPeriod' 		=> '24',
					'use_wp_update' 	=> FALSE
				),
				// EE_Register_Model
				'model_paths'	=> array( EE_PROMOTIONS_CORE . 'db_models' ),
				'class_paths'	=> array( EE_PROMOTIONS_CORE . 'db_classes' ),
				// EE_Register_Model_Extensions
				'model_extension_paths'	=> array( EE_PROMOTIONS_CORE . 'db_model_extensions' . DS ),
				'class_extension_paths'		=> array( EE_PROMOTIONS_CORE . 'db_class_extensions'  . DS ),
				'capabilities' => array(
					'administrator' => array(
						'ee_read_promotion',
						'ee_read_promotions',
						'ee_read_others_promotions',
						'ee_edit_promotion',
						'ee_edit_promotions',
						'ee_edit_others_promotions',
						'ee_delete_promotion',
						'ee_delete_promotions',
						'ee_delete_others_promotions',
						)
					),
				'capability_maps' => array(
					0 => array( 'EE_Meta_Capability_Map_Read' => array(
						'ee_read_promotion',
						array( 'Promotion', '', 'ee_read_others_promotions', '' )
						) ),
					1 => array( 'EE_Meta_Capability_Map_Edit' => array(
						'ee_edit_promotion',
						array( 'Promotion', '', 'ee_edit_others_promotions', '' )
						) ),
					2 => array( 'EE_Meta_Capability_Map_Delete' => array(
						'ee_delete_promotion',
						array( 'Promotion', '', 'ee_delete_others_promotions', '' )
						) )
					)
			)
		);
		//register promotion specific statuses
		add_filter( 'FHEE__EEM_Status__localized_status__translation_array', array( 'EE_Promotions', 'promotion_stati' ), 10 );
	}





	/**
	 * plugins_page_row
	 *
	 * HTML to appear within a new table row on the WP Plugins page, below the promotions plugin row
	 *
	 * @return array
	 */
	public static function plugins_page_row() {
		return array(
				'link_text' 	=> 'Promotions Addon Upsell Info',
				'link_url' 		=> '#',
				'description' 	=> 'To edit me, open up ' . __FILE__ . ' and find the ' . __METHOD__ . '() method',
		);
	}





	/**
	 * This registers the localization for the promotion statuses with the EEM_Status
	 * translation array
	 *
	 * @param array  $stati_translation Current localized stati
	 *
	 * @return array  Current stati with promotion stati appended.
	 */
	public static function promotion_stati( $stati_translation ) {
		$promotion_stati = array(
			EE_Promotion::upcoming => array(
				__('upcoming', 'event_espresso'),
				__('upcoming', 'event_espresso')
				),
			EE_Promotion::active => array(
				__('active', 'event_espresso'),
				__('active', 'event_espresso')
				),
			EE_Promotion::expired => array(
				__('expired', 'event_espresso'),
				__('expired', 'event_espresso')
				),
			EE_Promotion::unavailable => array(
				__('unavailable', 'event_espresso'),
				__('unavailable', 'event_espresso')
				)
			);
		return array_merge( $stati_translation, $promotion_stati );
	}









}
// End of file EE_Promotions.class.php
// Location: wp-content/plugins/espresso-promotions/EE_Promotions.class.php
