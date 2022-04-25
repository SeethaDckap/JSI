<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\QuickOrderPad\Block\Catalog\Product;


use Epicor\Comm\Model\Product;
use Epicor\QuickOrderPad\Block\Catalog\Product\Listing\Child;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

class Listing extends \Magento\Catalog\Block\Product\ListProduct
{

    protected $_processedInstock = false;

    /**
     * Catalog Product collection
     *
     *
     */
    protected $_productCollection;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Catalog\Model\Session
     */
    protected $catalogSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Quickorderpad
     */
    protected $listsQopHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $catalogResourceModelProductCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $catalogConfig;


    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $catalogProductVisibility;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Epicor\Lists\Helper\Frontend
     */
    protected $listsFrontendHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\CatalogSearch\Helper\Data
     */
    protected $catalogSearchHelper;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $checkoutCartHelper;

    /**
     * @var \Magento\Catalog\Helper\Output
     */
    protected $catalogOutputHelper;

    /**
     * @var \Magento\Catalog\Model\Layer\Search
     */
    protected $searchLayer;
    
    /**
     * @var \Epicor\Lists\Helper\Session
     */
    protected $listsSessionHelper;
    
    protected $_ignoreMultipleAddUrl = [
        'dealerconnect/inventory/linesearch',
    ];

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;
    private $columnSort;

    public function __construct(
        \Epicor\QuickOrderPad\Model\ColumnSort $columnSort,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Model\Session $catalogSession,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Epicor\Lists\Helper\Frontend\Quickorderpad $listsQopHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogResourceModelProductCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Epicor\Lists\Helper\Frontend $listsFrontendHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\CatalogSearch\Helper\Data $catalogSearchHelper,
        \Magento\Catalog\Helper\Output $catalogOutputHelper,
        \Epicor\Lists\Helper\Session $listsSessionHelper,
        \Magento\Catalog\Model\Layer\Search $searchLayer,
        \Epicor\Comm\Helper\Data $commHelper,
        array $data = []
    )
    {
        $this->customerSession = $customerSession;
        $this->request = $request;
        $this->catalogSession = $catalogSession;
        $this->registry = $context->getRegistry();
        $this->scopeConfig = $context->getScopeConfig();
        $this->commProductHelper = $commProductHelper;
        $this->listsQopHelper = $listsQopHelper;
        $this->catalogResourceModelProductCollectionFactory = $catalogResourceModelProductCollectionFactory;
        $this->storeManager = $context->getStoreManager();
        $this->catalogConfig = $context->getCatalogConfig();
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->eventManager = $context->getEventManager();
        $this->commLocationsHelper = $commLocationsHelper;
        $this->listsFrontendHelper = $listsFrontendHelper;
        $this->catalogSearchHelper = $catalogSearchHelper;
        $this->checkoutCartHelper = $context->getCartHelper();
        $this->catalogOutputHelper = $catalogOutputHelper;
        $this->searchLayer = $searchLayer;
        $this->listsSessionHelper = $listsSessionHelper;
        $this->commHelper = $commHelper;
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
        $this->columnSort = $columnSort;
    }

    /**
     * Retrieve url for direct adding product to cart
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     * @return string
     */
    public function getAddToCartUrl($product, $additional = array())
    {
        if ($this->hasCustomAddToCartUrl()) {
            return $this->getCustomAddToCartUrl();
        }

        if ($this->getRequest()->getParam('wishlist_next')) {
            $additional['wishlist_next'] = 1;
        }

//        $addUrlKey = Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED;
//        $addUrlValue = $this->getUrl('*/*/*', array('_use_rewrite' => true, '_current' => true));
//        
//        $additional[$addUrlKey] = Mage::helper('core')->urlEncode($addUrlValue);

        return $this->checkoutCartHelper->getAddUrl($product, $additional);
    }

    public function getReturnUrl()
    {
        return $this->getUrl('*/*/*', array('_use_rewrite' => true, '_current' => true));
    }

    public function getOrganiseBy()
    {
        $organiseBy = $this->request->getParam('organise_by', $this->catalogSession->getQopSortBy()) ?: 'uom';
        $this->catalogSession->setQopSortBy($organiseBy);

        return $organiseBy;
    }

    public function getPrimarySort(): string
    {
        if ($this->getOrganiseBy() === 'location' && !$this->getForceHideLocations()
            && $this->getHelper()->isLocationsEnabled()) {
            return 'location';
        }

        return 'uom';
    }

