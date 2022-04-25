<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Product\Link\Product;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection
{
     protected $_http;
     protected $logger;

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $productHelper;

     public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Framework\App\Request\Http $http,
        \Epicor\Comm\Helper\Product $productHelper
    ) {

        $this->productHelper = $productHelper;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $moduleManager,
            $catalogProductFlatState,
            $scopeConfig,
            $productOptionFactory,
            $catalogUrl,
            $localeDate,
            $customerSession,
            $dateTime,
            $groupManagement
        );
        $this->_http = $http;
        $this->logger = $logger;
    }
    
     /**
     * Filter products with required options
     *
     * @return \Epicor\Comm\Model\ResourceModel\Product\Link\Product\Collection
     */
    public function addFilterByRequiredOptions()
    {
        return $this;
    }

    public function _afterLoad()
    {
        parent::_afterLoad();

        if ($this->_linkTypeId == \Magento\Catalog\Model\Product\Link::LINK_TYPE_RELATED) {
            $type = 'related';
        } else if ($this->_linkTypeId == \Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED) {
            $type = 'grouped';
        } else if ($this->_linkTypeId == \Magento\Catalog\Model\Product\Link::LINK_TYPE_UPSELL) {
            $type = 'upsell';
        } else if ($this->_linkTypeId == \Magento\Catalog\Model\Product\Link::LINK_TYPE_CROSSSELL) {
            $type = 'crosssell';
        }  else if ($this->_linkTypeId == \Epicor\Comm\Model\Catalog\Product\Link::LINK_TYPE_SUBSTITUTE) {
            $type = 'substitute';
        } else {
            $type = 'unknown';
        }

        $productListType = $this->_http->getParam('type');
        $moduleControllerAction = $this->_http->getModuleName()."_".$this->_http->getControllerName()."_".$this->_http->getActionName();
        //in M2 this is also fired on catalog category view and catalog_result_index, but not in M1 - it is not needed there as it fires an extra msq 
        $urlToIgnore = array('cms_index_index','catalog_category_view', 'catalogsearch_result_index', 'catalogsearch_advanced_result', 'eccsearch_ajax_suggest');
        $ProductView = "catalog_product_view";                
        $isLazyload = $this->productHelper->isLazyLoad();
        if($ProductView == $moduleControllerAction && $isLazyload && !$this->_http->isAjax()){
            array_push($urlToIgnore,$ProductView);
        }
        $cartView = "checkout_cart_index";
        if(($cartView == $moduleControllerAction) && $isLazyload && !$this->_http->isAjax()){
            array_push($urlToIgnore,$cartView);
        }

        if(!in_array ($moduleControllerAction, $urlToIgnore) &&
            !in_array($productListType, ['bestseller_product', 'featured_product','newsale_product'])){
            $this->_eventManager->dispatch('linked_products_collection_load', array('collection' => $this, 'type' => $type));
        }
    }

}
