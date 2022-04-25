<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\Config\Source;


/**
 * List Config actions
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Enablecarttolistat
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'M', 'label' => 'Mini Cart'),
            array('value' => 'C', 'label' => 'Cart Detail Page'),
            array('value' => 'O', 'label' => 'Order Summary Page'),
            array('value' => 'L', 'label' => 'Product List Page'),
            array('value' => 'D', 'label' => 'Product Detail Page'),
        );
    }

}
