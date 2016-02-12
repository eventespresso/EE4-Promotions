<?php

if (!defined('EVENT_ESPRESSO_VERSION'))
	exit('No direct script access allowed');

/**
 *
 * EEM_Promotions_Event
 *
 * @package			Event Espresso
 * @subpackage		
 * @author				Mike Nelson
 *
 */
class EEME_Promotions_Event extends EEME_Base{
	public function __construct() {
		$this->_model_name_extended = 'Event';
		$this->_extra_relations = array(
			'Promotion_Object'=>new EE_Has_Many_Any_Relation( false )
		);
		parent::__construct();
	}
	
}

// End of file EEM_Promotions_Event.mode_ext.php