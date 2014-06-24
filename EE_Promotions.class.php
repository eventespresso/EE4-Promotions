<?php if ( ! defined( 'EVENT_ESPRESSO_VERSION' )) { exit(); }
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
	 * class constructor
	 */
	public function __construct() {
		$this->_activation_indicator_option_name = 'ee_espresso_promotions_activation';
		// register our activation hook
		register_activation_hook( __FILE__, array( $this, 'set_activation_indicator_option' ));
	}

	public static function register_addon() {
		// define the plugin directory path and URL
		define( 'EE_PROMOTIONS_PATH', plugin_dir_path( __FILE__ ));
		define( 'EE_PROMOTIONS_URL', plugin_dir_url( __FILE__ ));
		define( 'EE_PROMOTIONS_PLUGIN_FILE', plugin_basename( __FILE__ ));
		define( 'EE_PROMOTIONS_ADMIN', EE_PROMOTIONS_PATH . 'admin' . DS . 'promotions' . DS );
		define( 'EE_PROMOTIONS_CORE',EE_PROMOTIONS_PATH . DS . 'core' . DS);
		// register addon via Plugin API
		EE_Register_Addon::register(
			'Promotions',
			array(
				'version' 					=> EE_PROMOTIONS_VERSION,
				'min_core_version' => '4.3.0',
				'base_path' 				=> EE_PROMOTIONS_PATH,
				'admin_path' 			=> EE_PROMOTIONS_ADMIN,
				'plugin_slug' 			=> 'espresso_promotions',
				'config_class' 			=> 'EE_Promotions_Config',
				'config_name'			=> 'promotions',
				'dms_paths' 			=> array( EE_PROMOTIONS_CORE . 'data_migration_scripts' . DS ),
				'module_paths' 		=> array( EE_PROMOTIONS_PATH . 'EED_Promotions.module.php' ),
				'shortcode_paths' 	=> array( EE_PROMOTIONS_PATH . 'EES_Promotions.shortcode.php' ),
				'widget_paths' 		=> array( EE_PROMOTIONS_PATH . 'EEW_Promotions.widget.php' ),
				'autoloader_paths' => array(
					'EE_Promotions' 						=> EE_PROMOTIONS_PATH . 'EE_Promotions.class.php',
					'EE_Promotions_Config' 			=> EE_PROMOTIONS_PATH . 'EE_Promotions_Config.php',
					'Promotions_Admin_Page' 		=> EE_PROMOTIONS_ADMIN . 'Promotions_Admin_Page.core.php',
					'Promotions_Admin_Page_Init' => EE_PROMOTIONS_ADMIN . 'Promotions_Admin_Page_Init.core.php',
					'Promotions_Admin_List_Table' => EE_PROMOTIONS_ADMIN . 'Promotions_Admin_List_Table.class.php'
				),
				'pue_options'			=> array(
					'pue_plugin_slug' 		=> 'espresso-promotions',
					'plugin_basename' 	=> EE_PROMOTIONS_PLUGIN_FILE,
					'checkPeriod' 				=> '24',
					'use_wp_update' 		=> FALSE
				)
			)
		);
		EE_Register_Model::register('Promotions', array(
			'model_paths'=>array(EE_PROMOTIONS_CORE . 'db_models'),
			'class_paths'=>array(EE_PROMOTIONS_CORE . 'db_classes')
		));
		EE_Register_Model_Extensions::register('Promotions', array(
			'model_extension_paths'=>array(EE_PROMOTIONS_CORE . 'db_model_extensions'),
			'class_extension_paths'=>array(EE_PROMOTIONS_CORE . 'db_class_extensions')));

		//setup EEI_Plugin_API implementation for promotion scopes for other plugins to register a promotion scope.
		EEH_Autoloader::register_autoloaders_for_each_file_in_folder( EE_PROMOTIONS_PATH . 'lib/plugin_api' );


		//register promotion specific statuses
		add_filter( 'FHEE__EEM_Status__localized_status__translation_array', array( 'EE_Promotions', 'promotion_stati' ), 10 );
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
