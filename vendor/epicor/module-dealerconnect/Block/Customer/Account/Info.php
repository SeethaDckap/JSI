<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Customer\Account;

use Magento\Framework\Exception\NoSuchEntityException;

class Info extends \Epicor\Customerconnect\Block\Customer\Address
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Messaging\Customer
     */
    protected $commMessagingCustomerHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Messaging\Customer $commMessagingCustomerHelper,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->commMessagingCustomerHelper = $commMessagingCustomerHelper;
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $customerconnectHelper,
            $commMessagingHelper,
            $customerSession,
            $commonHelper,
            $dataObjectFactory,
            $data
        );
        $customer_helper = $this->commMessagingCustomerHelper;

        $customer = $this->customerSession->getCustomer();

        $addresses = $customer_helper->getErpAddresses($customer, 'registered');

        $address = null;

        if (!empty($addresses)) {
            $address = array_pop($addresses);
        } else {
            $addresses = $customer_helper->getErpAddresses($customer, 'invoice');
            if (!empty($addresses)) {
                $address = array_pop($addresses);
            }
        }
        if (!empty($address)) {
            $this->_addressData = $address;
        }
    }


    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_accessauthorization->isAllowed(
            'Dealer_Connect::dealerconnect_dashboard_account_information'
        )) {
            return '';
        }

        return $this->customerSession->getCustomer()->getId() ? parent::_toHtml() : '';
    }

}
