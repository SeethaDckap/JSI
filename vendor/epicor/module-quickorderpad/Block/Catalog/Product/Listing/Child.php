<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\QuickOrderPad\Block\Catalog\Product\Listing;


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use \Magento\Catalog\Model\Product;

/**
 * Description of Child
 *
 * @author Paul.Ketelle
 */
class Child extends \Magento\Framework\View\Element\Template
{

    protected $_processedInstock = false;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;


    /**
     * @var \Epicor\BranchPickup\Helper\Data
     */
    protected $branchPickupHelper;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $locationsHelper;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $checkoutCartHelper;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    protected $imageBuilder;
    
    /*
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    private $columnSort;
    private $eavConfig;
    /**
     * @var \Epicor\Lists\Helper\Frontend\Quickorderpad
     */
    private $quickOrderPadHelper;

    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Epicor\QuickOrderPad\Model\ColumnSort $columnSort,
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Locations $locationsHelper,
        \Magento\Checkout\Helper\Cart $checkoutCartHelper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Customer\Model\Session $customerSession,
        array $data = [],
        \Epicor\Lists\Helper\Frontend\Quickorderpad $quickOrderPadHelper = null
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->commProductHelper = $commProductHelper;
        $this->branchPickupHelper = $branchPickupHelper;
        $this->registry = $registry;
        $this->locationsHelper = $locationsHelper;
        $this->checkoutCartHelper = $checkoutCartHelper;
        $this->pricingHelper = $pricingHelper;
        $this->formKey = $formKey;
        $this->imageBuilder = $imageBuilder;
        $this->customerSession = $customerSession;
        parent::__construct(
            $context,
            $data
        );
        $this->columnSort = $columnSort;
        $this->eavConfig = $eavConfig;
        $this->quickOrderPadHelper = $quickOrderPadHelper;
    }


    /**
     * Retrieve url for direct adding product to cart
     *
     * @param Product $product
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

    /**
     * @param $type
     * @param bool $default
     * @return string
     */
    public function getDirectionSelector($type, $default = false): string
    {
        return $this->columnSort->getDirectionSelector($type, $this->isListSelected(), $default );
    }

    /**
     * @return bool
     */
    public function isClickToSortCondition(): bool
    {
        return $this->columnSort->getQuickOrderPadConfigOrder() === 0 && !$this->columnSort->getSortDirectionParam();
    }

    public function isListSelected()
    {
        return $this->columnSort->isListSelected();
    }

    /**
     * @return bool
     */
    public function isPositionListOrder(): bool
    {
        return $this->columnSort->isPositionListOrder();
    }

    public function getDefaultSortOrderUrl()
    {
       return $this->columnSort->getProductDefaultSortByUrl();

    }

    public function getSortByUrl($type): string
    {
        return $this->columnSort->getSortByUrl($type);
    }

    public function isQuickOrderResult(): bool
    {
        return $this->columnSort->isQuickOrderResult();
    }

    public function isUsedForSortBySetOnSku()
    {
        $skuAttribute = $this->eavConfig->getAttribute(Product::ENTITY, 'sku');
        return  $skuAttribute->getData('used_for_sort_by');
    }

    public function getProductColumnDefaultOrder(): string
    {
        return $this->columnSort::PRODUCT_COLUMN_DEFAULT_SORT_BY;
    }

    public function isLocationsEnabled()
    {
        return $this->columnSort->isLocationsEnabled();
    }

    public function getReturnUrl()
    {
        return $this->getUrl('*/*/*', array('_use_rewrite' => true, '_current' => true));
    }

    /**
     * Get Locations Helper
     * @return \Epicor\Comm\Helper\Locations
     */
    public function getHelper($type = null)
    {
        return $this->locationsHelper;
    }

    /**
     * Gets an array of UOM products from a UOM product
     *
     * @param Product $product
     *
     * @return array
     */
    public function getUOMProducts($product)
    {
        $result = $product->getTypeInstance(true)
            ->getAssociatedProducts($product);

        $storeId = $product->getStoreId();
        foreach ($result as $item) {
            $item->setStoreId($storeId);
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

        return $helper->productHasCustomOptions($product, $required);
    }

    public function getQtyFieldName($product, $child, $productLocation)
    {
        $qtyFieldName = 'qty';
        if ($product->getTypeId() == 'grouped') {
            if ($this->getHelper()->isLocationsEnabled() && !$this->getForceHideLocations()) {
                $locationCode = $product->getRequiredLocation();
                $branchHelper = $this->branchPickupHelper;
                if ($branchHelper->isBranchPickupAvailable() && $branchHelper->getSelectedBranch()) {
                    $locationCode = $branchHelper->getSelectedBranch();
                }
                $qtyFieldName = 'super_group_locations[' . $locationCode . '][' . $child->getId() . ']';
            } else {
                $qtyFieldName = 'super_group[' . $child->getId() . ']';
            }
        }

        return $qtyFieldName;
    }

    public function addHiddenLocationCode($product)
    {
        return $this->getHelper()->isLocationsEnabled() && $product->getTypeId() != 'grouped';
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

    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl();
    }

    /**
     * @return \Magento\Framework\Pricing\Helper\Data
     */
    public function getPricingHelper()
    {
        return $this->pricingHelper;
    }

    /**
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
    //M1 > M2 Translation End


    /**
     * Retrieve product image
     *
     * @param Product $product
     * @param string $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }

    public function getForceHideLocations(){
        $curUrl = $this->_urlBuilder->getCurrentUrl();
        if (strpos($curUrl,'rfq') !== false || strpos($curUrl,'dealerconnect/quotes') !== false || strpos($curUrl,'dealerconnect/inventory') !== false) {
            return 1;
        }else{
            return 0;
        }
    }

    public function getHideConfigurator() {
        $curUrl = $this->_urlBuilder->getCurrentUrl();
        if (strpos($curUrl, 'rfq') !== false
            || strpos($curUrl, 'dealerconnect/quotes') !== false
        ) {
            return 1;
        } else {
            return 0;
        }
    }
    
    /**
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }
    
    /**
     * Adds Decimal Validation
     *
     * @return array
     */
    public function getQuantityValidators()
    {
        $validators = [];
        $_product = $this->registry('current_product');
        $loopProduct = $_product;
        if ($_product->getTypeId() == 'grouped') {
            $loopProduct = $this->registry('current_loop_product') ? : $_product;
        }
        $decimalPlaces = $this->commProductHelper->getDecimalPlaces($loopProduct);
        if ($decimalPlaces !== '') {
            $validators['validatedecimalplace'] = $decimalPlaces;
        }
        return $validators;
    }

    public function getIsRfq()
    {
        $curUrl = $this->_urlBuilder->getCurrentUrl();
        if (strpos($curUrl,'rfq') !== false || strpos($curUrl,'dealerconnect/quotes') !== false || strpos($curUrl,'dealerconnect/inventory') !== false) {
            return 1;
        }else{
            return 0;
        }
    }

    /**
     * Get Configurable product price
     *
     * @param product $product
     * @param float $price
     *
     * @return float
     */
    public function getPriceForConfigurable($product, $price)
    {
        if ($product->getTypeId() === 'configurable') {
            $pricingSku = $product->getEccPricingSku();
            $specialPrice = $product->getSpecialPrice();
            $price = ($specialPrice && ($specialPrice < $price)) ? $specialPrice : $price;
            if ($pricingSku) {
                $collection = $this->getChildrenCollection();
                $collection->addAttributeToFilter('sku', $pricingSku);
                $items = $collection->getItems();
                foreach ($items as $item) {
                    if ($item->getSku() == $pricingSku) {
                        $price = $item->getSpecialPrice();
                        if (!$price) {
                            $price = $item->getFinalPrice();
                        }
                    }
                }
            }
        }
        return $price;
    }
}
