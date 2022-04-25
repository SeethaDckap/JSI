<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Observer\Customer;

use Magento\Framework\Event\Observer;

class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $salesRepHelper;

    protected $customerSession;

    protected $salesRepAccountFactory;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    public function __construct(
        \Epicor\SalesRep\Helper\Data $salesRepHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\SalesRep\Model\AccountFactory $salesRepAccountFactory,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->salesRepHelper = $salesRepHelper;
        $this->customerSession = $customerSession;
        $this->salesRepAccountFactory = $salesRepAccountFactory;
        $this->response = $response;
        $this->urlBuilder = $urlBuilder;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

    }


}

