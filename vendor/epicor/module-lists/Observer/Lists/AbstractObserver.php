<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Observer\Lists;

use Magento\Framework\Event\Observer;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;

class AbstractObserver implements \Magento\Framework\Event\ObserverInterface {

    protected $listsSessionHelper;
    protected $listsFrontendProductHelper;
    protected $listsFrontendContractHelper;
    protected $storeManager;
    protected $catalogResourceModelProductCollectionFactory;
    protected $scopeConfig;
    protected $listFrontendHelper;
    protected $_cookieManager;

    public function __construct(
    \Epicor\Lists\Helper\Session $listsSessionHelper, \Epicor\Lists\Helper\Frontend\Product $listsFrontendProductHelper, \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogResourceModelProductCollectionFactory, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,\Epicor\Lists\Helper\Frontend $listFrontendHelper, CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->listsSessionHelper = $listsSessionHelper;
        $this->listsFrontendProductHelper = $listsFrontendProductHelper;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->storeManager = $storeManager;
        $this->catalogResourceModelProductCollectionFactory = $catalogResourceModelProductCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->_cookieManager = $cookieManager;
        $this->listFrontendHelper = $listFrontendHelper;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        
    }
    
    public function isListsFilteringReq(){
        $productHelper = $this->listsFrontendProductHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Product */
        $listHelper = $this->listFrontendHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend */
        return $listHelper->listsEnabled() && $productHelper->hasFilterableLists() ? 1 : 0;
    }

}
