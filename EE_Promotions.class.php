<?php


/**
 * Class  EE_Promotions
 *
 * @package     Event Espresso
 * @subpackage  espresso-promotions
 * @author      Brent Christensen
 * @version     1.0.0
 */
class EE_Promotions extends EE_Addon
{
    /**
     * register_addon
     *
     * @throws EE_Error
     */
    public static function register_addon()
    {
        if (! defined('EE_PROMOTIONS_BASENAME')) {
            // define the plugin directory path and URL
            define('EE_PROMOTIONS_BASENAME', plugin_basename(EE_PROMOTIONS_PLUGIN_FILE));
            define('EE_PROMOTIONS_PATH', plugin_dir_path(__FILE__));
            define('EE_PROMOTIONS_URL', plugin_dir_url(__FILE__));
            define('EE_PROMOTIONS_ADMIN', EE_PROMOTIONS_PATH . 'admin' . DS . 'promotions' . DS);
            define('EE_PROMOTIONS_CORE', EE_PROMOTIONS_PATH . 'core' . DS);
        }
        // register addon via Plugin API
        EE_Register_Addon::register(
            'Promotions',
            [
                'version'               => EE_PROMOTIONS_VERSION,
                'min_core_version'      => EE_PROMOTIONS_CORE_VERSION_REQUIRED,
                'main_file_path'        => EE_PROMOTIONS_PLUGIN_FILE,
                'admin_path'            => EE_PROMOTIONS_ADMIN,
                'plugin_slug'           => 'espresso_promotions',
                'config_class'          => 'EE_Promotions_Config',
                'config_name'           => 'promotions',
                'namespace'        => [
                    'FQNS' => 'EventEspresso\Promotions',
                    'DIR'  => __DIR__,
                ],
                // 'plugins_page_row'    => EE_Promotions::plugins_page_row(),
                'dms_paths'             => [EE_PROMOTIONS_CORE . 'data_migration_scripts' . DS],
                'module_paths'          => [EE_PROMOTIONS_PATH . 'EED_Promotions.module.php'],
                'shortcode_paths'       => [EE_PROMOTIONS_PATH . 'EES_Espresso_Promotions.shortcode.php'],
                'widget_paths'          => [EE_PROMOTIONS_PATH . 'EEW_Promotions.widget.php'],
                // register autoloaders
                'autoloader_paths'      => [
                    'EE_Promotions_Config'        => EE_PROMOTIONS_PATH . 'EE_Promotions_Config.php',
                    'Promotions_Admin_Page_Init'  => EE_PROMOTIONS_ADMIN . 'Promotions_Admin_Page_Init.core.php',
                    'Promotions_Admin_Page'       => EE_PROMOTIONS_ADMIN . 'Promotions_Admin_Page.core.php',
                    'Promotions_Admin_List_Table' => EE_PROMOTIONS_ADMIN . 'Promotions_Admin_List_Table.class.php',
                    'EE_Promotion_Scope'
                                                  => EE_PROMOTIONS_PATH . 'lib' . DS . 'scopes' . DS . 'EE_Promotion_Scope.lib.php',
                ],
                'autoloader_folders'    => [
                    'Promotions_Plugin_API' => EE_PROMOTIONS_PATH . 'lib' . DS . 'plugin_api',
                ],
                'pue_options'           => [
                    'pue_plugin_slug' => 'eea-promotions',
                    'checkPeriod'     => '24',
                    'use_wp_update'   => false,
                ],
                // EE_Register_Model
                'model_paths'           => [EE_PROMOTIONS_CORE . 'db_models'],
                'class_paths'           => [EE_PROMOTIONS_CORE . 'db_classes'],
                // EE_Register_Model_Extensions
                'model_extension_paths' => [EE_PROMOTIONS_CORE . 'db_model_extensions' . DS],
                'class_extension_paths' => [EE_PROMOTIONS_CORE . 'db_class_extensions' . DS],
                'capabilities'          => [
                    'administrator' => [
                        'ee_read_promotion',
                        'ee_read_promotions',
                        'ee_read_others_promotions',
                        'ee_edit_promotion',
                        'ee_edit_promotions',
                        'ee_edit_others_promotions',
                        'ee_delete_promotion',
                        'ee_delete_promotions',
                        'ee_delete_others_promotions',
                    ],
                ],
                'capability_maps'       => [
                    0 => [
                        'EE_Meta_Capability_Map_Read' => [
                            'ee_read_promotion',
                            ['Promotion', '', 'ee_read_others_promotions', ''],
                        ],
                    ],
                    1 => [
                        'EE_Meta_Capability_Map_Edit' => [
                            'ee_edit_promotion',
                            ['Promotion', '', 'ee_edit_others_promotions', ''],
                        ],
                    ],
                    2 => [
                        'EE_Meta_Capability_Map_Delete' => [
                            'ee_delete_promotion',
                            ['Promotion', '', 'ee_delete_others_promotions', ''],
                        ],
                    ],
                ],
            ]
        );
    }


