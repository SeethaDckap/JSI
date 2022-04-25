<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Catalog\Product\View;


/**
 * Locations view block
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Locations extends \Epicor\Comm\Block\Catalog\Product\Locations
{

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $helper;


    
     public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Msrp\Helper\Data $catalogHelper,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Epicor\Comm\Helper\Data $helper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->request = $request;
        $this->catalogHelper = $catalogHelper;
        $this->checkoutCart = $checkoutCart;
        $this->helper = $helper;
        parent::__construct(
            $context,$registry,$catalogHelper, $data
        );
    }
    
    public function _construct()
    {
        parent::_construct();
    }

    public function getCanShowProductPrice($product)
    {
        return $this->getParentBlock()->getCanShowProductPrice($product);
    }

    public function getHidePrices()
    {
        return $this->getParentBlock()->getHidePrices();
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
        $product->setToLocationPrices($location);
        $product->reloadPriceInfo();
        $parentBlock = $this->getParentBlock();

        if (is_null($product)) {
            $product = $parentBlock->getProduct();
        }
        return $parentBlock->getProductPriceHtml($product, \Magento\Catalog\Pricing\Price\TierPrice::PRICE_CODE);     

    }

    public function getAssociatedProducts()
    {
        return $this->getParentBlock()->getAssociatedProducts();
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
    //M1 > M2 Translation End
    
    /**
     * Get Validation Rules for Quantity field
     *
     * @return array
     */
    public function getQuantityValidators()
    {
        $validators = [];
        $_product = $this->getParentBlock()->getProduct();
        $decimalPlaces = $this->helper->getDecimalPlaces($_product);
        if ($decimalPlaces !== "") {
            $validators['validatedecimalplace'] = $decimalPlaces;
        }
        return $validators;
    }
}
