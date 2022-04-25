<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer\Locations;

use Magento\Framework\Event\Observer;

class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $storeManager;

    protected $commLocationsHelper;

    protected $registry;

    protected $request;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->storeManager = $storeManager;
        $this->commLocationsHelper = $commLocationsHelper;
        $this->registry = $registry;
        $this->request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

    }


}

