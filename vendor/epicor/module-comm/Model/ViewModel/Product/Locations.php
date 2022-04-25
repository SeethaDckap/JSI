<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ViewModel\Product;

use Epicor\Comm\Api\ViewModel\Product\LocationsInterface;
use Epicor\Comm\Helper\Product as ProductHelper;
use Epicor\Comm\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Msrp\Helper\Data;

/**
 * Product locations block.
 *
 * @package Epicor\Comm
 */
class Locations implements ArgumentInterface, LocationsInterface
{
    /**
     * @var Product
     */
    private $product;

    /**
     * @var string
     */
    private $mode;

    /**
     * @var ListProduct
     */
    private $listProduct;

    /**
     * @var Data
     */
    private $msrpHelper;

    /**
     * @var int
     */
    private $productId;

    /**
     * @var ProductHelper
     */
    private $commProductHelper;

    /**
     * Locations constructor.
     *
     * @param ListProduct $listProduct
     * @param Data $msrpHelper
     * @param ProductHelper $commProductHelper
     */
    public function __construct(
        ListProduct $listProduct,
        Data $msrpHelper,
        ProductHelper $commProductHelper
    ) {
        $this->listProduct = $listProduct;
        $this->msrpHelper = $msrpHelper;
        $this->commProductHelper = $commProductHelper;
    }

    /**
     * @inheritDoc
     */
    public function getProduct($productId)
    {
        if (empty($this->product)) {
            // get Product collection with MSQ
            $collection = $this->commProductHelper->getProductCollectionByIds([$productId]);
            foreach ($collection as $product) {
                $this->product = $product;
            }
        }
        return $this->product;
    }

    /**
     * @inheritDoc
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getListMode()
    {
        return $this->mode;
    }

    /**
     * @inheritDoc
     */
    public function setListMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPriceHtml($location, Product $product)
    {
        $typeId = FinalPrice::PRICE_CODE;
        if (!$product->getIsLocationPriceApplied()) {
            $product->setToLocationPrices($location);
        }
        $product->reloadPriceInfo();

        if ($this->msrpHelper->canApplyMsrp($product)) {
            $realPriceHtml = $this->listProduct->getProductPriceHtml($product, $typeId);
            $product->setAddToCartUrl($this->listProduct->getAddToCartUrl($product));
            $product->setRealPriceHtml($realPriceHtml);
            $typeId = $this->_mapRenderer;
        }
        $product->setShowPriceZero(1);
        return $this->listProduct->getProductPriceHtml($product, $typeId);
    }
}
