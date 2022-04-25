<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source\Returns;


class Findlinesby
{

    const FIND_BY_ORDER_NUMBER = 'order_number';
    const FIND_BY_INVOICE_NUMBER = 'invoice_number';
    const FIND_BY_SHIPMENT_NUMBER = 'shipment_number';
    const FIND_BY_SERIAL_NUMBER = 'serial_number';

    public function toOptionArray()
    {
        return array(
            array('value' => self::FIND_BY_ORDER_NUMBER, 'label' => 'Order Number'),
            array('value' => self::FIND_BY_INVOICE_NUMBER, 'label' => 'Invoice Number'),
            array('value' => self::FIND_BY_SHIPMENT_NUMBER, 'label' => 'Shipment Number'),
            array('value' => self::FIND_BY_SERIAL_NUMBER, 'label' => 'Serial Number'),
        );
    }

}
