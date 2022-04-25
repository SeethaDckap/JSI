<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Block\Catalog\Product\View;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class EccProductLabel
 */
class EccProductLabel extends \Magento\Framework\View\Element\Template
{
    const DISCONTINUED_MESSAGE = 'This item is discontinued.';

    const NON_STOCK_MESSAGE = 'This is non stock item.';

    /**
     * Registry
     *
     * @var Registry
     */
    private $registry;

    /**
     * ScopeConfig
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;


    /**
     * EccProductLabel constructor
     *
     * @param Context  $context  Context.
     * @param Registry $registry Registry.
     * @param array    $data     Data.
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data=[]
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->registry    = $registry;
        parent::__construct(
            $context,
            $data
        );

    }//end __construct()


    /**
     * IsNonStock
     *
     * @return boolean
     */
    public function isNonStock()
    {
        if ($this->isDisplayNonStock()) {
            $product = $this->getProduct();
            /** @var $product \Epicor\Comm\Model\Product */
            if ($product && $product->getIsEccNonStock()) {
                return true;
            }
        }

        return false;

    }//end isNonStock()


    /**
     * IsEccDiscontinued
     *
     * @return boolean
     */
    public function isEccDiscontinued()
    {
        if ($this->isDisplayEccDiscontinued()) {
            $product = $this->getProduct();
            /** @var $product \Epicor\Comm\Model\Product */
            if ($product && $product->getIsEccDiscontinued() && !$product->getIsEccNonStock()) {
                return true;
            }
        }

        return false;

    }//end isEccDiscontinued()


    /**
     * Gets the current product context
     *
     * @return \Epicor\Comm\Model\Product
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');

    }//end getCurrentProduct()


    /**
     * GetProduct
     *
     * @return \Epicor\Comm\Model\Product
     */
    public function getProduct()
    {
        return $this->registry->registry('product');

    }//end getProduct()


    /**
     * IsDisplayNonStock
     *
     * @return boolean
     */
    private function isDisplayNonStock()
    {
        return $this->scopeConfig->getValue('epicor_product_config/non_stock/show_message');

    }//end isDisplayNonStock()


    /**
     * IsDisplayEccDiscontinued
     *
     * @return boolean
     */
    private function isDisplayEccDiscontinued()
    {
        return $this->scopeConfig->getValue('epicor_product_config/discontinued/show_message');

    }//end isDisplayEccDiscontinued()


    /**
     * GetDiscontinuedMessage
     *
     * @return string
     */
    public function getDiscontinuedMessage()
    {
        return __(self::DISCONTINUED_MESSAGE);

    }//end getDiscontinuedMessage()


    /**
     * GetNonStockMessage
     *
     * @return string
     */
    public function getNonStockMessage()
    {
        return __(self::NON_STOCK_MESSAGE);

    }//end getNonStockMessage()


}
