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
	private static $_instance = NULL;

	/**
	 *		This function is a singleton method used to instantiate the EEM_Attendee object
	 *
	 *		@access public
	 *		@return EEM_Promotion
	 */
	public static function instance(){

		// check if instance of EEM_Promotion already exists
		if ( ! self::$_instance instanceof EEM_Promotion ) {
			// instantiate Espresso_model
			self::$_instance = new self();
		}
		return self::$_instance;
	}



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
				'PRO_uses'        		=> new EE_Integer_Field( 'PRO_uses', __( "Times this can be used in a given scope", "event_espresso" ), FALSE, 1 ),
				'PRO_global'      		=> new EE_Boolean_Field( 'PRO_global', __( "Usable Globally?", "event_espresso" ), FALSE, FALSE ),
				'PRO_global_uses' 	=> new EE_Integer_Field( 'PRO_global_uses', __( "Times it can be used in all scopes", "event_espresso" ), FALSE, EE_INF_IN_DB ),
				'PRO_exclusive'   	=> new EE_Boolean_Field( 'PRO_exclusive', __( "Exclusive? (ie, can't be used with other promotions)", "event_espresso" ), FALSE, FALSE ),
				'PRO_accept_msg' 	=> new EE_Simple_HTML_Field( 'PRO_accept_msg', __( "Acceptance Message", "event_espresso" ), FALSE, __( "Accepted", "event_espresso" ) ),
				'PRO_decline_msg'	=> new EE_Simple_HTML_Field( 'PRO_decline_msg', __( "Declined Message", "event_espresso" ), FALSE, __( "Declined", "event_espresso" ) ),
				'PRO_default'     		=> new EE_Boolean_Field( 'PRO_default', __( "Usable by default on all new items within promotion's scope", "event_espresso" ), FALSE, FALSE ),
				'PRO_order'       		=> new EE_Integer_Field( 'PRO_order', __( "Order", "event_espresso" ), FALSE, 0 ),
				'PRO_deleted'     	=> new EE_Trashed_Flag_Field( 'PRO_deleted', __( "Deleted", 'event_espresso' ), FALSE, FALSE ),
			)
		);

		$this->_model_relations = array(
			'Price'            				=> new EE_Belongs_To_Relation(),
			'Rule'             				=> new EE_HABTM_Relation( 'Promotion_Rule' ),
			'Promotion_Rule'   	=> new EE_Has_Many_Relation(),
			'Promotion_Object' 	=> new EE_Has_Many_Relation()
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
						'PRO_code' 		=> $promo_code,
						'PRO_deleted' 	=> 0
					 )
				)
			)
		);
	}



	/**
	 * get_all_active_codeless_promotions
	 * retrieves all promotions that are currently active based on the current time and do NOT utilize a code
	 *
	 * @param array  $query_params
	 * @return EE_Promotion
	 */
	public function get_all_active_codeless_promotions( $query_params = array() ) {
		return $this->get_all(
			array_replace_recursive(
				array(
					array(
						'PRO_start' => array( '<', current_time( 'mysql' )),
						'PRO_end' => array( '>', current_time( 'mysql' )),
						'PRO_code' => NULL,
						'PRO_deleted' 	=> 0
					)
				),
				$query_params
			)
		);
	}



	/**
	 * get_all_active_codeless_promotions
	 * retrieves all promotions that are currently active based on the current time and do NOT utilize a code
	 *
	 * @param array  $additional_query_params
	 * @return EE_Promotion
	 */
	public function get_upcoming_codeless_promotions( $additional_query_params = array() ) {
		$query_args = array_replace_recursive(
			array(
				array(
					'PRO_start' 			=> array( '>=', gmdate( 'Y-m-d 00:00:00', current_time( 'timestamp' ))),
					'PRO_end' 			=> array( '<=', gmdate( 'Y-m-d H:i:s', ( time() + ( 30 * DAY_IN_SECONDS )))),
					'PRO_code' 		=> NULL,
					'PRO_deleted' 	=> 0
				)
			),
			$additional_query_params
		);
		return $this->get_all( $query_args );
	}




}
// End of file EEM_Promotion.model.php
// Location: /includes/models/EEM_Promotion.model.php