<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;
class Stocklevel
{

    public function toOptionArray()
    {
        return array(
            array('value' => '', 'label' => ' '),
            array('value' => 'bool', 'label' => "In / Out of Stock"),
            array('value' => 'level', 'label' => "Stock Level"),
            array('value' => 'range', 'label' => "Traffic Lights"),
        );
    }

}
