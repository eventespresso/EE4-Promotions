<?php

/**
 * EE_Promotion_Object class
 *
 * @package     Event Espresso
 * @subpackage  includes/classes/EE_Answer.class.php
 * @author      Mike Nelson
 */
class EE_Promotion_Object extends EE_Base_Class
{
    /**
     * @param array $props_n_values
     * @return EE_Promotion_Object
     * @throws EE_Error
     * @throws ReflectionException
     */
    public static function new_instance(array $props_n_values = array()): EE_Promotion_Object
    {
        $has_object = parent::_check_for_object($props_n_values, __CLASS__);
        return $has_object ?: new EE_Promotion_Object($props_n_values);
    }


    /**
     * @param array $props_n_values
     * @return EE_Promotion_Object
     * @throws EE_Error
     * @throws ReflectionException
     */
    public static function new_instance_from_db(array $props_n_values = array()): EE_Promotion_Object
    {
        return new EE_Promotion_Object($props_n_values, true);
    }


    /**
     * Gets promotion_ID
     *
     * @return int
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function promotion_ID(): int
    {
        return (int) $this->get('PRO_ID');
    }


    /**
     * Sets promotion_ID
     *
     * @param int $promotion_ID
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function set_promotion_ID(int $promotion_ID)
    {
        $this->set('PRO_ID', $promotion_ID);
    }


    /**
     * Gets OBJ_ID
     *
     * @return int
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function OBJ_ID(): int
    {
        return (int) $this->get('OBJ_ID');
    }


    /**
     * Sets OBJ_ID
     *
     * @param int $OBJ_ID
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function set_OBJ_ID(int $OBJ_ID)
    {
        $this->set('OBJ_ID', $OBJ_ID);
    }


    /**
     * Gets type
     * @return string
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function type(): string
    {
        return (string) $this->get('POB_type');
    }


    /**
     * Sets type
     *
     * @param string $type
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function set_type(string $type)
    {
        $this->set('POB_type', $type);
    }


    /**
     * Gets used
     * @return int
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function used(): int
    {
        return (int) $this->get('POB_used');
    }


    /**
     * Sets used
     *
     * @param int $used
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function set_used(int $used)
    {
        $this->set('POB_used', $used);
    }


    /**
     * increment_used used
     *
     * @param int $amount
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function increment_used(int $amount = 1)
    {
        $this->set_used($this->used() + $amount);
    }


    /**
     * Gets the object that this model-joins-to. Eg, if this promotion-object join model object
     * applies the promotion to an event (ie, has POB_type==EE_Promotion_Scope::SCOPE_EVENT), then it will return an EE_Event
     * @return EE_Base_Class (one of the model objects that the field OBJ_ID can point to... see the 'OBJ_ID' field on EEM_Promotion_Object)
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function object(): ?EE_Base_Class
    {
        $model_name_of_related_obj = $this->type() ?? '';
        if (EE_Registry::instance()->is_model_name($model_name_of_related_obj)) {
            return $this->get_first_related($model_name_of_related_obj);
        }
        return null;
    }
}
