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
		// make sure $attributes is an array
		$attributes = array_merge(
			// defaults
			array(
				'end_date' 	=> gmdate( 'Y-m-d H:i:s', ( time() + ( apply_filters( 'FHEE__EES_Espresso_Promotions__process_shortcode__upcoming_promotions_number_of_days', 60 ) * DAY_IN_SECONDS ))),
			),
			(array)$attributes
		);
		$query_args = array(
			array(
				'PRO_start' 	=> array( '>=', $attributes['start_date'] ),
				'PRO_end' 	=> array( '<=', $attributes['end_date'] ),
				'PRO_code' 	=> isset( $attributes['codes'] ) ?  array( 'IN', $attributes['codes'] ) : ''
			)
		);
		/** @type EEM_Promotion $EEM_Promotion */
		$EEM_Promotion = EE_Registry::instance()->load_model( 'Promotion' );
		EE_Registry::instance()->load_helper( 'Template' );
//		$EEM_Promotion->show_next_x_db_queries();
		$active_promotions = $EEM_Promotion->get_upcoming_codeless_promotions( $query_args );
//		d( $active_promotions );
		$html = '<div id="ee-upcoming-promotions-dv">';
		foreach ( $active_promotions as $promotion ) {
			if ( $promotion instanceof EE_Promotion ) {
				$config = EE_Registry::instance()->CFG->addons->promotions;
				if ( ! empty( $config->banner_template ) && $config->banner_template == 'promo-banner-ribbon.template.php' && ! empty( $config->ribbon_banner_color )) {
					$promo_bg_color = EE_Registry::instance()->CFG->addons->promotions->ribbon_banner_color;
				} else {
					$promo_bg_color = '';
				}

				$html .= EEH_Template::locate_template(
					apply_filters( 'FHEE__EED_Promotions__process_shortcode__upcoming_promotions', EE_PROMOTIONS_PATH . 'templates' . DS . 'upcoming-promotions-grid.template.php' ),
					array(
						'PRO_ID' => $promotion->ID(),
						'promo_bg_color'	=> 	apply_filters( 'FHEE__EED_Promotions__process_shortcode__promo_bg_color', $promo_bg_color ),
						'promo_header' 	=> 	$promotion->name(),
						'promo_desc' 		=>	$promotion->description() != '' ? $promotion->description() . '<br />' : '',
						'promo_amount'	=>	$promotion->pretty_amount(),
						'promo_scope' 		=>	$promotion->scope_obj()->label->plural,
						'promo_dates' 		=>	$this->promotion_date_range( $promotion ),
						'promo_applies'	=>	$this->_get_objects_promo_applies_to( $promotion )
					)
				);
			}
		}
		$html .= '</div>';
		return $html;
	}



	/**
	 * _get_objects_promo_applies_to
	 * returns an array of promotion objects that the promotion applies to
	 *
	 * @param EE_Promotion $promotion
	 * @return EE_Promotion[]
	 */
	protected function _get_objects_promo_applies_to( EE_Promotion $promotion ) {
		$redeemable_scope_promos = $promotion->scope_obj()->get_redeemable_scope_promos( $promotion );
		$scope_items = array();
		foreach( $redeemable_scope_promos as $scope => $scope_object_IDs ) {
			$scope_items = $promotion->scope_obj()->get_items(
				array(
					array( $promotion->scope_obj()->model_pk_name() => array( 'IN', $scope_object_IDs ))
				)
			);
		}
		$promo_applies = array();
		foreach ( $scope_items as $scope_item ) {
			$promo_applies[] = $scope_item->name();
		}
		return $promo_applies;
	}



	/**
	 * promotion_date_range
	 * returns the first and last chronologically ordered dates for a promotion (if different)
	 *
	 * @param EE_Promotion $promotion
	 * @return string
	 */
	public function promotion_date_range( EE_Promotion $promotion ) {
		EE_Registry::instance()->load_helper( 'DTT_Helper' );
		$promo_start = EEH_DTT_Helper::process_start_date( $promotion->start() );
		$promo_end = EEH_DTT_Helper::process_end_date( $promotion->end() );
		// if the promo starts at midnight on one day, and the promo ends at midnight on the very next day...
		if ( EEH_DTT_Helper::dates_represent_one_24_hour_day( $promotion->start(), $promotion->end() )) {
			return $promo_start;
		} else {
			return $promo_start . __( ' - ', 'event_espresso' ) . $promo_end;
		}
	}


}
// End of file EES_Espresso_Promotions.shortcode.php
// Location: /wp-content/plugins/espresso-promotions/EES_Espresso_Promotions.shortcode.php