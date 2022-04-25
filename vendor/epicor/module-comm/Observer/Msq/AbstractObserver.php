<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer\Msq;

use Magento\Framework\Event\Observer;

class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $commMessagingHelper;

    protected $request;

    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $productHelper;

    /**
     * @var \Magento\Framework\Registry 
     */
    protected $registry;

    /**
     * @var Epicor\AccessRight\Helper\Data
     */
    protected $accessRightHelper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    public function __construct(
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Product $productHelper,
        \Epicor\AccessRight\Helper\Data $accessRightHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->productHelper = $productHelper;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->registry = $registry;
        $this->accessRightHelper = $accessRightHelper;
        $this->messageManager = $messageManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    }

}

