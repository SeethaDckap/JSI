<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Invoices\Details;


class Address extends \Epicor\Supplierconnect\Block\Customer\Address
{
    public function _construct()
    {
        parent::_construct();
        $invoice = $this->registry->registry('supplier_connect_invoice_details');
        /* @var $order Epicor_Common_Model_Xmlvarien */
        if($invoice) {
            $this->_addressData = $invoice->getVarienDataFromPath('invoice/supplier_address');
        }

        $this->setTitle(__('Supplier Information: '));
    }
}
