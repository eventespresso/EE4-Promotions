<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');
/**
* Event Espresso
*
* Event Registration and Management Plugin for WordPress
*
* @ package 		Event Espresso
* @ author			Seth Shoultes
* @ copyright 	(c) 2008-2011 Event Espresso  All Rights Reserved.
* @ license 		{@link http://eventespresso.com/support/terms-conditions/}   * see Plugin Licensing *
* @ link 				{@link http://www.eventespresso.com}
* @ since		 	$VID:$
*
* ------------------------------------------------------------------------
*
* Promotions_Admin_Page_Init class
*
* This is the init for the Promotions Addon Admin Pages.  See EE_Admin_Page_Init for method inline docs.
*
* @package			Event Espresso (promotions addon)
* @subpackage		admin/Promotions_Admin_Page_Init.core.php
* @author				Darren Ethier
*
* ------------------------------------------------------------------------
*/
class Promotions_Admin_Page_Init extends EE_Admin_Page_Init  {

	/**
	 * 	constructor
	 *
	 * @access public
	 * @return \Promotions_Admin_Page_Init
	 */
	public function __construct() {

		do_action( 'AHEE_log', __FILE__, __FUNCTION__, '' );

		define( 'PROMOTIONS_PG_SLUG', 'espresso_promotions' );
		define( 'PROMOTIONS_LABEL', __( 'Promotions', 'event_espresso' ));
		define( 'EE_PROMOTIONS_ADMIN_URL', admin_url( 'admin.php?page=' . PROMOTIONS_PG_SLUG ));
		define( 'EE_PROMOTIONS_ADMIN_ASSETS_PATH', EE_PROMOTIONS_ADMIN . 'assets' . DS );
		define( 'EE_PROMOTIONS_ADMIN_ASSETS_URL', EE_PROMOTIONS_URL . 'assets' . DS );
		define( 'EE_PROMOTIONS_ADMIN_TEMPLATE_PATH', EE_PROMOTIONS_ADMIN . 'templates' . DS );
		define( 'EE_PROMOTIONS_ADMIN_TEMPLATE_URL', EE_PROMOTIONS_URL . 'templates' . DS );

		parent::__construct();
		$this->_folder_path = EE_PROMOTIONS_ADMIN;

	}





	protected function _set_init_properties() {
		$this->label = PROMOTIONS_LABEL;
		$this->menu_label = PROMOTIONS_LABEL;
		$this->menu_slug = PROMOTIONS_PG_SLUG;
		$this->capability = 'administrator';
	}



	/**
	*		sets vars in parent for creating admin menu entry
	*
	*		@access 		public
	*		@return 		void
	*/
	public function get_menu_map() {
		$map = array(
			'group' => 'settings',
			'menu_order' => 25,
			'show_on_menu' => TRUE,
			'parent_slug' => 'events'
			);
		return $map;
	}


}
// End of file Promotions_Admin_Page_Init.core.php
// Location: /wp-content/plugins/espresso-promotions/admin/promotions/Promotions_Admin_Page_Init.core.php