<?php
/**
 * This file contains the Promotions_Admin_Page_Init class
 *
 * @since 1.0.0
 * @package EE Promotions
 * @subpackage admin
 */
 if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');

/**
* Promotions_Admin_Page_Init class
*
* This is the init for the Promotions Addon Admin Pages.  See EE_Admin_Page_Init for method
* inline docs.
*
* @since 1.0.0
*
* @package		EE Promotions
* @subpackage		admin
* @author		Darren Ethier
*
* ------------------------------------------------------------------------
*/
class Promotions_Admin_Page_Init extends EE_Admin_Page_Init  {

	/**
	 * 	constructor
	 *
	 */
	public function __construct() {

		do_action( 'AHEE_log', __FILE__, __FUNCTION__, '' );

		define( 'PROMOTIONS_PG_SLUG', 'espresso_promotions' );
		define( 'PROMOTIONS_LABEL', __( 'Promotions', 'event_espresso' ));
		define( 'EE_PROMOTIONS_ADMIN_URL', admin_url( 'admin.php?page=' . PROMOTIONS_PG_SLUG ) );
		define( 'EE_PROMOTIONS_ADMIN_ASSETS_PATH', EE_PROMOTIONS_ADMIN . 'assets' . DS );
		define( 'EE_PROMOTIONS_ADMIN_ASSETS_URL', EE_PROMOTIONS_URL . 'admin/promotions/assets/' );
		define( 'EE_PROMOTIONS_ADMIN_TEMPLATE_PATH', EE_PROMOTIONS_ADMIN . 'templates' . DS );
		define( 'EE_PROMOTIONS_ADMIN_TEMPLATE_URL', EE_PROMOTIONS_ADMIN_URL . 'templates' . DS );

		parent::__construct();
		$this->_folder_path = EE_PROMOTIONS_ADMIN;
	}



	/**
	 * 	_set_init_properties
	 * @access protected
	 */
	protected function _set_init_properties() {
		$this->label = PROMOTIONS_LABEL;
	}

	// ADDED JUST DEV DOESN'T BLOW UP - remove after new EE Menu Item classes get merged to DEV
	function get_menu_map() {
		if ( !empty( $this->_menu_map ) )
			return $this->_menu_map; //we have menu maps!
		return array(); //still old system
	}


	/**
	 * 	_set_menu_map
	 * @access protected
	 */
	protected function _set_menu_map() {
		$this->_menu_map = new EE_Admin_Page_Sub_Menu( array(
			'menu_group' => 'addons',
			'menu_order' => 25,
			'show_on_menu' => TRUE,
			'parent_slug' => 'espresso_events',
			'menu_slug' => PROMOTIONS_PG_SLUG,
			'menu_label' => PROMOTIONS_LABEL,
			'capability' => 'ee_read_promotions',
			'admin_init_page' => $this
			));
	}

}
// End of file Promotions_Admin_Page_Init.core.php
// Location: /wp-content/plugins/espresso-promotions/admin/promotions/Promotions_Admin_Page_Init.core.php
