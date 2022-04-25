<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Soutrigger
{

    public function toOptionArray()
    {
        return array(
            array('value' => "", 'label' => ""),
            array('value' => "Refund Offline", 'label' => "Refund Offline"),
            array('value' => "Refund Online", 'label' => "Refund Online"),
            array('value' => "Invoice Offline", 'label' => "Invoice Offline"),
            array('value' => "Invoice Online", 'label' => "Invoice Online"),
            array('value' => "Ship", 'label' => "Ship"),
        );
    }

}
