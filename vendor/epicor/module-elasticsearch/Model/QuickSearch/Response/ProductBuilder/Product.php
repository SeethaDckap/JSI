<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\QuickSearch\Response\ProductBuilder;

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Escaper;
use Magento\Framework\Event\ManagerInterface;
use Epicor\Elasticsearch\Model\QuickSearch\Response\ProductBuilder\Price as PriceRenderer;
use Magento\Search\Helper\Data as SearchHelper;

/**
 * Class Product
 * @package Epicor\Elasticsearch\Model\QuickSearch\Response\ProductBuilder
 */
class Product
{
    /**
     * Catalog layer
     * @var Layer
     */
    private $catalogLayer;

    /**
     * Catalog Layer Resolver
     *
     * @var Resolver
     */
    private $layerResolver;

    /**
     * System event manager
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var PriceRenderer
     */
    private $priceRenderer;

    /**
     * @var int
     */
    private $collectionCount;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var SearchHelper
     */
    private $searchHelper;

    /**
     * Product constructor.
     * @param Resolver $layerResolver
     * @param ManagerInterface $eventManager
     * @param Price $priceRenderer
     */
    public function __construct(
        Resolver $layerResolver,
        ManagerInterface $eventManager,
        PriceRenderer $priceRenderer,
        Escaper $escaper,
        SearchHelper $searchHelper
    )
    {
        $this->eventManager = $eventManager;
        $this->priceRenderer = $priceRenderer;
        $this->layerResolver = $layerResolver;
        $this->escaper = $escaper;
        $this->searchHelper = $searchHelper;
    }

    /**
     * Product Collection
     * @param $searchSuggestionsCount
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollection($searchSuggestionsCount)
    {
        $collection = $this->initializeProductCollection($searchSuggestionsCount);
        if (!$collection->isLoaded()) {
            $collection->load();
        }
        return $collection;
    }
    /**
     * Initializing the product collection
     * @param $searchSuggestionsCount
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function initializeProductCollection($searchSuggestionsCount)
    {
        $layer = $this->getLayer();
        $collection = $layer->getProductCollection();
        $collectionSize = $collection->getSize();
        $this->setSeeAllCount($collectionSize);
        $collection->getSelect()->limit($searchSuggestionsCount);
        if ($this->priceRenderer->showPrice() !== false) {
            $this->eventManager->dispatch(
                'ecc_autosuggest_product_list_collection',
                ['collection' => $collection]
            );
        }
        return $collection;
    }

    /**
     * Get catalog layer model
     * @return Layer
     */
    private function getLayer()
    {
        if ($this->catalogLayer === null) {
            $this->layerResolver->create(Resolver::CATALOG_LAYER_SEARCH);
            $this->catalogLayer = $this->layerResolver->get();
        }
        return $this->catalogLayer;
    }

    /**
     * Setting the Product Collection count
     * @param $count
     */
    private function setSeeAllCount($count)
    {
        $this->collectionCount = $count;
    }

    /**
     * Returns the Product Collection Count
     * @return int
     */
    public function getSeeAllCount()
    {
        return $this->collectionCount;
    }

    /**
     * Get See All Title
     * @param $count
     * @return \Magento\Framework\Phrase
     */
    public function getSeeAllTitle()
    {
        return __("See All(%1)", $this->getSeeAllCount());
    }

    /**
     * Get Search Result Url
     * @param $query
     * @return array|string
     */
    public function getSeeAllUrl($query)
    {
        $url = $this->searchHelper->getResultUrl($query->getQueryText());
        return $this->escaper->escapeUrl($url);
    }
}