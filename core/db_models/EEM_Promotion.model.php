<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');
require_once ( EE_MODELS . 'EEM_Base.model.php' );
/**
 *
 * Promotion Model
 *
 * @package			Event Espresso
 * @subpackage		includes/models/
 * @author				Michael Nelson
 *
 */
class EEM_Promotion extends EEM_Soft_Delete_Base {

  	// private instance of the Attendee object
	protected static $_instance = NULL;



	/**
	 * @return EEM_Promotion
	 */
	protected function __construct(){

		$this->singular_item = __( 'Promotion', 'event_espresso' );
		$this->plural_item 	= __( 'Promotions', 'event_espresso' );

		$this->_tables = array(
			'Promotion' => new EE_Primary_Table( 'esp_promotion', 'PRO_ID' )
		);

		$this->_fields = array(
			'Promotion' => array(
				'PRO_ID'          		=> new EE_Primary_Key_Int_Field( 'PRO_ID', __( 'ID', 'event_espresso' ) ),
				'PRC_ID'          		=> new EE_Foreign_Key_Int_Field( 'PRC_ID', __( "Price ID", "event_espresso" ), FALSE, 0, 'Price' ),
				'PRO_scope'       		=> new EE_Plain_Text_Field( 'PRO_scope', __( "Scope", "event_espresso" ), FALSE, '' ),
				'PRO_start'       		=> new EE_Datetime_Field( 'PRO_start', __( "Start Date/Time", "event_espresso" ), TRUE, NULL ),
				'PRO_end'         		=> new EE_Datetime_Field( 'PRO_end', __( "End Date/Time", "event_espresso" ), TRUE, NULL ),
				'PRO_code'        		=> new EE_Plain_Text_Field( 'PRO_code', __( "Code", "event_espresso" ), TRUE, '' ),
				'PRO_uses'        		=> new EE_Integer_Field( 'PRO_uses', __( "Times this can be used in a given scope", "event_espresso" ), FALSE, EE_INF_IN_DB ),
				'PRO_global'      		=> new EE_Boolean_Field( 'PRO_global', __( "Applies to ALL Scope items", "event_espresso" ), FALSE, FALSE ),
				'PRO_global_uses' 	=> new EE_Integer_Field( 'PRO_global_uses', __( "Times it can be used in all scopes", "event_espresso" ), FALSE, EE_INF_IN_DB ),
				'PRO_exclusive'   	=> new EE_Boolean_Field(
					'PRO_exclusive',
					__( "Exclusive? (ie, can't be used with other promotions)", "event_espresso" ),
					false,
					apply_filters( 'FHEE__EEM_Promotion__promotions_exclusive_default', true )
				),
				'PRO_accept_msg' 	=> new EE_Simple_HTML_Field( 'PRO_accept_msg', __( "Acceptance Message", "event_espresso" ), FALSE, __( "Accepted", "event_espresso" ) ),
				'PRO_decline_msg'	=> new EE_Simple_HTML_Field( 'PRO_decline_msg', __( "Declined Message", "event_espresso" ), FALSE, __( "Declined", "event_espresso" ) ),
				'PRO_default'     		=> new EE_Boolean_Field( 'PRO_default', __( "Usable by default on all new items within promotion's scope", "event_espresso" ), FALSE, FALSE ),
				'PRO_order'       		=> new EE_Integer_Field( 'PRO_order', __( "Order", "event_espresso" ), FALSE, 0 ),
				'PRO_deleted'     	=> new EE_Trashed_Flag_Field( 'PRO_deleted', __( "Deleted", 'event_espresso' ), FALSE, FALSE ),
				'PRO_wp_user'		=> new EE_WP_User_Field( 'PRO_wp_user', __( 'Promotion Creator', 'event_espresso' ), false ),
			)
		);

		$this->_model_relations = array(
			'Price'            				=> new EE_Belongs_To_Relation(),
			'Promotion_Object' 	=> new EE_Has_Many_Relation(),
			'Line_Item' => new EE_Has_Many_Any_Relation(),
		);

		parent::__construct();
	}



	/**
	 * get_promotion_details_via_code
	 *
	 * @param string $promo_code
	 * @param array  $additional_query_params
	 * @return EE_Promotion
	 */
	public function get_promotion_details_via_code( $promo_code = '', $additional_query_params = array() ) {
		return $this->get_one(
			array_replace_recursive(
				$additional_query_params,
				array(
					array(
						'PRO_code'    => $promo_code,
						'PRO_deleted' => false,
					)
				),
				// query params for calendar controlled expiration
				$this->_get_promotion_expiration_query_params()
			)
		);
	}



