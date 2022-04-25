<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Yesnonulloption
{

    public function toOptionArray()
    {
        return array(
            array('value' => '', 'label' => 'Global Default'),
            array('value' => '1', 'label' => 'Yes'),
            array('value' => '0', 'label' => 'No'),
        );
    }

}
