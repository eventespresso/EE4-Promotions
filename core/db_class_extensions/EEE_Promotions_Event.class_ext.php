<?php

if (!defined('EVENT_ESPRESSO_VERSION'))
	exit('No direct script access allowed');

/**
 *
 * EE_Promotions_Event
 *
 * @package			Event Espresso
 * @subpackage
 * @author				Mike Nelson
 *
 */
class EEE_Promotions_Event extends EEE_Base_Class{

	/**
	 * 	constructor
	 * @return EEE_Promotions_Event
	 */
	public function __construct() {
		$this->_model_name_extended = 'Event';
		parent::__construct();
	}

	/**
	 * Adds the function 'promotions' onto each EE_Event object.
	 * Gets all the promotions for this event, (but doesn't cache its results or anything).
	 * @param array $query_params
	 * @return EE_Promotion[]
	 */
	public function ext_promotions($query_params = array()){
		$query_params = array_replace_recursive(array(array('Promotion_Object.Event.EVT_ID'=>$this->_->ID())), $query_params);
		return EEM_Promotion::instance()->get_all($query_params);
	}
}

// End of file EE_Promotions_Event.class_ext.php
