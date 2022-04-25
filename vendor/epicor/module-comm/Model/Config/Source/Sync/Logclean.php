<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source\Sync;


class Logclean
{

    public function toOptionArray()
    {
        // values equate to no of seconds per unit
        $freq = array(
            array('value' => 'day', 'label' => "Day"),
            array('value' => 'week', 'label' => "Week"),
            array('value' => 'month', 'label' => "Month"),
            array('value' => 'year', 'label' => "Year"),
        );
        return $freq;
    }

}
