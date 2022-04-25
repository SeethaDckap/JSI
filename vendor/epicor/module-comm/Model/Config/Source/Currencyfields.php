<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Currencyfields
{

    public function toOptionArray()
    {
        return array(
            array('value' => '', 'label' => ""),
            array('value' => 'currency_code', 'label' => "Currency Code"),
            array('value' => 'base_price', 'label' => "Base Price"),
            array('value' => 'price', 'label' => "Price"),
        );
    }

}
