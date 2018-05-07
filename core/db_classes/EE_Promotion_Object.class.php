<?php
/**
 * EE_Promotion_Object class
 *
 * @package         Event Espresso
 * @subpackage      includes/classes/EE_Answer.class.php
 * @author              Mike Nelson
 *
 * ------------------------------------------------------------------------
 */
class EE_Promotion_Object extends EE_Base_Class
{

    /** Price-to-Object ID", "event_espresso @var $_POB_ID*/
    protected $_POB_ID = null;
    /** Promotion Object", "event_espresso @var $_PRO_ID*/
    protected $_PRO_ID = null;
    /** ID of the Related Object", "event_espresso @var $_OBJ_ID*/
    protected $_OBJ_ID = null;
    /** Model of Related Object", "event_espresso @var $_POB_type*/
    protected $_POB_type = null;
    /** Times the promotion has been used for this object", "event_espresso @var $_POB_used*/
    protected $_POB_used = null;

    /**
     *
     * @var EE_Promotion
     */
    protected $_Promotion = null;
    /**
     *
     * @var EE_Event
     */
    protected $_Event = null;

    /**
     *
     * @var EE_Venue
     */
    protected $_Venue = null;
    /**
     *
     * @var EE_Ticket
     */
    protected $_Ticket = null;
    /**
     *
     * @var EE_Datetime
     */
    protected $_Datetime = null;

    /**
     *
     * @param array $props_n_values
     * @return EE_Promotion_Object
     */
    public static function new_instance($props_n_values = array())
    {
        $classname = __CLASS__;
        $has_object = parent::_check_for_object($props_n_values, $classname);
        return $has_object ? $has_object : new self($props_n_values);
    }

    /**
     *
     * @param array $props_n_values
     * @return EE_Promotion_Object
     */
    public static function new_instance_from_db($props_n_values = array())
    {
        return new self($props_n_values, true);
    }

    /**
     * Gets promotion_ID
     * @return int
     */
    public function promotion_ID()
    {
        return $this->get('PRO_ID');
    }

    /**
     * Sets promotion_ID
     * @param int $promotion_ID
     */
    public function set_promotion_ID($promotion_ID)
    {
        $this->set('PRO_ID', $promotion_ID);
    }

    /**
     * Gets OBJ_ID
     * @return int
     */
    public function OBJ_ID()
    {
        return $this->get('OBJ_ID');
    }

    /**
     * Sets OBJ_ID
     * @param int $OBJ_ID
     */
    public function set_OBJ_ID($OBJ_ID)
    {
        $this->set('OBJ_ID', $OBJ_ID);
    }
    /**
     * Gets type
     * @return string
     */
    public function type()
    {
        return $this->get('POB_type');
    }

    /**
     * Sets type
     * @param string $type
     */
    public function set_type($type)
    {
        $this->set('POB_type', $type);
    }
    /**
     * Gets used
     * @return int
     */
    public function used()
    {
        return $this->get('POB_used');
    }

    /**
     * Sets used
     * @param int $used
     */
    public function set_used($used)
    {
        $this->set('POB_used', $used);
    }

    /**
     * increment_used used
     * @param int $amount
     */
    public function increment_used($amount = 1)
    {
        $this->set_used($this->used() + $amount);
    }

    /**
     * Gets the object that this model-joins-to. Eg, if this promotion-object join model object
     * applies the promotion to an event (ie, has POB_type=='Event'), then it will return an EE_Event
     * @return EE_Base_Class (one of the model objects that the field OBJ_ID can point to... see the 'OBJ_ID' field on EEM_Promotion_Object)
     */
    public function object()
    {
        $model_name_of_related_obj = $this->type();
        $is_model_name = EE_Registry::instance()->is_model_name($model_name_of_related_obj);
        if (! $is_model_name) {
            return null;
        } else {
            return $this->get_first_related($model_name_of_related_obj);
        }
    }
}
