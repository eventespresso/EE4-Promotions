<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');
require_once ( EE_MODELS . 'EEM_Base.model.php' );
/**
 * EEM_Promotion_Object
 *
 * Promotion-to-almost-anything Model. Establishes that a certain promotion
 * CAN BE USED on certain objects
 *
 * @package			Event Espresso
 * @subpackage		includes/models/
 * @author			Michael Nelson
 *
 */
class EEM_Promotion_Object extends EEM_Base {

	/**
	 * @var EEM_Promotion_Object
	 */
	protected static $_instance = NULL;



	/**
	 * 	constructor
	 * @return EEM_Promotion_Object
	 */
	protected function __construct(){
		$this->singular_item = __('Status','event_espresso');
		$this->plural_item = __('Stati','event_espresso');
		$this->_tables = array(
			'Promotion_Object'=> new EE_Primary_Table('esp_promotion_object', 'POB_ID')
		);
		$relations = array('Event','Venue','Datetime','Ticket','Transaction');
		$this->_fields = array(
			'Promotion_Object'=>array(
				'POB_ID'=>new EE_Primary_Key_Int_Field('POB_ID', __("Promotion Object ID", "event_espresso")),
				'PRO_ID'=>new EE_Foreign_Key_Int_Field('PRO_ID', __("Promotion ID", "event_espresso"), false, 0, 'Promotion'),
				'OBJ_ID'=>new EE_Foreign_Key_Int_Field('OBJ_ID', __("Related Object ID", "event_espresso"), false, 0, $relations),
				'POB_type'=>new EE_Any_Foreign_Model_Name_Field('POB_type', __("Model of Related Object", "event_espresso"),false, 'Event',$relations),
				'POB_used'=>new EE_Integer_Field('POB_used', __("Times the promotion has been used for this object", "event_espresso"), false,0)

			));
		$this->_model_relations = array(
			'Event'=>new EE_Belongs_To_Any_Relation(),
			'Venue'=>new EE_Belongs_To_Any_Relation(),
			'Datetime'=>new EE_Belongs_To_Any_Relation(),
			'Ticket'=>new EE_Belongs_To_Any_Relation(),
			'Transaction'=>new EE_Belongs_To_Any_Relation(),
			'Promotion'=>new EE_Belongs_To_Relation(),
		);

		parent::__construct();
	}


}
//
// End of file EEM_Promotion_Object.model.php
// Location: /includes/models/EEM_Promotion_Object.model.php