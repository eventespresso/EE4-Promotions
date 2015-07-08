<?php

if (!defined('EVENT_ESPRESSO_VERSION'))
	exit('No direct script access allowed');

/**
 *
 * Model Extension on EEM_Line_Item for Promotions
 *
 * @package			EE Promotions
 * @subpackage      models
 * @author			Darren Ethier
 *
 */
class EEME_Promotions_Line_Item extends EEME_Base{
	public function __construct() {
		add_filter( 'FHEE__EEM_Line_Item__line_items_can_be_for', array( $this, 'register_promotion' ) );
		$this->_model_name_extended = 'Line_Item';
		$this->_extra_relations = array(
			'Promotion'=>new EE_Belongs_To_Any_Relation()
		);
		parent::__construct();
	}


	/**
	 * Callback for FHEE__EEM_Line_Item__line_items_can_be_for filter.
	 * @param $line_items_can_be_registered_for
	 *
	 * @return array
	 */
	public function register_promotion( $line_items_can_be_registered_for ) {
		$line_items_can_be_registered_for[] = 'Promotion';
		return $line_items_can_be_registered_for;
	}

}

// End of file EEM_Promotions_Event.mode_ext.php