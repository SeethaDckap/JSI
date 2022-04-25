<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Frequency
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'daily', 'label' => "Daily"),
            array('value' => 'weekly', 'label' => "Weekly"),
            array('value' => 'monthly', 'label' => "Monthly"),
        );
    }

}
