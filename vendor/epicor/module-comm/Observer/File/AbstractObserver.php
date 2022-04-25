<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer\File;

use Magento\Framework\Event\Observer;

class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $commFileHelper;

    public function __construct(
        \Epicor\Comm\Helper\File $commFileHelper
    ) {
        $this->commFileHelper = $commFileHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    }


}