    public function getSecondarySort()
    {
        #return 'location';
        if ($this->getPrimarySort() == 'uom') {
            if ($this->getHelper()->isLocationsEnabled() && !$this->getForceHideLocations()) {
                return 'location';
            } else {
                return null;
            }
        } else {
            return 'uom';
        }
    }

    /**
     * Get Locations Helper
     * @return \Epicor\Comm\Helper\Locations
     */
    public function getHelper($type = null)
    {
        return $this->commLocationsHelper;
    }

    /**
     *
     * @param \Epicor\Comm\Model\Product $product
     * @return Array
     */
    public function getPrimaryItems($product)
    {
        $items = array();
        switch ($this->getPrimarySort()) {
            case 'location' :

                if ($product->getTypeId() == 'grouped') {
                    foreach ($this->getUOMProducts($product) as $uomProd) {
                        $items = array_merge($items, $uomProd->getCustomerLocations());
                    }
                } else {
                    $items = $product->getCustomerLocations();
                }
                break;

            case 'uom':
                if ($product->getTypeId() == 'grouped') {
                    $items = $this->getUOMProducts($product);
                    if(!$this->commHelper->isShowOutOfStock() && !$product->getIsEccNonStock()) {
                        $items = array_filter($items, function ($arrayValue) {
                            return $arrayValue->isSaleable();
                        });
                    }
                }
                if (empty($items)) {
                    $items[] = $product;
                }
                break;
        }

        return $items;
    }

    /**
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param \Epicor\Comm\Model\Product|\Epicor\Comm\Model\Location\Product $primaryProduct
     * @return type
     */
    public function getSecondaryItems($product, $primaryProduct)
    {
        $items = array();
        switch ($this->getSecondarySort()) {
            case 'location' :
                $items = $primaryProduct->getCustomerLocations();
                break;

            case 'uom':
                if ($product->getTypeId() == 'grouped') {
                    $items = $this->getUOMProducts($product, $primaryProduct->getLocationCode());
                }
                break;
        }

        if (empty($items)) {
            $items[] = $primaryProduct;
        }
        return $items;
    }

    public function getPrimaryRowspan($product)
    {
        $primaryProducts = $this->getPrimaryItems($product);
        $primary_rowspan = count($primaryProducts);
        foreach ($primaryProducts as $primaryProduct) {
            $primary_rowspan += $this->getSecondaryRowspan($product, $primaryProduct) - 1;
        }

        return $primary_rowspan;
    }

    public function getSecondaryRowspan($product, $primaryProduct)
    {
        $secondaryProducts = $this->getSecondaryItems($product, $primaryProduct);
        return count($secondaryProducts);
    }

    public function setProductData($productA, $productB)
    {
        $product = null;
        /* @var $product Epicor_Comm_Model_Product */
        $location = null;
        /* @var $location Epicor_Comm_Model_Location_Product */
        if ($productB instanceof \Epicor\Comm\Model\Location\Product) {
            $location = $productB;
        } elseif ($productA instanceof \Epicor\Comm\Model\Location\Product) {
            $location = $productA;
        }
        if ($productB instanceof \Epicor\Comm\Model\Product) {
            $product = $productB;
        } elseif ($productA instanceof \Epicor\Comm\Model\Product) {
            $product = $productA;
        }
        if ($location && $product) {
            $product->setToLocationPrices($location->getLocationCode());
        }
        $this->registry->unregister('current_loop_product');
        $this->registry->register('current_loop_product', $product);
        $this->registry->unregister('current_location');
        $this->registry->register('current_location', $location);
    }

    /**
     * Gets an array of UOM products from a UOM product
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return array
     */
    public function getUOMProducts($product, $locationCode = null)
    {
        $result = array();
        if ($product->getTypeId() == 'grouped') {
            $result = $product->getTypeInstance(true)
                ->getAssociatedProducts($product);
            $storeId = $product->getStoreId();
            foreach ($result as $key => $item) {
                /* @var $item Epicor_Comm_Model_Product */
                $item->setStoreId($storeId);
                if (($locationCode && $item->getLocation($locationCode) === false) || ($this->useLists() && !$this->isInCurrentList($item->getId()))) {
                    unset($result[$key]);
                }
            }
            if(!$this->commHelper->isShowOutOfStock() && !$product->getIsEccNonStock()) {
                $result = !empty($result) ? array_filter($result, function ($arrayValue) {
                    return $arrayValue->isSaleable();
                }) : [];
            }
        }
        return $result;
    }

