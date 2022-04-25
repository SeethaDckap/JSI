<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Orderordering
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'O', 'label' => "Order Number Ascending"),
            array('value' => 'o', 'label' => "Order Number Descending"),
            array('value' => 'R', 'label' => "Order Reference Ascending"),
            array('value' => 'r', 'label' => "Order Reference Descending"),
            array('value' => 'D', 'label' => "Date Ascending"),
            array('value' => 'd', 'label' => "Date Descending")
        );
    }

}
