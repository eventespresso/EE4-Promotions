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

	const activation_indicator_option_name = 'ee_espresso_promotions_activation';



	/**
	 * class constructor
	 */
	public function __construct() {
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
			'Promotions',
			array(
				'version' 					=> EE_PROMOTIONS_VERSION,
				'min_core_version' => '4.3.0',
				'base_path' 				=> EE_PROMOTIONS_PATH,
				'admin_path' 			=> EE_PROMOTIONS_ADMIN,
				'admin_callback'		=> 'additional_admin_hooks',
				'config_class' 			=> 'EE_Promotions_Config',
				'autoloader_paths' => array(
					'EE_Promotions' 						=> EE_PROMOTIONS_PATH . 'EE_Promotions.class.php',
					'EE_Promotions_Config' 			=> EE_PROMOTIONS_PATH . 'EE_Promotions_Config.php',
					'Promotions_Admin_Page' 		=> EE_PROMOTIONS_ADMIN . 'Promotions_Admin_Page.core.php',
					'Promotions_Admin_Page_Init' => EE_PROMOTIONS_ADMIN . 'Promotions_Admin_Page_Init.core.php',
					'Promotions_Admin_List_Table' => EE_PROMOTIONS_ADMIN . 'Promotions_Admin_List_Table.class.php'
				),
				'dms_paths' 			=> array( EE_PROMOTIONS_PATH . 'data_migration_scripts' . DS ),
				'module_paths' 		=> array( EE_PROMOTIONS_PATH . 'EED_Promotions.module.php' ),
				'shortcode_paths' 	=> array( EE_PROMOTIONS_PATH . 'EES_Promotions.shortcode.php' ),
				'widget_paths' 		=> array( EE_PROMOTIONS_PATH . 'EEW_Promotions.widget.php' ),
			)
		);
	}



	/**
	* get_db_update_option_name
	* @return string
	*/
	public function get_db_update_option_name(){
		return EE_Promotions::activation_indicator_option_name;
	}



	/**
	* Until we do something better, we'll just check for migration scripts upon
	* plugin activation only. In the future, we'll want to do it on plugin updates too
	*/
	public function set_activation_indicator_option(){
		//let's just handle this on the next request, ok? right now we're just not really ready
		update_option( EE_Promotions::activation_indicator_option_name, TRUE );
	}



	/**
	 * new_install - check for migration scripts
	 * @return mixed
	 */
	public function new_install() {
		//if core is also active, then get core to check for migration scripts
		//and set maintenance mode is necessary
		if ( get_option( EE_Promotions::activation_indicator_option_name )) {
			EE_Maintenance_Mode::instance()->set_maintenance_mode_if_db_old();
			delete_option( EE_Promotions::activation_indicator_option_name );
		}
	}



	/**
	 * upgrade - check for migration scripts
	 * @return mixed
	 */
	public function upgrade() {
		//if core is also active, then get core to check for migration scripts
		//and set maintenance mode is necessary
		if ( get_option( EE_Promotions::activation_indicator_option_name )) {
			EE_Maintenance_Mode::instance()->set_maintenance_mode_if_db_old();
			delete_option( EE_Promotions::activation_indicator_option_name );
		}
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
			add_action( 'action_hook_espresso_promotions_update_api', array( $this, 'load_pue_update' ));
		}
	}





	/**
	 * 	load_pue_update - Update notifications
	 *
	 *  @return 	void
	 */
	public function load_pue_update() {
		if ( ! defined( 'EVENT_ESPRESSO_PLUGINFULLPATH' )) {
			return;
		}
		if ( is_readable( EVENT_ESPRESSO_PLUGINFULLPATH . 'class/pue/pue-client.php' )) {
			//include the file
			require( EVENT_ESPRESSO_PLUGINFULLPATH . 'class/pue/pue-client.php' );
			// initiate the class and start the plugin update engine!
			new PluginUpdateEngineChecker(
			// host file URL
				'http://eventespresso.com',
				// plugin slug(s)
				array(
					'premium' => array('reg' => 'espresso-promotions-core'),
					'prerelease' => array('beta' => 'espresso-promotions-core-pr')
				),
				// options
				array(
					'apikey' => EE_Registry::instance()->NET_CFG->core->site_license_key,
					'lang_domain' => 'event_espresso',
					'checkPeriod' => '24',
					'option_key' => 'site_license_key',
					'options_page_slug' => 'event_espresso',
					'plugin_basename' => EE_PROMOTIONS_PLUGIN_FILE,
					// if use_wp_update is TRUE it means you want FREE versions of the plugin to be updated from WP
					'use_wp_update' => FALSE,
				)
			);
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
		if ( $file == EE_PROMOTIONS_PLUGIN_FILE ) {
			// before other links
			array_unshift( $links, '<a href="admin.php?page=espresso_promotions">' . __('Settings') . '</a>' );
		}
		return $links;
	}






}
// End of file EE_Promotions.class.php
// Location: wp-content/plugins/espresso-promotions/EE_Promotions.class.php
