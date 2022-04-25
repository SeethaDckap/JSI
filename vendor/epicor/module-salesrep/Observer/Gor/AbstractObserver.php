<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Observer\Gor;

use Magento\Framework\Event\Observer;

class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $salesRepHelper;

    public function __construct(
        \Epicor\SalesRep\Helper\Data $salesRepHelper
    ) {
        $this->salesRepHelper = $salesRepHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    }


}

