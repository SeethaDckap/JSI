<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Locationstyle
{

    public function toOptionArray()
    {
        return [
            ['value' => 'location_view', 'label' => 'Location View'],
            ['value' => 'inventory_view', 'label' => 'Inventory View']
        ];
    }

}
