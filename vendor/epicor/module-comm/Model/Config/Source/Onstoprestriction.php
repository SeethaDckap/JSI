<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


/**
 * Onstop restriction config dropdown source
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Onstoprestriction
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'none', 'label' => 'No restriction - Epicor payment method unavailable'),
            array('value' => 'checkout', 'label' => 'Cannot access checkout'),
            array('value' => 'cart_checkout', 'label' => 'Cannot add to cart + cannot access checkout'),
            array('value' => 'login', 'label' => 'Cannot login')
        );
    }

}
