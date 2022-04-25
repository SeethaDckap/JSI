<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Shipstatus
{

    public function toOptionArray()
    {
        return array(
            array('value' => '', 'label' => 'No'),
            array('value' => 'yes', 'label' => "Yes"),
            array('value' => 'b2b', 'label' => "B2B Yes"),
            array('value' => 'b2c', 'label' => "B2C"),
        );
    }

}
