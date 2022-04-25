<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source\Customer;


/**
 * cusotmer disableable functionality list
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class disablefunctionality
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'prices', 'label' => 'Price Display'),
            array('value' => 'cart', 'label' => 'Cart'),
        );
    }

}
