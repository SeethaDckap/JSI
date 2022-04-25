<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Catalog\Product;


/**
 * Locations 
 * 
 * Displays Locations on the product list/view page
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Locations extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Msrp\Helper\Data
     */
    protected $msrpHelper;


    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Msrp\Helper\Data $msrpHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->msrpHelper = $msrpHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();
    }

    /**
     * Returns the current product
     * 
     * @return \Epicor\Comm\Model\Product
     */
    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * Gets the list mode
     * 
     * @return string
     */
    public function getListMode()
    {
        return $this->registry->registry('list_mode');
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
        if (!$product->getIsLocationPriceApplied()) {
            $product->setToLocationPrices($location);
        }
        $product->reloadPriceInfo();
        
        $parent = $this->getParentBlock();
        /* @var $parent \Magento\Catalog\Block\Product\ListProduct */
        if ($this->msrpHelper->canApplyMsrp($product)) {
            $realPriceHtml = $parent->getProductPriceHtml($product, $type_id);
            $product->setAddToCartUrl($parent->getAddToCartUrl($product));
            $product->setRealPriceHtml($realPriceHtml);
            $type_id = $this->_mapRenderer;
        }
        $product->setShowPriceZero(1);
        return $parent->getProductPriceHtml($product, $type_id);
    }

    public function getAddToCartUrl($product)
    {
        return $this->getParentBlock()->getAddToCartUrl($product);
    }

    public function getReturnUrl()
    {
        return $this->getParentBlock()->getReturnUrl();
    }

}
