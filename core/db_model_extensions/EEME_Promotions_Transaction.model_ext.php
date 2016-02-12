<?php

if (!defined('EVENT_ESPRESSO_VERSION'))
	exit('No direct script access allowed');

/**
 *
 * EEM_Promotions_Transaction
 *
 * @package			Event Espresso
 * @subpackage		
 * @author				Mike Nelson
 *
 */
class EEME_Promotions_Transaction extends EEME_Base{
	public function __construct() {
		$this->_model_name_extended = 'Transaction';
		$this->_extra_relations = array(
			'Promotion_Object'=>new EE_Has_Many_Any_Relation( false )
		);
		parent::__construct();
	}
}

// End of file EEM_Promotions_Transaction.model_ext.php