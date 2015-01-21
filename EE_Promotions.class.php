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
				'min_core_version' => EE_PROMOTIONS_CORE_VERSION_REQUIRED,
				'main_file_path' 		=> EE_PROMOTIONS_PLUGIN_FILE,
				'admin_path' 			=> EE_PROMOTIONS_ADMIN,
				'admin_callback'		=> 'additional_admin_hooks',
				'plugin_slug' 			=> 'espresso_promotions',
				'config_class' 			=> 'EE_Promotions_Config',
				'config_name'			=> 'promotions',
				'dms_paths' 			=> array( EE_PROMOTIONS_CORE . 'data_migration_scripts' . DS ),
				'module_paths' 		=> array( EE_PROMOTIONS_PATH . 'EED_Promotions.module.php' ),
				'shortcode_paths' 	=> array( EE_PROMOTIONS_PATH . 'EES_Espresso_Promotions.shortcode.php' ),
				'widget_paths' 		=> array( EE_PROMOTIONS_PATH . 'EEW_Promotions.widget.php' ),
				// register autoloaders
				'autoloader_paths' => array(
					'EE_Promotions' 							=> EE_PROMOTIONS_PATH . 'EE_Promotions.class.php',
					'EE_Promotions_Config' 				=> EE_PROMOTIONS_PATH . 'EE_Promotions_Config.php',
					'Promotions_Admin_Page' 			=> EE_PROMOTIONS_ADMIN . 'Promotions_Admin_Page.core.php',
					'Promotions_Admin_Page_Init' 	=> EE_PROMOTIONS_ADMIN . 'Promotions_Admin_Page_Init.core.php',
					'Promotions_Admin_List_Table' 	=> EE_PROMOTIONS_ADMIN . 'Promotions_Admin_List_Table.class.php'
				),
				'autoloader_folders' => array(
					'Promotion_Scopes_Plugin_API' 	=> EE_PROMOTIONS_PATH . 'lib' . DS . 'plugin_api',
					'Promotion_Scopes' 						=> EE_PROMOTIONS_PATH . 'lib' . DS . 'scopes'
				),
				'pue_options'			=> array(
					'pue_plugin_slug' 		=> 'eea-promotions',
					'checkPeriod' 				=> '24',
					'use_wp_update' 		=> FALSE
				),
				// EE_Register_Model
				'model_paths'	=> array( EE_PROMOTIONS_CORE . 'db_models' ),
				'class_paths'	=> array( EE_PROMOTIONS_CORE . 'db_classes' ),
				// EE_Register_Model_Extensions
				'model_extension_paths'	=> array( EE_PROMOTIONS_CORE . 'db_model_extensions' . DS ),
				'class_extension_paths'		=> array( EE_PROMOTIONS_CORE . 'db_class_extensions'  . DS )
			)
		);
		//register promotion specific statuses
		add_filter( 'FHEE__EEM_Status__localized_status__translation_array', array( 'EE_Promotions', 'promotion_stati' ), 10 );
	}



	/**
	 * 	additional_admin_hooks
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public function additional_admin_hooks() {
		// is admin and not in M-Mode ?
		if ( is_admin() && ! EE_Maintenance_Mode::instance()->level() ) {
			add_filter( 'plugin_action_links', array( $this, 'plugin_actions' ), 10, 2 );
		}
	}



	/**
	 * plugin_actions
	 *
	 * Add a settings link to the Plugins page, so people can go straight from the plugin page to the settings page.
	 * @param $links
	 * @param $file
	 * @return array
	 */
	public function plugin_actions( $links, $file ) {
		if ( $file == EE_PROMOTIONS_BASENAME ) {
			// before other links
			array_unshift( $links, '<a href="admin.php?page=espresso_promotions">' . __('Settings') . '</a>' );
		}
		return $links;
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
