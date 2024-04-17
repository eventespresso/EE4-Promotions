<?php

use EventEspresso\core\domain\entities\admin\menu\AdminMenuItem;

/**
 * Promotions_Admin_Page_Init class
 * This is the init for the Promotions Addon Admin Pages.  See EE_Admin_Page_Init for method
 * inline docs.
 *
 * @since            1.0.0
 * @package          EE Promotions
 * @subpackage       admin
 * @author           Darren Ethier
 */
class Promotions_Admin_Page_Init extends EE_Admin_Page_Init
{
    /**
     *  constructor
     */
    public function __construct()
    {
        do_action('AHEE_log', __FILE__, __FUNCTION__, '');

        define('PROMOTIONS_PG_SLUG', 'espresso_promotions');
        define('PROMOTIONS_LABEL', esc_html__('Promotions', 'event_espresso'));
        define('EE_PROMOTIONS_ADMIN_URL', admin_url('admin.php?page=' . PROMOTIONS_PG_SLUG));
        define('EE_PROMOTIONS_ADMIN_ASSETS_PATH', EE_PROMOTIONS_ADMIN . 'assets' . DS);
        define('EE_PROMOTIONS_ADMIN_ASSETS_URL', EE_PROMOTIONS_URL . 'admin/promotions/assets/');
        define('EE_PROMOTIONS_ADMIN_TEMPLATE_PATH', EE_PROMOTIONS_ADMIN . 'templates' . DS);
        define('EE_PROMOTIONS_ADMIN_TEMPLATE_URL', EE_PROMOTIONS_ADMIN_URL . 'templates' . DS);

        parent::__construct();
        $this->_folder_path = EE_PROMOTIONS_ADMIN;
    }


    /**
     *  _set_init_properties
     *
     * @access protected
     */
    protected function _set_init_properties()
    {
        $this->label = PROMOTIONS_LABEL;
    }


    public function getMenuProperties(): array
    {
        return [
            'menu_type'    => AdminMenuItem::TYPE_MENU_SUB_ITEM,
            'menu_group'   => 'addons',
            'menu_order'   => 25,
            'show_on_menu' => AdminMenuItem::DISPLAY_BLOG_ONLY,
            'parent_slug'  => 'espresso_events',
            'menu_slug'    => PROMOTIONS_PG_SLUG,
            'menu_label'   => PROMOTIONS_LABEL,
            'capability'   => 'ee_read_promotions',
        ];
    }
}
