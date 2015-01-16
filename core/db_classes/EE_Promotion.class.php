<?php
/**
 * This file contains the EE_Base_Class implementation for EE_Promotion model object
 *
 * @since 1.0.0
 * @package EE Promotions
 * @subpackage models
 */
if ( ! defined( 'EVENT_ESPRESSO_VERSION' )) { exit('NO direct script access allowed'); }

/**
 * EE_Base_Class for EE_Promotions
 *
 * @since 1.0.0
 *
 * @package EE4 Promotions
 * @subpackage models
 * @author Mike Nelson
 */
class EE_Promotion extends EE_Soft_Delete_Base_Class{

	//constants

	/**
	 * Promotion hasn't started yet.
	 *
	 * @since 1.0.0
	 * @type string
	 */
	const upcoming = 'PRU';


	/**
	 * Promotion is currently active.
	 *
	 * @since 1.0.0
	 * @type string
	 */
	const active = 'PRA';


	/**
	 * Promotion is expired.
	 *
	 * @since 1.0.0
	 * @type string
	 */
	const expired = 'PRX';


	/**
	 * Promotion is unavailable.
	 *
	 * @since 1.0.0
	 * @type string
	 */
	const unavailable = 'PRN';



	/**
	 * method for instantiating the object from client code.
	 *
	 * @since 1.0.0
	 *
	 * @param array $props_n_values array of fields and values to set on the object
	 * @return EE_Promotion
	 */
	public static function new_instance( $props_n_values = array() ) {
		$has_object = parent::_check_for_object( $props_n_values, __CLASS__ );
		return $has_object ? $has_object : new self( $props_n_values );
	}

	/**
	 * method for instantiating the object using values from the db.
	 *
	 * @since 1.0.0
	 *
	 * @param array $props_n_values
	 * @return EE_Promotion
	 */
	public static function new_instance_from_db ( $props_n_values = array() ) {
		return new self( $props_n_values, TRUE );
	}



	/**
	 * get EE_Price ID.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	public function price_ID(){
		return $this->get('PRC_ID');
	}



	/**
	 * get EE_Price name.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function name() {
		$price = $this->price();
		return $this->price() instanceof EE_Price ? $price->name() : '';
	}



	/**
	 * get EE_Price amount.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	public function amount() {
		$price = $this->price();
		if ( $price instanceof EE_Price ) {
			return $price->type_obj()->is_discount() ? $price->amount() * -1 : $price->amount();
		}
		return 0;
	}




	/**
	 * get EE_Price pretty amount.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function pretty_amount() {
		$price = $this->price();
		return $price instanceof EE_Price ? $price->pretty_price() : 0;
	}



	/**
	 * given a passed float value, this will calculate and return the total promotion discount for that value
	 *
	 * @since 1.0.0
	 *
	 * @param float $total_discount_is_applied_to
	 * @return string
	 */
	public function calculated_amount_on_value( $total_discount_is_applied_to = 0.00 ) {
		if ( $this->is_percent() ) {
			return floatval( $this->amount() / 100 * $total_discount_is_applied_to );
		} else {
			return $this->amount();
		}
	}




	/**
	 * whether promo discount is percentage or dollar based
	 * defaults to TRUE
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function is_percent() {
		$price = $this->price();
		return $price instanceof EE_Price ? $price->is_percent() : TRUE;
	}



	/**
	 * get EE_Price description.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function description() {
		$price = $this->price();
		return $price instanceof EE_Price ? $price->desc() : '';
	}




	/**
	 * get EE_Price_Type ID.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	public function price_type_id() {
		$price = EEM_Price::instance()->get_one_by_ID( $this->price_ID() );
		return $price instanceof EE_Price ? $price->type() : 0;
	}



	/**
	 * get EE_promotion accept message text.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function accept_message(){
		return $this->get('PRO_accept_msg');
	}




	/**
	 * get EE_Promotion code.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function code(){
		return $this->get('PRO_code');
	}




	/**
	 * get EE_Promotion decline message text.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function decline_message(){
		return $this->get('PRO_decline_msg');
	}




	/**
	 * returns whether or not this promotion should be added by default to all items in its scope.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	public function is_default(){
		return $this->get('PRO_default');
	}



	/**
	 * Gets the date this promotion is no longer valid.
	 *
	 * @since 1.0.0
	 *
	 * @param string $date_format
	 * @param string $time_format
	 * @return string
	 */
	public function end( $date_format='', $time_format='' ){
		//if PRO_end is null then we return an empty string.  It is entirely possible for promotions to have NO dates
		$pro_end = $this->get_raw('PRO_end');
		return empty( $pro_end ) ? '' : $this->_get_datetime('PRO_end',$date_format,$time_format);
	}





