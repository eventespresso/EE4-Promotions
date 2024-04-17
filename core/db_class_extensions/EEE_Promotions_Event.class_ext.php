<?php

/**
 *
 * EE_Promotions_Event
 *
 * @package             Event Espresso
 * @subpackage
 * @author              Mike Nelson
 *
 */
class EEE_Promotions_Event extends EEE_Base_Class
{
    public function __construct()
    {
        $this->_model_name_extended = EE_Promotion_Scope::SCOPE_EVENT;
        parent::__construct();
    }


    /**
     * Adds the function 'promotions' onto each EE_Event object.
     * Gets all the promotions for this event, (but doesn't cache its results or anything).
     *
     * @param array $query_params
     * @return EE_Promotion[]
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function ext_promotions(array $query_params = []): array
    {
        $query_params = array_replace_recursive([['Promotion_Object.Event.EVT_ID' => $this->_->ID()]], $query_params);
        return EEM_Promotion::instance()->get_all($query_params);
    }
}
