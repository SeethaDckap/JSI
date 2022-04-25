<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model\Config\Source;


class ErpToggleAllowed
{
     public function toOptionArray()
    {
        return array(
            array('value' => '2', 'label' => 'Global Default'),
            array('value' => '1', 'label' => 'Yes'),
            array('value' => '0', 'label' => 'No'),
        );
    }

}