	/**
	 * If this returns true, this promotion cannot be combined with other promotions. If false, it
	 * cannot be.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	public function is_exclusive(){
		return $this->get('PRO_exclusive');
	}




	/**
	 * Return whether or not this promotion can be used globally or not.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	public function is_global(){
		return $this->get('PRO_global');
	}





	/**
	 * The number of times this promotion has been used globally.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	public function global_uses(){
		return $this->get('PRO_global_uses');
	}





	/**
	 * The remaining number of times this promotion can be used globally.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	public function global_uses_left(){
		return $this->global_uses() === EE_INF_IN_DB ? INF : $this->global_uses() - $this->redeemed();
	}





	/**
	 * the order in which this promotion should be applied.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	public function order(){
		return $this->get('PRO_order');
	}




	/**
	 * The model this promotion should be applied to. Eg, Registration, Transaction, etc.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function scope(){
		return $this->get('PRO_scope');
	}



	/**
	 * Get the scope object for the scope on this promotion.
	 *
	 * @since 1.0.0
	 *
	 * @return EE_Promotion_Scope
	 */
	public function scope_obj() {
		$scope = $this->scope();
		$scope = empty( $scope ) ? 'Event' : $scope;
		return EE_Registry::instance()->CFG->addons->promotions->scopes[$scope];
	}



	/**
	 * This simply returns the name for the applied to item attached to this scope.  The related
	 * scope object determines how this is displayed (i.e. if this is for multiple items then scope
	 * object will just return the plural label for this scope item.  If there is only ONE item then it
	 * will return the specific name of that one item).
	 *
	 * @since 1.0.0
	 *
	 * @param bool | string $link   If false then just return the related name.  If 'front' then
	 * return the name wrapped in a hyperlink to the frontend details for the item.  If 'admin'
	 * then return the name wrapped in a hyperlink to the admin details for the item.
	 * @return string
	 */
	public function applied_to_name( $link = FALSE ) {
		$pro_objects = $this->promotion_objects();
		$obj = 0;
		$obj_ids = array();
		if ( !empty( $pro_objects ) ) {
			$obj = count( $pro_objects ) > 1 ? $pro_objects : array_shift($pro_objects);
		}

		$applied_obj = ! empty( $obj ) && $obj instanceof EE_Promotion_Object ? $obj->get_first_related( $this->scope() ) : $obj;

		if ( is_array( $applied_obj )  ) {
			foreach( $pro_objects as $pro_object ) {
				$obj_ids[] = $pro_object->OBJ_ID();
			}
			$applied_obj = $obj_ids;
		}

		return $this->scope_obj()->name( $applied_obj, $link, $this->ID() );
	}






	/**
	 * This returns how many times this promotion has been redeemed (via promotion object table)
	 *
	 * @since  1.0.0
	 *
	 * @param int 	$objID	 If a specific object ID is included then we only return the count
	 * for that specific object ID.  Otherwise we sum all the values for the matching PRO_ID in
	 * the Promotion Objects table.
	 * @return int
	 */
	public function redeemed($objID = 0) {
		$query_params[0] = array( 'PRO_ID' => $this->ID() );
		if ( !empty( $objID ) )
			$query_params[0]['OBJ_ID'] = $objID;
		return EEM_Promotion_Object::instance()->sum( $query_params, 'POB_used' );
	}



	/**
	 * Returns the date/time this promotion becomes available.
	 *
	 * @since 1.0.0
	 *
	 * @param string $date_format
	 * @param string $time_format
	 * @return string
	 */
	public function start( $date_format='', $time_format='' ){
		//if pro_start is null then we return an empty string.  It is entirely possible for promotions to have NO dates
		$pro_start = $this->get_raw('PRO_start');
		return empty( $pro_start ) ? '' : $this->get_datetime('PRO_start', $date_format, $time_format);
	}



	/**
	 * promotion_date_range
	 * returns the first and last chronologically ordered dates for a promotion (if different)
	 *
	 * @return string
	 */
	public function promotion_date_range() {
		EE_Registry::instance()->load_helper( 'DTT_Helper' );
		$promo_start = EEH_DTT_Helper::process_start_date( $this->start() );
		$promo_end = EEH_DTT_Helper::process_end_date( $this->end() );
		// if the promo starts at midnight on one day, and the promo ends at midnight on the very next day...
		if ( EEH_DTT_Helper::dates_represent_one_24_hour_day( $this->start(), $this->end() )) {
			return $promo_start;
		} else {
			return $promo_start . __( ' - ', 'event_espresso' ) . $promo_end;
		}
	}



