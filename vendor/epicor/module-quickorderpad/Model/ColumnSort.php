<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\QuickOrderPad\Model;

use Epicor\Comm\Helper\Data as CommHelper;
use Epicor\Comm\Helper\Locations as LocationsHelper;
use Epicor\Lists\Helper\Frontend\Quickorderpad as QuickOrderPadHelper;
use Epicor\Lists\Model\ListModel;
use Epicor\QuickOrderPad\Logger\Logger;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\UrlInterface as Url;
use Epicor\QuickOrderPad\Model\Source\PositionSort;
use Magento\Framework\Data\Collection\AbstractDb as CollectionAbstractDb;

class ColumnSort
{
    const RESULTS_DEFAULT_ORDER_CONFIG = 'Epicor_Comm/quickorderpad/default_sort_order';
    const PRODUCT_COLUMN_DEFAULT_SORT_BY = 'sku';

    /**
     * @var Url
     */
    private $url;
    /**
     * @var HttpRequest
     */
    private $httpRequest;
    /**
     * @var LocationsHelper
     */
    private $locationsHelper;
    /**
     * @var CommHelper
     */
    private $commHelper;
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var QuickOrderPadHelper
     */
    private $quickOrderPadHelper;
    /**
     * @var PositionSort
     */
    private $positionSort;

    public function __construct(
        Logger $logger,
        Url $url,
        HttpRequest $httpRequest,
        LocationsHelper $locationsHelper,
        CommHelper $commHelper,
        ScopeConfigInterface $scopeConfig,
        QuickOrderPadHelper $quickOrderPadHelper = null,
        PositionSort $positionSort = null
    ){
        $this->url = $url;
        $this->httpRequest = $httpRequest;
        $this->locationsHelper = $locationsHelper;
        $this->commHelper = $commHelper;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->quickOrderPadHelper = $quickOrderPadHelper;
        $this->positionSort = $positionSort;
    }

