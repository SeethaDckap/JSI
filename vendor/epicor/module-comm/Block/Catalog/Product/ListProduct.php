<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Block\Catalog\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Pricing\Render;

/**
 * Block for use with product list page
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $locationsHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    protected $_singleLocation;
    protected $_hasLocations;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;
    
    /**
     *
     * @var \Epicor\Comm\Helper\Product 
     */
    protected $_commProductHelper;

    /**
     * @var \Epicor\BranchPickup\Helper\DataFactory
     */
    protected $branchPickupHelperFactory;
    
    /**
     * 
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Epicor\Comm\Helper\Locations $locationsHelper
     * @param \Epicor\Comm\Helper\Product $commProductHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Epicor\Comm\Helper\Locations $locationsHelper,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Epicor\BranchPickup\Helper\DataFactory $branchPickupHelperFactory,
        array $data = []
    ) {
        $this->locationsHelper = $locationsHelper;
        $this->registry = $context->getRegistry();
        $this->_commProductHelper = $commProductHelper;
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->branchPickupHelperFactory = $branchPickupHelperFactory->create();
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
    }

    /**
     * Sets the list mode in the registry
     * 
     * @param string $mode
     */
    public function setListMode($mode) 
    {
        if ($this->registry->registry('list_mode')) {
            $this->registry->unregister('list_mode');
        }
        $this->registry->register('list_mode', $mode);
    }
    
    /**
     * Returns whether to show locations UI
     * 
     * Only shown if:
     * locations enabled, 
     * stock visibility is not all source locations
     * and the product has more than one location
     * 
     * @return boolean
     */
    public function showLocations() {
        
        if (
            $this->locationsHelper->isLocationsEnabled() == false ||
            $this->locationsHelper->getStockVisibilityFlag() == 'all_source_locations' ||
            $this->_hasLocations == false || 
            $this->_singleLocation ||
            $this->branchPickupHelperFactory->getSelectedBranch()
        ) {
            return false;
        }
        
        
        return true;
    }
    
    /**
     * Checks the product and returns a single location for it -
     * if it only has one
     * 
     * @param Epicor\Comm\Model\Product $_product
     * @return mixed
     */
    public function checkLocations($_product)
    {
        $customerLocations = $_product->getCustomerLocations();
        $this->_hasLocations = (count($customerLocations) > 0) ? true : false;
        $this->_singleLocation = (count($customerLocations) == 1) ? array_pop($customerLocations) : false;
        
        return $this;
    }
    
    /**
     * Returns the current single location
     * 
     * @return mixed
     */
    public function getSingleLocation()
    {
        return $this->_singleLocation;
    }

    /**
     * Sets any custom product prices
     * 
     * @param \Epicor\Comm\Model\Product $_product
     */
    public function setProductPrices($_product)
    {
        $this->checkLocations($_product);
        if ($this->_singleLocation) {
            $_product->setToLocationPrices($this->_singleLocation);
        }

        $this->setCurrentProduct($_product);
        return $this;
    }
    
    /**
     * Sets the current product context, resets some internal variables
     * 
     * @param \Epicor\Comm\Model\Product $_product
     * 
     * @return $this
     */
    public function setCurrentProduct($_product) {
        
        if ($this->registry->registry('current_product')) {
            $this->registry->unregister('current_product');
        }
        $this->registry->register('current_product', $_product);

        return $this;
    }
    
    /**
     * Sets the current product context, resets some internal variables
     * 
     * @return \Epicor\Comm\Model\Product $_product
     */
    public function getCurrentProduct() {
        
        return $this->registry->registry('current_product');
    }

    public function showPriceOnConfiguration()
    {
        $product = $this->getCurrentProduct();
        return $product->getPrice() == 0 && $product->getEccConfigurator();
    }

    /**
     * Returns whether the add to cart button can be shown
     * 
     * @return boolean
     */
    public function showAddToCart()
    {
        $showAddToCart = $this->locationsHelper->isFunctionalityDisabledForCustomer('cart') ? false : true;
        return $showAddToCart;
    }
    
    public function getCurrentUrl(){
        return $this->_urlBuilder->getCurrentUrl();
    }
    
    public function isLazyLoad(){
       return $this->_commProductHelper->isLazyLoad("list");
    }
    
    public function getLoaderImageUrl(){
       $loaderImagepath = $this->_scopeConfig->getValue('catalog/lazy_load/image_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);      
       if($loaderImagepath) {
           $store = $this->_storeManager->getStore();
           return $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . "lazyloader/$loaderImagepath";
       }
       return $this->getViewFileUrl("images/loader-2.gif");  
    }      

    /**
     * Retrieve current view mode
     * override core function for M2.1.7
     *
     * @return string
     */
    public function getMode()
    {
        if ($this->getChildBlock('toolbar')) {
            return $this->getChildBlock('toolbar')->getCurrentMode();
        }

        return $this->getDefaultListingMode();
    }
    
    /**
     * Get listing mode for products if toolbar is removed from layout.
     * Use the general configuration for product list mode from config path catalog/frontend/list_mode as default value
     * or mode data from block declaration from layout.
     *
     * @return string
     */
    private function getDefaultListingMode()
    {
        // default Toolbar when the toolbar layout is not used
        $defaultToolbar = $this->getToolbarBlock();
        $availableModes = $defaultToolbar->getModes();

        // layout config mode
        $mode = $this->getData('mode');

        if (!$mode || !isset($availableModes[$mode])) {
            // default config mode
            $mode = $defaultToolbar->getCurrentMode();
        }

        return $mode;
    }

    /**
     * Get product price.
     *
     * @param Product $product
     * @return string
     */
    public function getProductPrice(Product $product)
    {
        $priceRender = $this->getPriceRender();

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                FinalPrice::PRICE_CODE,
                $product,
                [
                    'include_container' => true,
                    'display_minimal_price' => true,
                    'zone' => Render::ZONE_ITEM_LIST,
                    'list_category_page' => true,
                    'msq_collection' => $this->getData('msq_collection')
                ]
            );
        }

        return $price;
    }


    /**
     * Is locations enabled?
     *
     * @return boolean
     */
    public function locationsEnabled() {
        return $this->locationsHelper->isLocationsEnabled();

    }


}