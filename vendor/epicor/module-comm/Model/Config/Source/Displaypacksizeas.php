<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Displaypacksizeas
{

    public function toOptionArray()
    {
        return array(
            array('value' => '1', 'label' => 'UOM Description'),
            array('value' => '2', 'label' => 'UOM Code'),
            array('value' => '3', 'label' => 'UOM Description & Code')
        );
    }

}
