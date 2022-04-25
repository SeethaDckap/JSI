<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\QuickSearch\Response;

use Epicor\Elasticsearch\Api\QuickSearchResponseBuilderInterface;
use Epicor\Elasticsearch\Helper\Autosuggest as SearchHelperData;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Search\Model\QueryInterface;
use Magento\Search\Model\QueryFactoryInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Catalog\Helper\Image;
use Epicor\Elasticsearch\Model\QuickSearch\Response\ProductBuilder\Price as PriceRenderer;
use Epicor\Elasticsearch\Model\QuickSearch\Response\ProductBuilder\Review as ReviewRenderer;
use Epicor\Elasticsearch\Model\QuickSearch\Response\ProductBuilder\Product as ProductRenderer;

/**
 * Implementation class that adds Product Section to Quick Search
 */
class ProductBuilder extends AbstractBuilder implements QuickSearchResponseBuilderInterface
{
    /**
     * Image Id for Autosuggest
     */
    const AUTOCOMPLETE_IMAGE_ID = 'product_small_image';

    /**
     * @var Image
     */
    private $imageHelper;

    /**
     * @var PriceRenderer
     */
    private $priceRenderer;

    /**
     * @var ReviewRenderer
     */
    private $reviewRenderer;

    /**
     * @var ProductRenderer
     */
    private $productRenderer;


    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Config $config,
        QueryFactoryInterface $queryFactory,
        SearchIndexNameResolver $searchIndexNameResolver,
        StoreManager $storeManager,
        ConnectionManager $connectionManager,
        SearchHelperData $searchHelperData,
        Image $imageHelper,
        ReviewRenderer $reviewRenderer,
        PriceRenderer $priceRenderer,
        ProductRenderer $productRenderer
    )
    {
        $this->imageHelper = $imageHelper;
        $this->reviewRenderer = $reviewRenderer;
        $this->priceRenderer = $priceRenderer;
        $this->productRenderer = $productRenderer;
        parent::__construct(
            $scopeConfig,
            $config,
            $queryFactory,
            $searchIndexNameResolver,
            $storeManager,
            $connectionManager,
            $searchHelperData
        );
    }

    /**
     * @inheritdoc
     */
    public function buildQuickSearchResponse()
    {
        return $this->getItems($this->query);
    }

    /**
     * @param QueryInterface $query
     * @return \Magento\Search\Model\QueryResult[]
     */
    private function getItems(QueryInterface $query)
    {
        $productResult = [];
        if ($this->isAutoSuggestionAllowed()) {
            $searchSuggestionsCount = $this->getSearchSuggestionsCount();
            $products = $this->productRenderer->getProductCollection($searchSuggestionsCount);
            foreach ($products as $product) {
                $productResult[] = [
                    'description' => $this->getDescription($product),
                    'productCode' => $this->getProductCode($product),
                    'productimage' => $this->getImageUrl($product),
                    'producturl' => $product->getProductUrl(),
                    'price' => $this->priceRenderer->renderProductPrice($product, FinalPrice::PRICE_CODE),
                    'rating' => $this->reviewRenderer->getReviewSummaryHtml($product)
                ];
            }
            $productResult = ["products" => $productResult];
            $seeAllCount = $this->productRenderer->getSeeAllCount();
            if ($seeAllCount > 0) {
                $productResult["seeall"][] = [
                    'seeallcount' => $this->productRenderer->getSeeAllTitle(),
                    'seeallurl' => $this->productRenderer->getSeeAllUrl($query)
                ];
            }
        }
        return $productResult;
    }

    /**
     * Get the image url of the product
     * @param $product
     * @return string
     */
    private function getImageUrl($product)
    {
        $this->imageHelper->init($product, self::AUTOCOMPLETE_IMAGE_ID);
        return $this->imageHelper->getUrl();
    }

    /**
     * Gets the product description
     * @param $product
     * @return string
     */
    private function getDescription($product)
    {
        if ($this->getResultsby() == 2) {
            $description = $product->getShortDescription();
            $length = $this->getDescriptionLength();
        } else {
            $description = $product->getName();
            $length = $this->getProductNameLength();
        }
        if ($description) {
            $description = strip_tags($description);
            $description = trim($description);
            if (strlen($description) > $length) {
                $description = $this->truncateData($description, $length) . "...";
            }
        }
        return $description;
    }

    /**
     * Gets the product name
     * @param $product
     * @return string
     */
    private function getProductCode($product)
    {
        $sku = $product->getSku();
        $sku = strip_tags($sku);
        $sku = trim($sku);
        $length = $this->getNameLength();
        if (strlen($sku) > $length) {
            $sku = $this->truncateData($sku, $length) . "...";
        }

        return __("SKU:") . $sku;
    }
}