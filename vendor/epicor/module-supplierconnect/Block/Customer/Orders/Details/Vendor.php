<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Orders\Details;


class Vendor extends \Epicor\Supplierconnect\Block\Customer\Address
{

    public function _construct()
    {
        parent::_construct();
        $order = $this->registry->registry('supplier_connect_order_details');
        if($order) {
            $this->_addressData = $order->getVarienDataFromPath('purchase_order/supplier_address');
        }
        $this->setOnLeft(true);
        $this->setTitle(__('Vendor Information : '));
    }

}
