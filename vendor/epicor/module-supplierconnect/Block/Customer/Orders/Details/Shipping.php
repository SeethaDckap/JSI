<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Orders\Details;


class Shipping extends \Epicor\Supplierconnect\Block\Customer\Address
{


    public function _construct()
    {
        parent::_construct();
        $order = $this->registry->registry('supplier_connect_order_details');
        if($order) {
            $this->_addressData = $order->getVarienDataFromPath('purchase_order/delivery_address');
        }
        $this->setOnRight(true);
        $this->setTitle(__('Ship To: '));
    }

}