    /**
     * @param ProductCollection $collection
     * @return bool
     */
    public function setInitialDefaultOrder(ProductCollection $collection)
    {
        $list = $this->getList();
        if (!$list instanceof ListModel) {
            return false;
        }

        try {
            $dirParam = $this->getSortDirectionParam();
            $sortParam = $this->getSortByParam();
            $pageSize = $this->getGridPageSize();
            $collection->setPageSize($pageSize);
            $collection->setCurPage($this->getCurrentPage());
            $collection->getSelect()->joinLeft(
                ['lp' => $collection->getTable('ecc_list_product')],
                'e.sku = lp.sku AND lp.list_id = "' . $list->getId() . '"',
                ['qty', 'location_code', 'list_position']
            );
            if ($this->isInitialSortOrder($collection)) {
                $this->setPositionOrder($collection);

            }
            if ($this->isUrlSortOrder($collection)) {
                $collection->setOrder($sortParam, $dirParam);
            }
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * @param $collection
     */
    public function setPositionOrder($collection)
    {
        if (!$collection instanceof CollectionAbstractDb) {
            return;
        }
        $sort = $this->httpRequest->getParam('sort');
        if ($sort && $sort !== 'list_position') {
            return;
        }
        $dir = $this->httpRequest->getParam('dir') ?? '';

        if ($dir && $sort && $sort === 'list_position') {
            $dir = $dir === 'asc' ? 'desc' : 'asc';
            $collection->getSelect()->order(new \Zend_Db_Expr("-lp.list_position $dir"));
            return;
        }
        if ($dir) {
            $collection->getSelect()->order('e.sku ' . $dir);
        } else {
            if ($this->getQuickOrderPadConfigOrder() === 0) {
                $collection->getSelect()->order(new \Zend_Db_Expr("-lp.list_position desc"));
            }

            $order = $this->getDefaultOrder();
            $collection->getSelect()->order('e.sku ' . $order);
        }
    }

    /**
     * @param $type
     * @return string
     */
    public function getSortByUrl($type): string
    {
        if ($this->getSortByParam() === $type && $this->getSortDirectionParam()) {
            return $this->url->getUrl('*/*/*', [
                    '_query' => ['sort_by' => $this->getSortByParam(), 'dir' => $this->getUrlDirection()],
                    '_current' => true,
                    '_escape' => true,
                    '_use_rewrite' => true
                ]
            );
        }

        return $this->getDefaultSortUrl($type);
    }

    /**
     * @return string
     */
    public function getProductDefaultSortByUrl(): string
    {
        return $this->url->getUrl('*/*/*', [
                '_query' => $this->getDefaultSortByParams(),
                '_current' => $this->isQuerySearchParamSet() ? true : false,
                '_escape' => true,
                '_use_rewrite' => true
            ]
        );
    }

    /**
     * @return array
     */
    public function getDefaultSortByParams()
    {
        return ['sort_by' => 'sku', 'dir' => $this->getDefaultOrder()];
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->url->getBaseUrl();
    }

    /**
     * @return mixed|string
     */
    public function getDefaultOrder()
    {
        $order = $this->getQuickOrderPadConfigOrder();
        switch ($order) {
            case 1:
                return 'asc';
                break;
            case 2:
                return 'desc';
                break;
            default:
                return 'asc';
        }
    }

    /**
     * @return bool
     */
    public function isQuickOrderResult()
    {
        return $this->positionSort->isQuickOrderPadResults();
    }

    /**
     * @return bool
     */
    public function isLocationsEnabled()
    {
        return $this->locationsHelper->isLocationsEnabled();
    }

    /**
     * @param $type
     * @param $isListSelected
     * @param bool $default
     * @return string
     */
    public function getDirectionSelector($type, $isListSelected, $default = false): string
    {
        $sortDir = $this->getSortDirectionParam();

        if (!$sortDir && $isListSelected && $this->getQuickOrderPadConfigOrder() === 0) {
            return '';
        }

        if ($sortDir && $this->getSortByParam() === $type) {
            return $sortDir === 'asc' ? '_ascend' : '_descend';
        }
        if ($default && !$sortDir ) {
            switch ($this->getDefaultOrder()) {
                case 'desc':
                    return '_descend';
                    break;
                case 'asc':
                    return '_ascend';
                    break;
                default:
                    return '_ascend';
            }
        }

        return 'not_sort';
    }

    /**
     * @return int
     */
    public function getQuickOrderPadConfigOrder()
    {
        return (int) $this->scopeConfig->getValue(self::RESULTS_DEFAULT_ORDER_CONFIG);
    }

    /**
     * @return bool
     */
    public function isListSelected()
    {
        return $this->quickOrderPadHelper->getSessionList() ? true : false;
    }

    /**
     * @return bool
     */
    public function isPositionListOrder()
    {
        return $this->getQuickOrderPadConfigOrder() === 0;
    }

    /**
     * @return mixed
     */
    public function getSortDirectionParam()
    {
        return $this->httpRequest->getParam('dir');
    }

    /**
     * @return ListModel
     */
    private function getList()
    {
        return $this->quickOrderPadHelper->getSessionList();
    }

    /**
     * @return bool
     */
    private function isQuerySearchParamSet()
    {
        return $this->httpRequest->getParam('q') ? true : false;
    }

    /**
     * @param $type
     * @return string
     */
    private function getDefaultSortUrl($type)
    {
        $defaults = $this->getDefaultUrlParamValues();
        return $this->url->getUrl('*/*/*', [
            '_query' => ['sort_by' => $type, 'dir' => $defaults[$type] ?? ''],
            '_current' => true,
            '_escape' => true,
            '_use_rewrite' => true
        ]);
    }

    /**
     * @return array
     */
    private function getDefaultUrlParamValues()
    {
        if($this->getQuickOrderPadConfigOrder() === 0
            && !$this->httpRequest->getParam('sort_by')
            && $this->isListSelected()){
            return [
                'name' => 'asc',
                'price' => 'asc',
                'stock' => 'asc',
                'uom' => 'asc',
                'sku' => 'asc'
            ];
        }
        if (!$this->httpRequest->getParam('sort_by')) {
            return [
                'name' => 'desc',
                'price' => 'desc',
                'stock' => 'desc',
                'uom' => 'desc',
                'sku' => $this->getDefaultUrlDirection()
            ];
        }
        return [
            'name' => 'asc',
            'price' => 'asc',
            'stock' => 'asc',
            'uom' => 'asc',
            'sku' => 'asc'
        ];
    }

    /**
     * @return string
     */
    private function getUrlDirection()
    {
        $dir = $this->getSortDirectionParam();
        switch ($dir) {
            case 'asc':
                return 'desc';
                break;
            case 'desc':
                return 'asc';
                break;
            default:
                return 'non';
        }
    }

    /**
     * @return string
     */
    private function getDefaultUrlDirection()
    {
        switch ($this->getDefaultOrder()) {
            case 'asc':
                return 'desc';
                break;
            case 'desc':
                return 'asc';
                break;
            default:
                echo 'desc';
        }
    }

    /**
     * @return mixed
     */
    private function getSortByParam()
    {
        return $this->httpRequest->getParam('sort_by');
    }

    /**
     * @return int|mixed
     */
    private function getCurrentPage()
    {
        return $this->httpRequest->getParam('p') ?? 1;
    }

    /**
     * @return mixed
     */
    private function getGridPageSize()
    {
        $pageSize = $this->httpRequest->getParam('product_list_limit');
        if (!$pageSize) {
            $pageSize = $this->getDefaultPageSize();
        }

        if (!$pageSize) {
            throw new \RuntimeException('No grid page size set, check configuration');
        }

        return $pageSize;
    }

    /**
     * @return mixed
     */
    private function getDefaultPageSize()
    {
        return $this->commHelper->getScopeConfig()->getValue('catalog/frontend/grid_per_page');
    }

    /**
     * @param ProductCollection $collection
     * @return bool
     */
    private function isInitialSortOrder(ProductCollection $collection)
    {
        return !$collection->isLoaded() && $this->isQuickOrderResult();
    }

    /**
     * @param ProductCollection $collection
     * @return bool
     */
    private function isUrlSortOrder(ProductCollection $collection)
    {
        return !$collection->isLoaded() && $this->isQuickOrderResult() && $this->isSortRequestedInUrl();
    }

    /**
     * @return bool
     */
    private function isSortRequestedInUrl()
    {
        return $this->httpRequest->getParam('dir') && $this->httpRequest->getParam('sort_by');
    }
}