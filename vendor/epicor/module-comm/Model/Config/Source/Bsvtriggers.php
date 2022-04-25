<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Bsvtriggers
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'billing_address', 'label' => 'Billing Address'),
            array('value' => 'shipping_address', 'label' => 'Shipping Address'),
            array('value' => 'payment_method', 'label' => 'Payment Method'),
        );
    }

}