	/**
	 * Gets the number of times this promotion has been used in its particular scope.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	public function uses(){
		return $this->get('PRO_uses');
	}



	/**
	 * Gets the number of times this promotion has been used in its particular scope.
	 *
	 * @since 1.0.0
	 *
	 * @param int $OBJ_ID
	 * @return int
	 */
	public function uses_left( $OBJ_ID = 0 ){
		return $this->uses() === EE_INF_IN_DB ? INF : $this->uses() - $this->redeemed( $OBJ_ID );
	}



	/**
	 * Gets the number of times this promotion has been used in its particular scope.
	 *
	 * @since 1.0.0
	 *
	 * @param \EE_Promotion_Object $promotion_object
	 * @return int
	 */
	public function uses_left_for_scope_object( EE_Promotion_Object $promotion_object ){
		return $this->uses() === EE_INF_IN_DB ? INF : $this->uses() - $promotion_object->used();
	}



	/**
	 * This returns the status for the promotion (which is a calculation based on the date strings)
	 * Note that its possible promotion dates are null which DOES affect the calculation accordingly.
	 *
	 * @since 1.0.0
	 *
	 * @return string  One of the EE_Promotion constant values.
	 */
	public function status() {
		//check uses first... if uses has none left then expired.
		$uses = $this->uses();
		if ( $uses !== EE_INF_IN_DB && $uses <= $this->redeemed() ) {
			return self::unavailable;
		}

		$start = $this->get_raw('PRO_start');
		$end = $this->get_raw('PRO_end');
		$now = time();
//		echo '<h5 style="color:#2EA2CC;">$start : <span style="color:#E76700">' . $start . '</span><br/><span style="font-size:9px;font-weight:normal;color:#666">' . __FILE__ . '</span>    <b style="font-size:10px;color:#333">  ' . __LINE__ . ' </b></h5>';
//		echo '<h5 style="color:#2EA2CC;">$end : <span style="color:#E76700">' . $end . '</span><br/><span style="font-size:9px;font-weight:normal;color:#666">' . __FILE__ . '</span>    <b style="font-size:10px;color:#333">  ' . __LINE__ . ' </b></h5>';
//		echo '<h5 style="color:#2EA2CC;">$now : <span style="color:#E76700">' . $now . '</span><br/><span style="font-size:9px;font-weight:normal;color:#666">' . __FILE__ . '</span>    <b style="font-size:10px;color:#333">  ' . __LINE__ . ' </b></h5>';
//		echo '<h5 style="color:#2EA2CC;">$start < $now : <span style="color:#E76700">' . ( $start < $now ) . '</span><br/><span style="font-size:9px;font-weight:normal;color:#666">' . __FILE__ . '</span>    <b style="font-size:10px;color:#333">  ' . __LINE__ . ' </b></h5>';
//		echo '<h5 style="color:#2EA2CC;">empty( $start ) && empty( $end ) : <span style="color:#E76700">' . ( empty( $start ) && empty( $end ) ) . '</span><br/><span style="font-size:9px;font-weight:normal;color:#666">' . __FILE__ . '</span>    <b style="font-size:10px;color:#333">  ' . __LINE__ . ' </b></h5>';
//		echo '<h5 style="color:#2EA2CC;">empty( $start ) && $end > $now : <span style="color:#E76700">' . ( empty( $start ) && $end > $now ) . '</span><br/><span style="font-size:9px;font-weight:normal;color:#666">' . __FILE__ . '</span>    <b style="font-size:10px;color:#333">  ' . __LINE__ . ' </b></h5>';
//		echo '<h5 style="color:#2EA2CC;">empty( $end ) && $start < $now : <span style="color:#E76700">' . ( empty( $end ) && $start < $now ) . '</span><br/><span style="font-size:9px;font-weight:normal;color:#666">' . __FILE__ . '</span>    <b style="font-size:10px;color:#333">  ' . __LINE__ . ' </b></h5>';
		//active (which means that the promotion is currently able to be used)
		if ( ( $start < $now ) || ( empty( $start ) && empty( $end ) ) || ( empty( $start ) && $end > $now ) || ( empty( $end ) && $start < $now ) ) {
			return self::active;
		//upcoming
		} else if ( $start > $now  ) {
			return self::upcoming;
		//k must be expired
		} else {
			return self::expired;
		}
	}




	/**
	 * set the EE_Price ID.
	 *
	 * @since 1.0.0
	 *
	 * @param int $price_id
	 */
	public function set_price_ID($price_id){
		$this->set('PRC_ID',$price_id);
	}



	/**
	 * set the scope.
	 *
	 * @since 1.0.0
	 * @todo  would likely be good to do some validation here to make sure the given scope matches a registered scope.
	 *
	 * @param string $scope
	 */
	public function set_scope($scope){
		$this->set('PRO_scope',$scope);
	}





	/**
	 * Set the start time for the promotion.
	 *
	 * @since 1.0.0
	 *
	 * @param string $start
	 */
	public function set_start($start) {
		$this->set('PRO_start', $start);
	}




