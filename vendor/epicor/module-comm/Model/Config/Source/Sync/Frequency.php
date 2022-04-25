<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source\Sync;


class Frequency
{

    public function toOptionArray()
    {
        // values equate to no of seconds per unit
        $freq = array(
            array('value' => '1', 'label' => "Seconds"),
            array('value' => '60', 'label' => "Minutes"),
            array('value' => '3600', 'label' => "Hours"),
            array('value' => '86400', 'label' => "Days"),
        );
        return $freq;
    }

}
