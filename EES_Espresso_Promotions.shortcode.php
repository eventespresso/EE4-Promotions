<?php if ( ! defined( 'EVENT_ESPRESSO_VERSION' )) { exit(); }
/**
 * Class EES_Espresso_Promotions
 *
 * Description
 *
 * @package 			Event Espresso
 * @subpackage 	espresso-promotions
 * @author 				Brent Christensen
 * @since 					1.0.0
 *
 */
class EES_Espresso_Promotions  extends EES_Shortcode {



	/**
	 * 	set_hooks - for hooking into EE Core, modules, etc
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public static function set_hooks() {
	}



	/**
	 * 	set_hooks_admin - for hooking into EE Admin Core, modules, etc
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public static function set_hooks_admin() {
	}



	/**
	 * 	set_definitions
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public static function set_definitions() {
	}



	/**
	 * 	run - initial shortcode module setup called during "wp_loaded" hook
	 * 	this method is primarily used for loading resources that will be required by the shortcode when it is actually processed
	 *
	 *  @access 	public
	 *  @param 	 WP $WP
	 *  @return 	void
	 */
	public function run( WP $WP ) {
		EED_Promotions::$shortcode_active = TRUE;
		EED_Promotions::enqueue_scripts();
	}



	/**
	 *    process_shortcode
	 *
	 *    [ESPRESSO_PROMOTIONS]
	 *
	 * @access 	public
	 * @param 	array $attributes
	 * @return 	string
	 */
	public function process_shortcode( $attributes = array() ) {
		/** @type EEM_Promotion $EEM_Promotion */
		$EEM_Promotion = EE_Registry::instance()->load_model( 'Promotion' );
		EE_Registry::instance()->load_helper( 'Template' );
		$active_promotions = $EEM_Promotion->get_all_active_codeless_promotions();
		$html = '<div id="ee-upcoming-promotions-dv">';
		foreach ( $active_promotions as $promotion ) {
			if ( $promotion instanceof EE_Promotion ) {
				$config = EED_Promotions::instance()->set_config();
				if ( ! empty( $config->banner_template ) && $config->banner_template == 'promo-banner-ribbon.template.php' && ! empty( $config->ribbon_banner_color )) {
					$promo_bg_color = $config->ribbon_banner_color;
				} else {
					$promo_bg_color = '';
				}
				$scope_objects = $promotion->get_objects_promo_applies_to();
				$html .= EEH_Template::locate_template(
					apply_filters( 'FHEE__EED_Promotions__process_shortcode__upcoming_promotions', EE_PROMOTIONS_PATH . 'templates' . DS . 'upcoming-promotions-grid.template.php' ),
					array(
						'PRO_ID' 			=> $promotion->ID(),
						'promo_bg_color' 	=> apply_filters( 'FHEE__EED_Promotions__process_shortcode__promo_bg_color', $promo_bg_color ),
						'promo_header' 		=> $promotion->name(),
						'promo_desc' 		=> $promotion->description() != '' ? $promotion->description() . '<br />' : '',
						'promo_amount'		=> $promotion->pretty_amount(),
						'promo_dates' 		=> $promotion->promotion_date_range(),
						'promo_scopes'		=> $promotion->get_promo_applies_to_link_array( $scope_objects )
					)
				);
			}
		}
		$html .= '</div>';
		return $html;
	}

}
// End of file EES_Espresso_Promotions.shortcode.php
// Location: /wp-content/plugins/espresso-promotions/EES_Espresso_Promotions.shortcode.php
