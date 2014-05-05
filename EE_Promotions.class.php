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
		// register addon via Plugin API
		EE_Register_Addon::register(
			array(
				'addon_name'			=> 'Promotions',
				'version' 					=> EE_PROMOTIONS_VERSION,
				'min_core_version' => '4.3.0',
				'base_path' 				=> EE_PROMOTIONS_PATH,
				'admin_path' 			=> EE_PROMOTIONS_ADMIN,
				'plugin_slug' 			=> 'espresso_promotions',
				'admin_callback'		=> 'additional_admin_hooks',
				'config_class' 			=> 'EE_Promotions_Config',
				'config_name'			=> 'promotions',
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
	}









}
// End of file EE_Promotions.class.php
// Location: wp-content/plugins/espresso-promotions/EE_Promotions.class.php