    /**
     * Get if the stock column can be shown on the quick order pad search results
     *
     * @return bool
     */
    public function showStockLevelDisplay()
    {
        return $this->scopeConfig->getValue('Epicor_Comm/stock_level/display', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) != '';
    }

    public function showProductImageDisplay()
    {
        return $this->scopeConfig->isSetFlag('quickorderpad/general/show_quickorderpad_images', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function productHasOptions($product, $required = false)
    {
        $helper = $this->commProductHelper;
        /* @var $helper Epicor_Comm_Helper_Product */

        return $helper->productHasCustomOptions($product, $required);
    }

    /**
     * @return \Magento\Catalog\Helper\Output
     */
    public function getCatalogOutputHelper()
    {
        return $this->catalogOutputHelper;
    }

    /**
     * @return \Epicor\Comm\Helper\Product
     */
    public function getCommProductHelper()
    {
        return $this->commProductHelper;
    }

    /**
     * @return \Epicor\Comm\Helper\Locations
     */
    public function getCommLocationsHelper()
    {
        return $this->commLocationsHelper;
    }

    public function getLayer()
    {
        return $this->searchLayer;
    }

    /**
     * Retrieve loaded product collection
     *
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    protected function _getProductCollection()
    {

        if (is_null($this->_productCollection)) {
            $helper = $this->commProductHelper;

            $csv = $this->getRequest()->getParam('csv');
            if (($csv == 1 || $this->useLists())) {
                if ($csv == 1) {
                    $productsId = $helper->getConfigureListProductIds();
                } else {
                    $list = $this->listsQopHelper->getSessionList();
                    /* @var $list \Epicor\Lists\Model\ListModel */
                    if ($list) {
                        $productsId = $this->listsQopHelper->getProductIdsByList($list, true);
                        $this->registry->unregister('QOP_list_product_filter_applied');
                        $this->registry->register('QOP_list_product_filter_applied', true);
                    } else {
                        $productsId = array();
                    }
                }

                /* @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
                $collection = $this->catalogResourceModelProductCollectionFactory->create();
                
                $collection
                    ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
                    ->addAttributeToSelect('sku')
                    ->setStore($this->storeManager->getStore())
                    ->addMinimalPrice()
                    ->addFinalPrice()
                    ->addTaxPercents()
                    ->addStoreFilter()
                    ->addUrlRewrite()
                    ->setVisibility($this->catalogProductVisibility->getVisibleInSearchIds());

                $this->columnSort->setInitialDefaultOrder($collection);
                $collection->addFieldToFilter('entity_id', array('in' => $productsId));
                $this->_eventManager->dispatch(
                        'catalog_block_product_list_collection', ['collection' => $collection]
                );
                $this->_productCollection = $collection;
            }
        }

        return parent::_getProductCollection();
    }

    public function getLoadedProductCollection()
    {
        $csv = $this->getRequest()->getParam('csv');
        if ($csv == 1) {
            if(null !== $this->_productCollection){
                return $this->_productCollection;
            }
            $collection = $this->catalogResourceModelProductCollectionFactory->create()
                ->setStoreId($this->storeManager->getStore()->getId());

            $helper = $this->commProductHelper;
            /* @var $helper \Epicor\Comm\Helper\Product */
            $configurations = $helper->getConfigureListProductIds();

           $collection
                ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
                ->addAttributeToSelect('sku')
                ->setStore($this->storeManager->getStore())
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addStoreFilter()
                ->addUrlRewrite()
                ->setVisibility($this->catalogProductVisibility->getVisibleInSearchIds());

            $collection->addFieldToFilter('entity_id', $configurations);
            $this->eventManager->dispatch('epicor_comm_products_required_configuration', array('configuration' => $collection));

            $this->_productCollection = $collection;
            return $this->_productCollection;
        } else {
            $locHelper = $this->commLocationsHelper;
            // limit search to products in selected list 
            $list = $this->listsQopHelper->getSessionList();
            if (
                $locHelper->isLocationsEnabled() && $this->registry->registry('search-instock') ||
                $this->listsQopHelper->listsEnabled() && $list
            ) {
                $collection = parent::getLoadedProductCollection();
                if ($locHelper->isLocationsEnabled() && $this->registry->registry('search-instock')) {
                    foreach ($collection as $key => $product) {
                        $product->unsetData('salable');
                        if (!$product->isSaleable()) {
                            $collection->removeItemByKey($key);
                        }
                    }
                    $collcount = count($collection);
                    $this->registry->unregister('Epicor_Locations_Paging');
                    $this->registry->register('Epicor_Locations_Paging', $collcount);
                }
                $this->_productCollection = $collection;
                return $this->_productCollection;
            } else {
                return parent::getLoadedProductCollection();
            }
        }
    }

    public function getProductCollection()
    {
        return $this->_productCollection;
    }

    public function getHeaderText()
    {
        $headerText = $this->getData('header_text');
        if ($headerText && $headerText !== false) {
            return $headerText;
        }

        if ($list = $this->listsQopHelper->getSessionList()) {
            return $list->getTitle();
        }

        return false;
    }

    public function useLists()
    {
        if ($this->getRequest()->getParam('csv') !== 1 && $this->listsFrontendHelper->listsEnabled() && !$this->catalogSearchHelper->getEscapedQueryText()) {
            return true;
        }

        return false;
    }

    public function isInCurrentList($productId)
    {
        $list = $this->listsQopHelper->getSessionList();
        /* @var $list Epicor_Lists_Model_ListModel */
        $productIds = $list ? $this->listsQopHelper->getProductIdsByList($list) : array();

        return in_array($productId, $productIds);
    }

    //M1 > M2 Translation Begin (Rule 56)
    /**
     * @param $path
     * @return mixed
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $path
     * @return bool
     */
    public function getConfigFlag($path)
    {
        return $this->_scopeConfig->isSetFlag($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    //M1 > M2 Translation End

    //M1 > M2 Translation Begin (Rule p2-1)
    public function getCustomerSession()
    {
        return $this->customerSession;
    }
    //M1 > M2 Translation End

    //M1 > M2 Translation Begin (Rule p2-8)
    /**
     * @param $key
     * @return mixed
     */
    public function registry($key)
    {
        return $this->registry->registry($key);
    }

    /**
     * @param $key
     * @param $value
     * @param bool $graceful
     */
    public function register($key, $value, $graceful = false)
    {
        $this->registry->register($key, $value, $graceful);
    }

    /**
     * @param $key
     */
    public function unregister($key)
    {
        $this->registry->unregister($key);
    }
    //M1 > M2 Translation End

    /**
     * Checks if add to cart/quote button to be shown
     * 
     * @param boolean $showMultipleAdd
     * @return boolean
     */
    public function showMultipleAdd($showMultipleAdd = false)
    {
        $currentUrl = $this->_urlBuilder->getCurrentUrl();
        $eccHidePrices = $this->commHelper->getEccHidePrice();
        foreach ($this->_ignoreMultipleAddUrl as $_url) {
            if (strpos($currentUrl, $_url) !== false) {
               $showMultipleAdd = false; 
            }
        }
        return ($showMultipleAdd && (!$eccHidePrices || $eccHidePrices == 3 || $this->getIsRfq()));
    }

    public function getPriceBlock($alias, $useCache){

        $eccHidePrices = $this->commHelper->getEccHidePrice();
        if($eccHidePrices == 1 || $eccHidePrices == 3 || ($eccHidePrices == 2 && $this->getIsRfq())){
            return '';
        }
        $product = $this->registry('current_product');
        $loopProduct = $this->registry('current_loop_product')?: $product;
        if (is_null($loopProduct) === false) {
            if ($loopProduct->getTypeId() == 'configurable') {
                $layout = $this->getLayout();
                $name = $this->getNameInLayout();
                $childName = $layout->getChildName($name, $alias);
                $block = $layout->getBlock($childName);
                $collection = $this->getProductCollection();
                $block->setChildrenCollection($collection);
            }
        }
        return parent::getChildHtml($alias, $useCache);

    }

    public  function getCartBlock($alias, $useCache){
        $eccHidePrices = $this->commHelper->getEccHidePrice();
        if($eccHidePrices && $eccHidePrices != 3 && !$this->getIsRfq()){
            return '';
        }
        return parent::getChildHtml($alias, $useCache);
    }
}
