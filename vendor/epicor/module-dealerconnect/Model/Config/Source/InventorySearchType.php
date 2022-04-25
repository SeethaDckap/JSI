<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model\Config\Source;


class InventorySearchType
{
    public function toOptionArray()
    {
        return array(
            array('value' => '0', 'label' => 'Own Dealership Only'),
            array('value' => '1', 'label' => 'All Dealership'),
            array('value' => '2', 'label' => 'Dealer Groups'),
        );
    }

}
