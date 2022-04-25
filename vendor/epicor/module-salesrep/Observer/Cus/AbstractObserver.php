<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Observer\Cus;

class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $salesRepHelper;

    protected $salesRepAccountFactory;

    public function __construct(
        \Epicor\SalesRep\Helper\Data $salesRepHelper,
        \Epicor\SalesRep\Model\AccountFactory $salesRepAccountFactory
    ) {
        $this->salesRepHelper = $salesRepHelper;
        $this->salesRepAccountFactory = $salesRepAccountFactory;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

    }


}

