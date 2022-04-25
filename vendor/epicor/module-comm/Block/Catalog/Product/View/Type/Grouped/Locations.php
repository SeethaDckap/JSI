<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Catalog\Product\View\Type\Grouped;

use \Epicor\Comm\Model\Location\Product as LocationProduct;
use Epicor\Comm\Model\Product;

/**
 * Locations view block
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Locations extends \Magento\GroupedProduct\Block\Product\View\Type\Grouped
{

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $catalogHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;
    
    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $helper;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Msrp\Helper\Data   $catalogHelper,
        \Epicor\Comm\Helper\Data $helper,
        array $data = []
    ) {
        $this->catalogHelper = $catalogHelper;
        $this->scopeConfig = $context->getScopeConfig();
        $this->checkoutCart = $checkoutCart;
        $this->helper = $helper;
        parent::__construct(
            $context,
            $arrayUtils,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();
    }

    /**
     * Get Locations Helper
     * @return \Epicor\Comm\Helper\Locations
     */
    /*
    public function getHelper()
    { 
        return $this->helper('epicor_comm/locations');
    } */

    /**
     * Returns product tier price block html
     *
     * @param null|\Magento\Catalog\Model\Product $product
     * @param null|\Magento\Catalog\Model\Product $parent
     * @return string
     */
    public function getTierPriceHtml($location, $product = null, $parent = null)
    {
        $product->setToLocationPrices($location->getLocationCode());

        if (is_null($product)) {
            $product = $this->getProduct();
        }
         $parent = $this->getParentBlock();
        
         return $parent->getProductPriceHtml($product, \Magento\Catalog\Pricing\Price\TierPrice::PRICE_CODE);     
        
        /* // below code is of Magento 1 it will not work in M2 //
        return $this->_getPriceBlock($product->getTypeId())
                ->setTemplate($this->getTierPriceTemplate())
                ->setProduct($product)
                ->setInGrouped($product->isGrouped())
                ->setParent($parent)
                ->callParentToHtml();
        */
    }

    /**
     * Returns product price block html
     *
     * @param \Epicor\Comm\Model\Location\Product $location
     * @param Product $product
     * @param boolean $displayMinimalPrice
     * @param string $idSuffix
     * @return string
     */
    public function getPriceHtml($location, $product, $displayMinimalPrice = false, $idSuffix = '')
    { 
        $product->reloadPriceInfo();
        $type_id = \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE;

        $product->setToLocationPrices($location->getLocationCode());

        $parent = $this->getParentBlock();

        if ($this->catalogHelper->canApplyMsrp($product)) {
            $realPriceHtml = $parent->getProductPriceHtml($product, $type_id);
            $product->setAddToCartUrl($parent->getAddToCartUrl($product));
            $product->setRealPriceHtml($realPriceHtml);
            $type_id = $this->_mapRenderer;
        }
        
       return $parent->getProductPriceHtml($product, $type_id);
        
    }

    public function getPrimarySort()
    {
        return $this->scopeConfig->getValue('epicor_comm_locations/frontend/product_details_sort', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getSecondarySort()
    {
        return $this->getPrimarySort() == 'uom' ? 'location' : 'uom';
    }

    /**
     * 
     * @param Product $product
     * @return Array
     */
    public function getPrimaryItems($product)
    {
        $items = array();
        switch ($this->getPrimarySort()) {
            case 'location' :
                foreach ($this->getUOMProducts($product) as $uomProd) {
                    /* @var $uomProd Epicor_Comm_Model_Product */
                    foreach ($uomProd->getCustomerLocations() as $locationCode => $location) {
                        if (!isset($items[$locationCode])) {
                            $items[$locationCode] = $location;
                        }
                    }
                }
                break;
            case 'uom':
                $items = $this->getUOMProducts($product);
                break;
        }

        if (empty($items)) {
            $items[] = $product;
        } else {
            $items = $this->filterItems($items, $this->getPrimarySort());
        }

        return $items;
    }

    /**
     * 
     * @param Product $product
     * @param Product|\Epicor\Comm\Model\Location\Product $primaryProduct
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
                $items = $this->getUOMProducts($product, $primaryProduct->getLocationCode());
                break;
        }

        if (empty($items)) {
            $items[] = $primaryProduct;
        } else {
            $items = $this->filterItems($items, $this->getSecondarySort());
        }
        return $items;
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
                if ($locationCode) {
                    if ($item->getLocation($locationCode) === false) {
                        unset($result[$key]);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Works out if we need to skip the row
     * 
     * @param Product $childProduct
     * @param \Epicor\Comm\Model\Location\Product $location
     * 
     * @return boolean
     */
    public function filterItems($items, $type)
    {
        $filtered = $items;
        $itemId = $this->getRequest()->getParam('itemid');

        if ($itemId) {
            $cart = $this->checkoutCart;
            /* @var $cart Mage_Checkout_Model_Cart */

            $cartItem = $cart->getQuote()->getItemById($itemId);
            /* @var $cartItem Mage_Sales_Model_Quote_Item */

            if ($cartItem) {
                foreach ($items as $x => $item) {
                    if ($type == 'uom' && $cartItem->getProductId() != $item->getId()) {
                        unset($filtered[$x]);
                    }
                    if ($type == 'location' && $cartItem->getEccLocationCode() != $item->getLocationCode()) {
                        unset($filtered[$x]);
                    }
                }
            }
        }

        return $filtered;
    }

    public function allChildrenLocationCodes($product)
    {
        $locationCodes = array();
        foreach ($this->getUOMProducts($product) as $uomProduct) {
            foreach ($uomProduct->getCustomerLocations() as $locationCode => $location) {
                if (!in_array($locationCode, $locationCodes)) {
                    $locationCodes[] = $locationCode;
                }
            }
        }

        return $locationCodes;
    }

    /**
     * Get Validation Rules for Quantity field
     *
     * @return array
     */
    public function getQuantityValidators($childProduct)
    {
        $validators = [];
        $decimalPlaces = $this->helper->getDecimalPlaces($childProduct);
        if ($decimalPlaces !== '') {
            $validators['validatedecimalplace'] = $decimalPlaces;
        }
        return $validators;
    }

    /**
     * Can show child UOM when display out of stocks is set to No
     *
     * @param Product $childProduct
     * @return bool
     */
    public function canShowChildUom($childProduct)
    {
        $return = true;
        $erpStock = $childProduct->getErpStock();
        if(!$this->helper->isShowOutOfStock() && !$childProduct->getIsEccNonStock()){
            $return =  $erpStock > 0 ? true : false;
        }
        return $return;
    }

    /**
     * @param $secondaryProduct
     * @return bool
     */
    public function isValidSecondaryProductType($secondaryProduct): bool
    {
        return $secondaryProduct instanceof LocationProduct || $secondaryProduct instanceof Product;
    }

}
