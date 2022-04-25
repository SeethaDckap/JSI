<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Catalog\Product\View\Type\Bundle;


/**
 * Locations view block
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Locations extends \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle
{

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $catalogHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;
    
    /**
     * @var \Magento\Msrp\Helper\Data
     */
    protected $msrpHelper;
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commhelper;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Bundle\Model\Product\PriceFactory $productPrice,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Msrp\Helper\Data $msrpHelper,
        \Epicor\Comm\Helper\Data $commhelper,
        array $data = []
    ) {
        $this->catalogHelper = $context->getCatalogHelper();
        $this->request = $request;
        $this->checkoutCart = $checkoutCart;
        $this->msrpHelper = $msrpHelper;
        $this->registry = $context->getRegistry();
        $this->commhelper = $commhelper;
        parent::__construct(
            $context,
            $arrayUtils,
            $catalogProduct,
            $productPrice,
            $jsonEncoder,
            $localeFormat,
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

         return $parent->getProductPriceHtml($product, \Magento\Catalog\Pricing\Price\TierPrice::PRICE_CODE);
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

        $product->setToLocationPrices($location);
        $parent = $this->getParentBlock();
        if ($this->msrpHelper->canApplyMsrp($product)) {
            $realPriceHtml = $parent->getProductPriceHtml($product, $type_id);
            $product->setAddToCartUrl($parent->getAddToCartUrl($product));
            $product->setRealPriceHtml($realPriceHtml);
        }
        return $parent->getProductPriceHtml($product, $type_id);
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
    
    /**
     * Get Validation Rules for Quantity field
     *
     * @return array
     */
    public function getQuantityValidators()
    {
        $validators = [];
        $_product = $this->getProduct();
        $decimalPlaces = $this->commhelper->getDecimalPlaces($_product);
        if ($decimalPlaces !== "") {
            $validators['validatedecimalplace'] = $decimalPlaces;
        }
        return $validators;
    }
}
