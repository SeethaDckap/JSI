<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Reports\Observer;

use Magento\Framework\Event\Observer;

class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $reportsRawdataFactory;

    public function __construct(
        \Epicor\Reports\Model\RawdataFactory $reportsRawdataFactory
    ) {
        $this->reportsRawdataFactory = $reportsRawdataFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

    }


}

