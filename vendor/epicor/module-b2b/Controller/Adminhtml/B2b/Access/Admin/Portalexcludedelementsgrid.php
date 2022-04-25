<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\B2b\Controller\Adminhtml\B2b\Access\Admin;

class Portalexcludedelementsgrid extends \Epicor\B2b\Controller\Adminhtml\B2b\Access\Admin
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->cache = $cache;
        $this->storeManager = $storeManager;
    }
    public function execute()
    {
        $this->loadLayout();
        $elements = $this->getRequest()->getParam('portalelements');
        $this->getLayout()->getBlock('portalelements.grid')->setSelected($elements);
        $this->renderLayout();
    }

}
