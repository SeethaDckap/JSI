<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Observer;

use Magento\Framework\Event\Observer;

class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $registry;

    protected $hostingManagerSiteFactory;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Epicor\HostingManager\Model\SiteFactory $hostingManagerSiteFactory
    ) {
        $this->registry = $registry;
        $this->hostingManagerSiteFactory = $hostingManagerSiteFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

    }


}

