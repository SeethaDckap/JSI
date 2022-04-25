<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\QuickSearch\Response\ProductBuilder;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\Render;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Price
 * @package Epicor\Elasticsearch\Model\QuickSearch\Response\ProductBuilder
 */
class Price
{
    /**
     * System Config to show/hide price
     */
    const XML_PATH_DISPLAY_PRICE = 'catalog/search/ecc_product_displayprice';

    /**
     * @var Render
     */
    private $priceRenderer = null;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Price constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param LayoutInterface $layout
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LayoutInterface $layout
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->layout = $layout;
    }

    /**
     * Renders the product price
     * @param Product $product
     * @param $priceCode
     * @return mixed|null
     */
    public function renderProductPrice(Product $product, $priceCode)
    {
        $price = '';
        if ($this->showPrice() !== false) {
            $priceRender = $this->getPriceRenderer();
            $price = $product->getData($priceCode);
            if ($priceRender) {
                $price = $priceRender->render(
                    $priceCode,
                    $product,
                    [
                        'include_container' => false,
                        'display_minimal_price' => true,
                        'zone' => Render::ZONE_ITEM_LIST,
                        'list_category_page' => true,
                    ]
                );
            }
        }
        return $price;
    }

    /**
     * Retrieve Price Renderer Block
     * @return bool|BlockInterface
     */
    private function getPriceRenderer()
    {
        if ($this->priceRenderer === null) {
            /** @var LayoutInterface $layout */
            $this->layout->getUpdate()->addHandle('default');
            $priceRender = $this->layout->getBlock('product.price.render.default');
            if (!$priceRender) {
                $priceRender = $this->layout->createBlock(
                    'Magento\Framework\Pricing\Render',
                    'product.price.render.default',
                    ['data' => ['price_render_handle' => 'catalog_product_prices']]
                );
            }
            $this->priceRenderer = $priceRender;
        }

        return $this->priceRenderer;
    }

    /**
     * Check if Price can be displayed
     * @return bool
     */
    public function showPrice()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DISPLAY_PRICE,
            ScopeInterface::SCOPE_STORE
        );
    }
}