    /**
     * a safe space for addons to add additional logic like setting hooks
     * that will run immediately after addon registration
     * making this a great place for code that needs to be "omnipresent"
     */
    public function after_registration()
    {
        // register promotion specific statuses
        add_filter(
            'FHEE__EEM_Status__localized_status__translation_array',
            ['EE_Promotions', 'promotion_stati']
        );

        // add promotion codes shortcode to messages
        add_filter('FHEE__EE_Shortcodes__shortcodes', ['EE_Promotions', 'register_new_shortcodes'], 10, 2);
        add_filter('FHEE__EE_Shortcodes__parser_after', ['EE_Promotions', 'register_new_shortcode_parser'], 10, 5);
    }


    /**
     * plugins_page_row
     * HTML to appear within a new table row on the WP Plugins page, below the promotions plugin row
     *
     * @return array
     */
    public static function plugins_page_row()
    {
        return [
            'link_text'   => 'Promotions Addon Upsell Info',
            'link_url'    => '#',
            'description' => 'To edit me, open up ' . __FILE__ . ' and find the ' . __METHOD__ . '() method',
        ];
    }


    /**
     * This registers the localization for the promotion statuses with the EEM_Status
     * translation array
     *
     * @param array $stati_translation Current localized stati
     * @return array  Current stati with promotion stati appended.
     */
    public static function promotion_stati(array $stati_translation): array
    {
        $promotion_stati = [
            EE_Promotion::upcoming    => [
                esc_html__('upcoming', 'event_espresso'),
                esc_html__('upcoming', 'event_espresso'),
            ],
            EE_Promotion::active      => [
                esc_html__('active', 'event_espresso'),
                esc_html__('active', 'event_espresso'),
            ],
            EE_Promotion::expired     => [
                esc_html__('expired', 'event_espresso'),
                esc_html__('expired', 'event_espresso'),
            ],
            EE_Promotion::unavailable => [
                esc_html__('unavailable', 'event_espresso'),
                esc_html__('unavailable', 'event_espresso'),
            ],
        ];
        return array_merge($stati_translation, $promotion_stati);
    }


    /**
     * Callback for FHEE__EE_Shortcodes__shortcodes
     *
     * @param array         $shortcodes The existing shortcodes in this library
     * @param EE_Shortcodes $lib
     * @return array          new array of shortcodes
     * @since 1.0.0
     */
    public static function register_new_shortcodes(array $shortcodes, EE_Shortcodes $lib): array
    {
        // Check we have the EE_Transaction_Shortcodes library
        if ($lib instanceof EE_Transaction_Shortcodes) {
            // Add shortcode to the shortcodes array.
            $shortcodes['[PROMOTIONS_USED]'] = esc_html__(
                'This shortcode outputs all promotions used on the registration.',
                'event_espresso'
            );
        }
        // Return the shortcodes.
        return $shortcodes;
    }


    /**
     * Call back for the FHEE__EE_Shortcodes__parser_after filter.
     * This contains the logic for parsing the new shortcodes introduced by this addon.
     *
     * @param string        $parsed     The current parsed template string.
     * @param string        $shortcode  The incoming shortcode being setup for parsing.
     * @param array|object  $data       Depending on the shortcode parser the filter is called in, this will represent
     *                                  either an array of data objects or a specific data object.
     * @param array|object  $extra_data Depending on the shortcode parser the filter is called in, this will either
     *                                  represent an array with an array of templates being parsed, and a
     *                                  EE_Addressee_Data object OR just an EE_Addresee_Data object.
     * @param EE_Shortcodes $lib
     * @return string        The parsed string
     * @throws EE_Error
     * @throws ReflectionException
     * @since 1.0.0
     */
    public static function register_new_shortcode_parser(
        string $parsed,
        string $shortcode,
        $data,
        $extra_data,
        EE_Shortcodes $lib
    ): string {
        // Check we have the EE_Transaction_Shortcodes and our the shortcode matches
        if ($lib instanceof EE_Transaction_Shortcodes && $shortcode == '[PROMOTIONS_USED]') {
            // Pull the transaction from the EE_Messages_Addressee object passed to parser.
            $transaction = $data instanceof EE_Messages_Addressee ? $data->txn : null;
            // Check we have an EE_Transaction object
            if ($transaction instanceof EE_Transaction) {
                // Pull in the promotion line items for this transaction
                $promo_rows = EEM_Price::instance()->get_all_wpdb_results(
                    [
                        [
                            'Promotion.Line_Item.TXN_ID' => $transaction->ID(),
                        ],
                    ]
                );
                // Setup an arrey to store all promo codes used on the transaction
                $promo_codes = [];
                // Loop through promo line items and build the promo_codes array using the Promocode name and code
                // (if available)
                foreach ($promo_rows as $promo_row) {
                    if ($promo_row['Promotion.PRO_code']) {
                        $promo_codes[] = sprintf(
                            '%1$s [%2$s]',
                            $promo_row['Price.PRC_name'],
                            $promo_row['Promotion.PRO_code']
                        );
                    } else {
                        $promo_codes[] = $promo_row['Price.PRC_name'];
                    }
                }
                // Implode the promo_codes array into a comma-delimited string.
                // Return a single string or promo codes used.
                return implode(', ', $promo_codes);
            }
        }
        // If not within the correct section, or parsing the correct shortcode,
        // return the currently parsed content.
        return $parsed;
    }
}
