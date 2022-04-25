<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Observer\Cart;

use Magento\Framework\Event\Observer;

class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $salesRepHelper;

    protected $request;
    
    protected $customerSession;

    public function __construct(
        \Epicor\SalesRep\Helper\Data $salesRepHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->salesRepHelper = $salesRepHelper;
        $this->customerSession = $customerSession;
        $this->request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    }


}

