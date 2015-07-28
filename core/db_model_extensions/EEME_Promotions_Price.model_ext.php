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
class EEME_Promotions_Price extends EEME_Base{
	public function __construct() {
		$this->_model_name_extended = 'Price';
		$this->_extra_relations = array(
			'Promotion'=>new EE_Has_Many_Relation()
		);
		parent::__construct();
	}

}

// End of file EEM_Promotions_Event.mode_ext.php