	/**
	 * Set the end time for the promotion.
	 *
	 * @since 1.0.0
	 *
	 * @param string $end
	 */
	public function set_end($end) {
		$this->set('PRO_end', $end);
	}





	/**
	 * Set the promotion code.
	 *
	 * @since 1.0.0
	 *
	 * @param string $code
	 */
	public function set_code($code) {
		$this->set('PRO_code', $code);
	}





	/**
	 * Sets how many times this promotion has been used in the given scope.
	 *
	 * @since 1.0.0
	 *
	 * @param int $uses
	 */
	public function set_uses($uses) {
		$this->set('PRO_uses', $uses);
	}





	/**
	 * Sets whether or not this promotion is global.
	 *
	 * @since 1.0.0
	 * @param boolean $global
	 */
	public function set_global($global) {
		$this->set('PRO_global', $global);
	}



	/**
	 * Sets the number of times this promotion hsa been used globally.
	 *
	 * @since 1.0.0
	 *
	 * @param string $global_uses
	 */
	public function set_global_uses($global_uses) {
		$this->set('PRO_global_uses', $global_uses);
	}





	/**
	 * Sets whether or not this promotion is exclusive (ie, cant be combined with others).
	 *
	 * @since 1.0.0
	 *
	 * @param boolean $exclusive
	 */
	public function set_exclusive($exclusive) {
		$this->set('PRO_exclusive', $exclusive);
	}





	/**
	 * sets the acceptance message.
	 *
	 * @since 1.0.0
	 *
	 * @param string $accept_msg
	 */
	public function set_accept_msg($accept_msg) {
		$this->set('PRO_accept_msg', $accept_msg);
	}





	/**
	 * sets the declined message.
	 *
	 * @since 1.0.0
	 *
	 * @param string $decline_msg
	 */
	public function set_decline_msg($decline_msg) {
		$this->set('PRO_decline_msg', $decline_msg);
	}





	/**
	 * Sets whether or not this promotion should be usable by DEFAULT on all new items in its scope.
	 *
	 * @since 1.0.0
	 * @param boolean $default
	 */
	public function set_default($default) {
		$this->set('PRO_default', $default);
	}





	/**
	 * sets the order of application on this promotion.
	 *
	 * @since 1.0.0
	 *
	 * @param int $order
	 */
	public function set_order($order) {
		$this->set('PRO_order', $order);
	}



	/**
	 * Return the related Price object for this promotion.
	 *
	 * @since 1.0.0
	 * @return EE_Price
	 */
	public function price() {
		return $this->get_first_related('Price');
	}



	/**
	 * Return the related promotion objects for this promotion.
	 *
	 * @since 1.0.0
	 *
	 * @param array $query_args filter what objects get
	 *                          	          returned by this.
	 * @return EE_Promotion_Object[]
	 */
	public function promotion_objects( $query_args = array() ) {
		return $this->get_many_related( 'Promotion_Object', $query_args );
	}



	/**
	 * get_objects_promo_applies_to
	 * returns an array of promotion objects that the promotion applies to
	 *
	 * @return array
	 */
	public function get_objects_promo_applies_to() {
		$scope_objects = array();
		if ( $this->scope_obj() instanceof EE_Promotion_Scope ) {
			$redeemable_scope_promos = $this->scope_obj()->get_redeemable_scope_promos( $this );
			foreach( $redeemable_scope_promos as $scope => $scope_object_IDs ) {
				$new_scope_objects[ $scope ] = $this->scope_obj()->get_items(
					array( array( $this->scope_obj()->model_pk_name() => array( 'IN', $scope_object_IDs )))
				);
				if ( reset( $new_scope_objects[ $scope ] ) instanceof EE_Base_Class ) {
					$scope_objects = array_merge( $scope_objects, $new_scope_objects );
				}
			}
		}
		return $scope_objects;
	}



	/**
	 * get_promo_applies_to_link_array
	 * given an array of promotion objects that the promotion applies to
	 * will return an array of linked item names indexed by scope then ID
	 *
	 * @param array $scope_objects
	 * @return array
	 */
	public function get_promo_applies_to_link_array( $scope_objects = array() ) {
		$promo_applies = array();
		foreach ( $scope_objects as $scope =>$objects ) {
			foreach ( $objects as $object ) {
				if ( $object instanceof EE_CPT_Base ) {
					$promo_applies[ $scope ][ $object->ID() ] = '<a href="' . $object->get_permalink() . '" title="' . $object->name() . '">' . $object->name() . '</a>' ;
				} else if ( $object instanceof EE_Base_Class ) {
					$promo_applies[ $scope ][ $object->ID() ] = $object->name();
				}
			}
		}
		return $promo_applies;
	}


}
