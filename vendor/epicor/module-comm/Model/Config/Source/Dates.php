<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Dates
{

    public function toOptionArray()
    {

        $dates = array();
        for ($i = 1; $i <= 28; $i++) {
            $dates[] = array('value' => $i, 'label' => $i);
        }
        return $dates;
    }

}
