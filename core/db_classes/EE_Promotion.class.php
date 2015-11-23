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
class EE_Promotion extends EE_Soft_Delete_Base_Class implements
	EEI_Line_Item_Object,
	EEI_Admin_Links,
	EEI_Has_Icon,
	EEI_Has_Code {

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
	 * @param string $timezone  	incoming timezone
	 * 													if not set the timezone set for the website will be used.
	 * @param array $date_formats  	incoming date_formats in an array where the first value is the
	 * 														date_format and the second value is the time format
	 * @return EE_Promotion
	 */
	public static function new_instance( $props_n_values = array(), $timezone = null, $date_formats = array() ) {
		$has_object = parent::_check_for_object( $props_n_values, __CLASS__ );
		return $has_object ? $has_object : new self( $props_n_values, false, $timezone, $date_formats );
	}

	/**
	 * method for instantiating the object using values from the db.
	 *
	 * @since 1.0.0
	 *
	 * @param array $props_n_values
	 * @param string $timezone 	incoming timezone
	 *													if not set the timezone set for the website will be used.
	 * @return EE_Promotion
	 */
	public static function new_instance_from_db ( $props_n_values = array(), $timezone = null ) {
		return new self( $props_n_values, true, $timezone );
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
		return $this->global_uses() === EE_INF_IN_DB ? EE_INF : $this->global_uses() - $this->redeemed();
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
	 * @return \EE_Promotion_Scope
	 * @throws \EE_Error
	 */
	public function scope_obj() {
		$scope = $this->scope();
		$scope = empty( $scope ) ? 'Event' : $scope;
		if ( ! isset( $this->_config()->scopes[ $scope ] ) ) {
			$this->_config()->init();
		}
		$scope_obj = isset( $this->_config()->scopes[$scope] ) ? $this->_config()->scopes[ $scope ] : null;
		if ( ! $scope_obj instanceof EE_Promotion_Scope ) {
			throw new EE_Error( sprintf( __( 'The EE_Promotion_%1$s_Scope class was not found.', 'event_espresso' ), $scope ));
		}
		return $scope_obj;
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
		// global promotions apply to ALL scope items, so just return a link if this is the admin
		if ( $this->is_global() && $link === 'admin' ) {
			$scope_obj = $this->scope_obj();
			return sprintf(
				_x( '%1$sView all %2$s%3$sAll %2$s%4$s', '(link title)View all events(link text)All Events', 'event_espresso' ),
				$scope_obj->get_scope_icon() . '<a href="' . $scope_obj->get_admin_url( null ) . '" title="',
				$scope_obj->label->plural,
				'">',
				'</a>'
			);
		}
		$pro_objects = $this->promotion_objects();
		$obj = 0;
		if ( !empty( $pro_objects ) ) {
			$obj = count( $pro_objects ) > 1 ? $pro_objects : array_shift($pro_objects);
		}

		$applied_obj = $obj instanceof EE_Promotion_Object ? $obj->get_first_related( $this->scope() ) : $obj;

		if ( is_array( $applied_obj )  ) {
			$obj_ids = array();
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
		$start_date = $this->get_DateTime_object( 'PRO_start' );
		$end_date = $this->get_DateTime_object( 'PRO_end' );
		// if the promo starts at midnight on one day, and the promo ends at midnight on the very next day
		// (this also verifies that $dates are DateTime objects)
		if ( EEH_DTT_Helper::dates_represent_one_24_hour_date( $start_date, $end_date ) ) {
			return $start_date->format( EE_Datetime_Field::mysql_time_format ) == '00:00:00' ? $this->get_i18n_datetime( 'PRO_start', $this->_dt_frmt ) : $this->get_i18n_datetime( 'PRO_start' );
		} else if ( ! $start_date instanceof DateTime && $end_date instanceof DateTime ) {
			return sprintf( _x( 'Ends: %s', 'Value is the end date for a promotion', 'event_espresso' ), $this->get_i18n_datetime( 'PRO_end' ) );
		} else if ( $start_date instanceof DateTime && ! $end_date instanceof DateTime ) {
			return sprintf( _x( 'Starts: %s', 'Value is the start date for a promotion', 'event_espresso' ), $this->get_i18n_datetime( 'PRO_start' ) );
		} else if ( $start_date instanceof DateTime && $end_date instanceof DateTime ) {
			return sprintf( _x( '%s - %s', 'First value is start date and second value is end date in a date range.', 'event_espresso' ), $this->get_i18n_datetime( 'PRO_start' ), $this->get_i18n_datetime( 'PRO_end' ) );
		} else {
			return __( 'Ongoing Promotion', 'event_espresso' );
		}
	}



	/**
	 * Gets the number of times this promotion can be used per scope.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	public function uses(){
		return $this->get('PRO_uses');
	}


	/**
	 * Gets the number of times this promotions can be used considering how many scope objects the promotion applies to.
	 * Note that this means that we retrieve the number of scopes applied and multiply that by the uses.
	 *
	 * @return int
	 */
	public function uses_available() {
		$uses =  $this->get('PRO_uses');
		$scope_count = $this->get_scope_object_count();
		$global_uses = $this->global_uses();
		$total_uses = $uses === EE_INF_IN_DB || $scope_count === 0 ? $uses : $uses * $scope_count;

		//global uses trumps the value above unless $uses is less than it.
		return $global_uses === EE_INF_IN_DB || $global_uses > $total_uses ? $total_uses : $global_uses;
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
		return $this->uses() === EE_INF_IN_DB ? EE_INF : $this->uses() - $this->redeemed( $OBJ_ID );
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
		return $this->uses() === EE_INF_IN_DB ? EE_INF : $this->uses() - $promotion_object->used();
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

		//active (which means that the promotion is currently able to be used)
		if ( ( $start < $now && ( ! empty( $end ) && $end > $now ) ) || ( empty( $start ) && empty( $end ) ) || ( empty( $start ) && $end > $now ) || ( empty( $end ) && $start < $now ) ) {
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
	 * Sets how many times this promotion can be used for the given scope.
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
	 * Sets the number of times this promotion can be used globally.
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
	 * This gets the count of scope objects this promotion applies to
	 *
	 * @return int
	 */
	public function get_scope_object_count() {
		$scope_count = 0;
		if ( $this->scope_obj() instanceof EE_Promotion_Scope ) {
			$scope_count = $this->scope_obj()->count_applied_to_items( $this->ID() );
		}
		return $scope_count;
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




	/**
	 * Implementation for EEI_Has_Icon interface method.
	 * @see EEI_Visual_Representation for comments
	 * @return string
	 */
	public function get_icon() {
		return '<span class="dashicons dashicons-tag"></span>';
	}




	/**
	 * Implementation for EEI_Admin_Links interface method.
	 * @see EEI_Admin_Links for comments
	 * @return string
	 */
	public function get_admin_details_link() {
		return $this->get_admin_edit_link();
	}






	/**
	 * Implementation for EEI_Admin_Links interface method.
	 * @see EEI_Admin_Links for comments
	 * @return string
	 */
	public function get_admin_edit_link() {
		EE_Registry::instance()->load_helper('URL');
		return EEH_URL::add_query_args_and_nonce( array(
			'page' => 'espresso_promotions',
			'action' => 'edit',
			'PRO_ID' => $this->ID()
		),
			admin_url( 'admin.php' )
		);
	}



	/**
	 * Implementation for EEI_Admin_Links interface method.
	 * @see EEI_Admin_Links for comments
	 * @return string
	 */
	public function get_admin_settings_link() {
		EE_Registry::instance()->load_helper('URL');
		return EEH_URL::add_query_args_and_nonce( array(
			'page' => 'espresso_promotions',
			'action' => 'basic_settings'
		),
			admin_url( 'admin.php' )
		);
	}



	/**
	 * Implementation for EEI_Admin_Links interface method.
	 * @see EEI_Admin_Links for comments
	 * @return string
	 */
	public function get_admin_overview_link() {
		EE_Registry::instance()->load_helper('URL');
		return EEH_URL::add_query_args_and_nonce( array(
			'page' => 'espresso_promotions',
			'action' => 'default'
		),
			admin_url( 'admin.php' )
		);
	}



	/**
	 * _config
	 *
	 * @access protected
	 * @return EE_Promotions_Config
	 */
	protected function _config() {
		return EED_Promotions::instance()->set_config();
	}



}
