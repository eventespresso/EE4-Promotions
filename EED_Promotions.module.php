<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
/*
 * Event Espresso
 *
 * Event Registration and Management Plugin for WordPress
 *
 * @ package		Event Espresso
 * @ author			Event Espresso
 * @ copyright	(c) 2008-2014 Event Espresso  All Rights Reserved.
 * @ license		http://eventespresso.com/support/terms-conditions/   * see Plugin Licensing *
 * @ link				http://www.eventespresso.com
 * @ version		 	$VID:$
 *
 * ------------------------------------------------------------------------
 */
/**
 * Class  EED_Promotions
 *
 * @package			Event Espresso
 * @subpackage		espresso-promotions
 * @author 				Brent Christensen
 *
 * ------------------------------------------------------------------------
 */
class EED_Promotions extends EED_Module {

	/**
	 * @var 		bool
	 * @access 	public
	 */
	public static $shortcode_active = FALSE;



	 /**
	  * 	set_hooks - for hooking into EE Core, other modules, etc
	  *
	  *  @access 	public
	  *  @return 	void
	  */
	 public static function set_hooks() {
		 EE_Config::register_route( 'promotions', 'EED_Promotions', 'run' );
	 }

	 /**
	  * 	set_hooks_admin - for hooking into EE Admin Core, other modules, etc
	  *
	  *  @access 	public
	  *  @return 	void
	  */
	 public static function set_hooks_admin() {
		 // ajax hooks
		 add_action( 'wp_ajax_get_promotions', array( 'EED_Promotions', '_get_promotions' ));
		 add_action( 'wp_ajax_nopriv_get_promotions', array( 'EED_Promotions', '_get_promotions' ));
	 }





	 /**
	  *    run - initial module setup
	  *
	  * @access    public
	  * @param  WP $WP
	  * @return    void
	  */
	 public function run( $WP ) {
		 EED_Promotions::_set_config();
		 add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ));
	 }






	/**
	 * 	enqueue_scripts - Load the scripts and css
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public function enqueue_scripts() {
		//Check to see if the promotions css file exists in the '/uploads/espresso/' directory
		if ( is_readable( EVENT_ESPRESSO_UPLOAD_DIR . "css/promotions.css")) {
			//This is the url to the css file if available
			wp_register_style( 'espresso_promotions', EVENT_ESPRESSO_UPLOAD_URL . 'css/espresso_promotions.css' );
		} else {
			// EE promotions style
			wp_register_style( 'espresso_promotions', EE_PROMOTIONS_URL . 'css/espresso_promotions.css' );
		}
		// promotions script
		wp_register_script( 'espresso_promotions', EE_PROMOTIONS_URL . 'scripts/espresso_promotions.js', array( 'jquery' ), EE_PROMOTIONS_VERSION, TRUE );

		// is the shortcode or widget in play?
		if ( EED_Promotions::$shortcode_active ) {
			wp_enqueue_style( 'espresso_promotions' );
			wp_enqueue_script( 'espresso_promotions' );
		}
	}




	 /**
	  *    _get_promotions
	  *
	  * @access    	public
	  * @return    	string
	  */
	public static function _get_promotions(  ) {
		// get promotions options
		$config = EED_Promotions::_get_config();
		return '';
	}




	 /**
	  *    display_promotions
	  *
	  * @access    	public
	  * @return    	string
	  */
	public function display_promotions(  ) {
		// get promotions options
		$config = EED_Promotions::_get_config();
		return '';
	}



	/**
	 *		@ override magic methods
	 *		@ return void
	 */
	public function __set($a,$b) { return FALSE; }
	public function __get($a) { return FALSE; }
	public function __isset($a) { return FALSE; }
	public function __unset($a) { return FALSE; }
	public function __clone() { return FALSE; }
	public function __wakeup() { return FALSE; }
	public function __destruct() { return FALSE; }

 }
// End of file EED_Promotions.module.php
// Location: /wp-content/plugins/espresso-promotions/EED_Promotions.module.php