	/**
	 * get_all_active_codeless_promotions
	 * retrieves all promotions that are currently active based on the current time and do
	 * NOT utilize a code
	 *
	 * Note this DOES include promotions that have no dates set.
	 *
	 * @param array  $query_params
	 * @return EE_Promotion[]
	 */
	public function get_all_active_codeless_promotions( $query_params = array() ) {

		return $this->get_all(
			array_replace_recursive(
				array(
					array(
						'PRO_code' 		=> null,
						'PRO_deleted' 	=> false,
					)
				),
				// query params for calendar controlled expiration
				$this->_get_promotion_expiration_query_params(),
				// incoming $query_params array filtered to remove null values and empty strings
				array_filter( (array) $query_params, 'EEM_Promotion::has_value' )
			)
		);
	}



	/**
	 * _get_promotion_expiration_query_params
	 * query params for calendar controlled expiration
	 *
	 * @return array
	 */
	protected function _get_promotion_expiration_query_params() {
		$promo_start_date = $this->current_time_for_query( 'PRO_start' );
		$promo_end_date = $this->current_time_for_query( 'PRO_end' );
		return array(
			array(
				'OR' => array(
					'AND'    => array(
						'PRO_start' => array( '<=', $promo_start_date ),
						'PRO_end'   => array( '>=', $promo_end_date ),
					),
					'AND*'   => array(
						'PRO_start*' => array( 'IS NULL' ),
						'PRO_end*'   => array( 'IS NULL' ),
					),
					'AND**'  => array(
						'PRO_start**' => array( '<=', $promo_start_date ),
						'PRO_end**'   => array( 'IS NULL' ),
					),
					'AND***' => array(
						'PRO_start***' => array( 'IS NULL' ),
						'PRO_end***'   => array( '>=', $promo_end_date ),
					),
				)
			)
		);
	}



	/**
	 * get_upcoming_codeless_promotions
	 * retrieves all promotions that are not active yet but are upcoming within so many days (
	 * 60 by default ) and that do not have a code.
	 *
	 * @param array  $query_params
	 * @return EE_Promotion[]
	 */
	public function get_upcoming_codeless_promotions( $query_params = array() ) {
		$PRO_end = date(
			'Y-m-d 00:00:00',
			time() + (
				apply_filters(
					'FHEE__EEM_Promotion__get_upcoming_codeless_promotions__number_of_days',
					60
				) * DAY_IN_SECONDS
			)
		);
		return $this->get_all(
			array_replace_recursive(
				array(
					array(
						'AND' => array(
							'PRO_start'  => array( '>=', $this->current_time_for_query( 'PRO_start' ) ),
							'PRO_end' => array( '<=', $this->convert_datetime_for_query( 'PRO_end', $PRO_end, 'Y-m-d H:i:s' ) ),
						),
						'PRO_code' => null,
						'PRO_deleted' 	=> false,
					)
				),
				// incoming $query_params array filtered to remove null values and empty strings
				array_filter( (array) $query_params, 'EEM_Promotion::has_value' )
			)
		);
	}





	/**
	 * Get all active and upcoming promotions that fall within the given range and that do not
	 * have a code.
	 * Default range is within 60 days from now.
	 * Note: this query does NOT return any promotions with no end date.
	 *
	 * @param array $query_params any additional query params (or you can replace the
	 *                            		      defaults as well)
	 *
	 * @return EE_Promotion[]
	 */
	public function get_active_and_upcoming_codeless_promotions_in_range( $query_params = array() ) {
		$PRO_end = date(
			'Y-m-d 00:00:00',
			time() + (
				apply_filters(
					'FHEE__EEM_Promotion__get_active_and_upcoming_codeless_promotions_in_range__number_of_days',
					60
				) * DAY_IN_SECONDS
			)
		);
		return $this->get_all(
			array_replace_recursive(
				array(
					array(
						'PRO_end'  => array(
							'BETWEEN', array(
								$this->current_time_for_query( 'PRO_end' ),
								$this->convert_datetime_for_query( 'PRO_end', $PRO_end, 'Y-m-d H:i:s' )
							)
						),
						'PRO_code' => null,
						'PRO_deleted' 	=> false,
					)
				),
				// incoming $query_params array filtered to remove null values and empty strings
				array_filter( (array) $query_params, 'EEM_Promotion::has_value' )
			)
		);
	}



	/**
	 * not_null
	 * @param $val
	 * @return bool
	 */
	public static function has_value( $val ) {
		return ! ( $val === NULL || $val === '' );
	}




}
// End of file EEM_Promotion.model.php
// Location: /includes/models/EEM_Promotion.model.php
