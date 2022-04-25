<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\QuickSearch\Response\ProductBuilder;

use Magento\Catalog\Block\Product\ReviewRendererInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Review
 * @package Epicor\Elasticsearch\Model\QuickSearch\Response\ProductBuilder
 */
class Review
{
    /**
     * System Config to show/hide reviews
     */
    const XML_PATH_SHOW_REVIEWS = 'catalog/search/ecc_product_showreviews';

    /**
     * @var ReviewRendererInterface
     */
    private $reviewRenderer;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Review constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ReviewRendererInterface $reviewRenderer
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->reviewRenderer = $reviewRenderer;
    }

    /**
     * Gets Rating Html
     * @param Product $product
     * @return string
     * @throws NoSuchEntityException
     */
    public function getReviewSummaryHtml(Product $product)
    {
        $review = '';
        if ($this->showReviews() !== false) {
            $review = $this->reviewRenderer->getReviewsSummaryHtml($product, ReviewRendererInterface::SHORT_VIEW);
        }
        return $review;
    }

    /**
     * Check if Reviews needs to shown or not
     * @return bool
     */
    private function showReviews()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SHOW_REVIEWS,
            ScopeInterface::SCOPE_STORE);
    }
}