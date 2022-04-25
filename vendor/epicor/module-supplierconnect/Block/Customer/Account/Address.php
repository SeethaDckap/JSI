<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Account;


class Address extends \Epicor\Supplierconnect\Block\Customer\Address
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    protected $_addressData;


    public function _construct()
    {
        parent::_construct();
        if ($details = $this->registry->registry('supplier_connect_account_details')) {
            $this->_addressData = $details->getSupplierAddress();
        }
        $this->setTitle(__('Supplier Information'));
    }

}