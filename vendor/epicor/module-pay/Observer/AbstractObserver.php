<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Pay\Observer;

use Magento\Framework\Event\Observer;

class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $payHelper;

    public function __construct(
        \Epicor\Pay\Helper\Data $payHelper
    ) {
        $this->payHelper = $payHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    }


}

