<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source\Locations;


class Groupeddetails
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'uom', 'label' => 'Display By UOM'),
            array('value' => 'location', 'label' => 'Display By Location'),
        );
    }

}
