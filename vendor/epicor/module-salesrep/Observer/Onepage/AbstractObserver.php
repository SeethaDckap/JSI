<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Observer\Onepage;

use Magento\Framework\Event\Observer;

class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $salesRepCheckoutHelper;

    protected $customerSession;

    protected $commHelper;

    protected $customerCustomerFactory;

    protected $request;

    protected $checkoutSession;

    protected $registry;
    
    
    /**
     * @var \Epicor\Comm\Model\Customer\Erpaccount\AddressFactory
     */
    protected $commCustomerErpaccountAddressFactory;        

    public function __construct(
        \Epicor\SalesRep\Helper\Checkout $salesRepCheckoutHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Comm\Model\Customer\Erpaccount\AddressFactory $commCustomerErpaccountAddressFactory,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Registry $registry
    ) {
        $this->salesRepCheckoutHelper = $salesRepCheckoutHelper;
        $this->commCustomerErpaccountAddressFactory = $commCustomerErpaccountAddressFactory;
        $this->customerSession = $customerSession;
        $this->commHelper = $commHelper;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
        $this->registry = $registry;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    }


}