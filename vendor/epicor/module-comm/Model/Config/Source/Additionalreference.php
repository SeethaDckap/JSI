<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Additionalreference
{

    public function toOptionArray()
    {
        return array(
            array('value' => '', 'label' => 'No'),
            array('value' => 'yes', 'label' => "Yes"),
            array('value' => 'b2b', 'label' => "B2B"),
            array('value' => 'b2bm', 'label' => "B2B Mandatory"),
            array('value' => 'b2c', 'label' => "B2C"),
            array('value' => 'b2cm', 'label' => "B2C Mandatory"),
            array('value' => 'm', 'label' => "Mandatory"),
        );
    }

}
