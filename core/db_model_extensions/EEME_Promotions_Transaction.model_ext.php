<?php

/**
 *
 * EEM_Promotions_Transaction
 *
 * @package         Event Espresso
 * @subpackage
 * @author              Mike Nelson
 *
 */
class EEME_Promotions_Transaction extends EEME_Base
{
    public function __construct()
    {
        $this->_model_name_extended = 'Transaction';
        $this->_extra_relations = array(
            'Promotion_Object'=>new EE_Has_Many_Any_Relation(false)
        );
        parent::__construct();
    }
}
