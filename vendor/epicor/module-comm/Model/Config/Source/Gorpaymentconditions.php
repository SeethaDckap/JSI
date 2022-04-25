<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Gorpaymentconditions
{

    const DONT_SEND = 0;
    const SEND_CAPTURED_PAYMENTS_ONLY = 'captured';
    const SEND_AUTHORISED_PAYMENTS = 'authorised';

    public function toOptionArray()
    {
        return array(
            array('value' => self::DONT_SEND, 'label' => "Don't Send"),
            array('value' => self::SEND_CAPTURED_PAYMENTS_ONLY, 'label' => "Send Captured Payments Only"),
            array('value' => self::SEND_AUTHORISED_PAYMENTS, 'label' => "Send Authorised & Captured Payments"),
        );
    }

}
