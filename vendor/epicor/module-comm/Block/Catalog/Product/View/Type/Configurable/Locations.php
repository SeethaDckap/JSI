<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Catalog\Product\View\Type\Configurable;

use Epicor\Comm\Model\Product;
/**
 * Locations view block
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Locations extends \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable
{

    /**
     * @var \Magento\Msrp\Helper\Data
     */
    protected $catalogHelper;
    
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commhelper;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\ConfigurableProduct\Helper\Data $helper,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\ConfigurableProduct\Model\ConfigurableAttributeData $configurableAttributeData,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Msrp\Helper\Data   $catalogHelper,
        \Epicor\Comm\Helper\Data $commhelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        array $data = []
    ) {
        $this->catalogHelper = $catalogHelper;
        $this->request = $request;
        $this->checkoutCart = $checkoutCart;
        $this->storeManager = $context->getStoreManager();
        $this->commhelper = $commhelper;
        $this->catalogProductFactory = $catalogProductFactory;
        parent::__construct(
            $context,
            $arrayUtils,
            $jsonEncoder,
            $helper,
            $catalogProduct,
            $currentCustomer,
            $priceCurrency,
            $configurableAttributeData,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();
    }

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
        
         return $this->getProductPriceHtml($product, \Magento\Catalog\Pricing\Price\TierPrice::PRICE_CODE);     
    }

    /**
     * Returns product price block html
     *
     * @param \Epicor\Comm\Model\Location\Product $location
     * @param \Epicor\Comm\Model\Product $product
     * @param boolean $displayMinimalPrice
     * @param string $idSuffix
     * @return string
     */
    public function getPriceHtml($location, $product, $displayMinimalPrice = false, $idSuffix = '')
    {
        $type_id = \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE;

        $product->setToLocationPrices($location->getLocationCode());

        $parent = $this->getParentBlock();

        if ($this->catalogHelper->canApplyMsrp($product)) {
            $realPriceHtml = $parent->getProductPriceHtml($product, $type_id);
            $product->setAddToCartUrl($parent->getAddToCartUrl($product));
            $product->setRealPriceHtml($realPriceHtml);
            $type_id = $this->_mapRenderer;
        }
        
       return $this->getProductPriceHtml($product, $type_id);
    }

    public function getLocations($product)
    {
        $locations = $product->getCustomerLocations();

        return $this->filterLocations($locations);
    }

    public function filterLocations($locations)
    {
        $filtered = $locations;

        $cartItem = $this->getCartItem();

        if ($cartItem) {
            foreach ($locations as $x => $location) {
                if ($cartItem->getEccLocationCode() != $location->getLocationCode()) {
                    unset($filtered[$x]);
                }
            }
        }

        return $filtered;
    }

    /**
     * 
     * @return \Magento\Quote\Model\Quote\Item
     */
    protected function getCartItem()
    {
        $controller = $this->request->getControllerName();
        $action = $this->request->getActionName();
        $cartItem = false;

        if ($controller == 'cart' && $action == 'configure') {

            $itemId = $this->getRequest()->getParam('id');

            if ($itemId) {
                $cart = $this->checkoutCart;
                /* @var $cart Mage_Checkout_Model_Cart */

                $cartItem = $cart->getQuote()->getItemById($itemId);
                /* @var $cartItem Mage_Sales_Model_Quote_Item */
            }
        }

        return $cartItem;
    }

    /**
     * Check whether the price can be shown for the specified product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function getCanShowProductPrice($product)
    {
        return true; //$product->getCanShowPrice() !== false;
    }
    
    
    public  function getStoreInfo()
    {
        return $this->storeManager->getStore()->getId();
    }
    
    public function getQuantityValidators($productId)
    {
        $validators = [];
        $product = $this->catalogProductFactory->create()->load($productId);
        $decimalPlaces = $this->commhelper->getDecimalPlaces($product);
        if ($decimalPlaces !== '') {
            $validators['validatedecimalplace'] = $decimalPlaces;
        }
        return $validators;
    }

    /**
     * Can show child UOM when display out of stocks is set to No
     *
     * @param Product $product
     * @return bool
     */
    public function canShowLocation($product)
    {
        $return = true;
        $erpStock = $product->getErpStock();
        if(!$this->commhelper->isShowOutOfStock() && !$product->getIsEccNonStock()){
            $return =  $erpStock > 0 ? true : false;
        }
        return $return;
    }

}
