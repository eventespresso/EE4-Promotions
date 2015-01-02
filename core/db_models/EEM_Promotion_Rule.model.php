<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');
require_once ( EE_MODELS . 'EEM_Base.model.php' );
/**
 *
 * Promotion-Rule join Model
 *
 * @package			Event Espresso
 * @subpackage		includes/models/
 * @author				Michael Nelson
 *
 */
class EEM_Promotion_Rule extends EEM_Base {

  	// private instance of the Attendee object
	protected static $_instance = NULL;

	/**
	 *		This function is a singleton method used to instantiate the EEM_Attendee object
	 *
	 *	@access public
	 * @param string $timezone
	 *	@return EEM_Promotion_Rule
	 */
	public static function instance( $timezone = NULL ){

		// check if instance of EEM_Attendee already exists
		if ( self::$_instance === NULL ) {
			// instantiate Espresso_model
			self::$_instance = new self( $timezone );
		}
		// EEM_Attendee object
		return self::$_instance;
	}



	/**
	 *	@return EEM_Promotion_Rule
	 * @throws \EE_Error
	 */
	protected function __construct(){
		$this->singular_item = __('Promotion-Rule-Relation','event_espresso');
		$this->plural_item = __('Promotion-Rule-Relation','event_espresso');
		$this->_tables = array(
			'Promotion_Rule'=> new EE_Primary_Table('esp_promotion_rule', 'PRR_ID')
		);
		$this->_fields = array(
			'Promotion_Rule'=>array(
				'PRR_ID'=>new EE_Primary_Key_Int_Field('PRR_ID', __("Relation ID between Promotion and Rule", "event_espresso")),
				'PRO_ID'=>new EE_Foreign_Key_Int_Field('PRO_ID', __("Promotion ID", "event_espresso"), true, null, 'Promotion'),
				'RUL_ID'=>new EE_Foreign_Key_Int_Field('RUL_ID', __("Rule ID", "event_espresso"), true, null, 'Rule'),
				'PRR_order'=>new EE_Integer_Field('PRR_order', __("Order of this Rule in applying to the Promotion", "event_espresso"), false,0),
				'PRR_add_rule_comparison'=>new EE_Enum_Text_Field('PRR_add_rule_comparison', __("Comparison Operator", "event_espresso"), false, 'AND',
						array('AND'=>  __("And", "event_espresso"),'OR'=>  __("Or", "event_espresso")))
			));
		$this->_model_relations = array(
			'Promotion'=>new EE_Belongs_To_Relation(),
			'Rule'=>new EE_Belongs_To_Relation()
		);

		parent::__construct();
	}


}
// End of file EEM_Promotion_Rule.model.php
// Location: /includes/models/EEM_Promotion_Rule.model.php