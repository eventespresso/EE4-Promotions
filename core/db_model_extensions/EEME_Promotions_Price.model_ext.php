<?php

/**
 *
 * EEM_Promotions_Event
 *
 * @package         Event Espresso
 * @subpackage
 * @author              Mike Nelson
 *
 */
class EEME_Promotions_Price extends EEME_Base
{
    public function __construct()
    {
        $this->_model_name_extended = 'Price';
        $this->_extra_relations = array(
            'Promotion'=>new EE_Has_Many_Relation()
        );
        parent::__construct();
    }
}
