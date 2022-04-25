<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Block\Catalog\Pricing\Renderer;

use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\View\Element\Template\Context;
use Epicor\Comm\Helper\Data as CommHelper;

class ConfiguredPriceBox extends \Magento\Catalog\Pricing\Render\ConfiguredPriceBox
{
    /**
     * @var CommHelper
     */
    private $commHelper;

    /**
     * ConfiguredPriceBox constructor.
     * @param CommHelper $commHelper
     * @param Context $context
     * @param SaleableInterface $saleableItem
     * @param PriceInterface $price
     * @param RendererPool $rendererPool
     * @param array $data
     */
    public function __construct(
        CommHelper $commHelper,
        Context $context,
        SaleableInterface $saleableItem,
        PriceInterface $price,
        RendererPool $rendererPool,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $saleableItem,
            $price,
            $rendererPool,
            $data
        );
        $this->commHelper = $commHelper;
    }

    /**
     * @return string
     */
    public function getCacheKey()
    {
        $key = 'display_price';
        if ($this->isPriceDisplayDisabled()) {
            $key = 'price_display_disabled';
        }
        return parent::getCacheKey() . $key;
    }

    /**
     * @return bool
     */
    public function isPriceDisplayDisabled()
    {
        return $this->commHelper->isPriceDisplayDisabled();
    }
}