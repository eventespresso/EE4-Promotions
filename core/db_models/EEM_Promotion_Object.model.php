<?php

/**
 * EEM_Promotion_Object
 * Promotion-to-almost-anything Model. Establishes that a certain promotion
 * CAN BE USED on certain objects
 *
 * @package         Event Espresso
 * @subpackage      includes/models/
 * @author          Michael Nelson
 */
class EEM_Promotion_Object extends EEM_Base
{
    protected static ?EEM_Promotion_Object $_instance = null;


    /**
     * @param string|null $timezone
     * @throws EE_Error
     */
    protected function __construct(?string $timezone = '')
    {
        if (! class_exists('EE_Promotion_Scope')) {
            require_once EE_PROMOTIONS_PATH . 'lib/scopes/EE_Promotion_Scope.lib.php';
        }
        $this->singular_item    = esc_html__('Status', 'event_espresso');
        $this->plural_item      = esc_html__('Stati', 'event_espresso');
        $this->_tables          = [
            'Promotion_Object' => new EE_Primary_Table('esp_promotion_object', 'POB_ID'),
        ];
        $relations              = ['Event', 'Venue', 'Datetime', 'Ticket', 'Transaction'];
        $this->_fields          = [
            'Promotion_Object' => [
                'POB_ID'   => new EE_Primary_Key_Int_Field(
                    'POB_ID', esc_html__("Promotion Object ID", "event_espresso")
                ),
                'PRO_ID'   => new EE_Foreign_Key_Int_Field(
                    'PRO_ID',
                    esc_html__("Promotion ID", "event_espresso"),
                    false,
                    0,
                    'Promotion'
                ),
                'OBJ_ID'   => new EE_Foreign_Key_Int_Field(
                    'OBJ_ID',
                    esc_html__("Related Object ID", "event_espresso"),
                    false,
                    0,
                    $relations
                ),
                'POB_type' => new EE_Any_Foreign_Model_Name_Field(
                    'POB_type',
                    esc_html__("Model of Related Object", "event_espresso"),
                    false,
                    EE_Promotion_Scope::SCOPE_EVENT,
                    $relations
                ),
                'POB_used' => new EE_Integer_Field(
                    'POB_used',
                    esc_html__(
                        "Times the promotion has been used for this object",
                        "event_espresso"
                    ),
                    false,
                    0
                ),

            ],
        ];
        $this->_model_relations = [
            'Event'       => new EE_Belongs_To_Any_Relation(),
            'Venue'       => new EE_Belongs_To_Any_Relation(),
            'Datetime'    => new EE_Belongs_To_Any_Relation(),
            'Ticket'      => new EE_Belongs_To_Any_Relation(),
            'Transaction' => new EE_Belongs_To_Any_Relation(),
            'Promotion'   => new EE_Belongs_To_Relation(),
        ];

        parent::__construct($timezone);
    }
}
