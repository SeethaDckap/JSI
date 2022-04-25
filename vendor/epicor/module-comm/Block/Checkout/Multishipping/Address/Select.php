<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Checkout\Multishipping\Address;


/**
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Select extends \Magento\Multishipping\Block\Checkout\Address\Select
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Common\Helper\Data $commonHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->commonHelper = $commonHelper;
    }
    public function restrictAddressTypes()
    {
        return $this->scopeConfig->isSetFlag('Epicor_Comm/address/force_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getAddressCollection()
    {
        $collection = $this->getData('address_collection');
        if (is_null($collection)) {
            $collection = ($this->restrictAddressTypes()) ? $this->_getCheckout()->getCustomer()->getAddressesByType('invoice') : $this->_getCheckout()->getCustomer()->getAddresses();
            $this->setData('address_collection', $collection);
        }
        return $collection;
    }

    public function canAddNew()
    {
        $helper = $this->commonHelper;
        /* @var $helper Epicor_Comm_Helper_Data */

        return $helper->customerAddressPermissionCheck('create');
    }

}
