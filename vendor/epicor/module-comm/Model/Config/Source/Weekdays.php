<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Weekdays
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'Monday', 'label' => "Monday"),
            array('value' => 'Tuesday', 'label' => "Tuesday"),
            array('value' => 'Wednesday', 'label' => "Wednesday"),
            array('value' => 'Thursday', 'label' => "Thursday"),
            array('value' => 'Friday', 'label' => "Friday"),
            array('value' => 'Saturday', 'label' => "Saturday"),
            array('value' => 'Sunday', 'label' => "Sunday"),
        );
    }

}
