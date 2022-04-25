<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\QuickOrderPad\Model\Source;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection as SearchCollectionClass;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Epicor\QuickOrderPad\Logger\Logger;
use Zend_Db_Select;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Data\Collection\AbstractDb as CollectionAbstractDb;
use Epicor\QuickOrderPad\Model\Source\SortDirections;

class PositionSort
{
    const RESULTS_DEFAULT_ORDER_CONFIG = 'Epicor_Comm/quickorderpad/default_sort_order';
    const QUICK_ORDER_PAD_RESULTS = 'quickorderpad_form_results';
    /**
     * @var HttpRequest
     */
    private $httpRequest;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var CustomerSession
     */
    private $customerSession;

    public function __construct(
        CustomerSession $customerSession,
        HttpRequest $httpRequest,
        ScopeConfigInterface $scopeConfig,
        Logger $logger
    ){
        $this->httpRequest = $httpRequest;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->customerSession = $customerSession;
    }

    /**
     * Based on getting a full set of results from the search engine
     * Before loading the collection
     * This resets the order to allow joining of the ecc_list_product table
     * and ordering by list position, pagination is also set
     *
     * @param $_productCollection
     */
    public function setOrderForSearchQuery($_productCollection)
    {
        try {
            if ($this->isQopCatalogSearch($_productCollection)) {
                $this->clearCollection($_productCollection);
                $this->resetQueryOrders($_productCollection);
                $this->setOrderForListPositionQuery($_productCollection);
                $this->setOrderBySku($_productCollection);
                $this->setPagination($_productCollection);
            }
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * @param $_productCollection
     */
    private function setOrderForListPositionQuery($_productCollection)
    {
        if ($this->isQopPositionOrderListSearch($_productCollection) ) {
            $this->joinListProductTable($_productCollection);
            $this->filterByListId($_productCollection);
            $this->setPositionOrderNullLast($_productCollection);
        }
    }

    /**
     * @param $collection
     * @return bool
     */
    public function isQopPositionOrderListSearch($collection)
    {
        return $this->isQopCatalogSearch($collection) && $this->isListSelectedWithPosition() && !$this->isSortBySku();
    }

    /**
     * @return bool
     */
    public function isQuickOrderPadResults()
    {
        return $this->httpRequest->getFullActionName() === self::QUICK_ORDER_PAD_RESULTS;
    }

    /**
     * @param $collection
     * @return bool
     */
    public function isQopCatalogSearch($collection)
    {
        return $collection instanceof SearchCollectionClass && $this->isSearchQuery()
            && $this->isQuickOrderPadResults();
    }

    /**
     * @return mixed
     */
    private function getListId()
    {
        return $this->customerSession->getData('ecc_quickorderpad_list');
    }

    /**
     * @return bool
     */
    private function isSearchQuery()
    {
        return $this->httpRequest->getParam('q') ? true : false;
    }

    /**
     * @param $_productCollection
     */
    private function setPagination($_productCollection)
    {
        if ($_productCollection instanceof CollectionAbstractDb) {
            $_productCollection->setPageSize(null);
            $_productCollection->getSelect()->limitPage($this->getCurrentPage(), $this->getProductsPerPage());
        }
    }

    /**
     * @param $_productCollection
     */
    private function filterByListId($_productCollection)
    {
        $listId = $this->getListId();
        if ($_productCollection instanceof CollectionAbstractDb && $listId) {
            $_productCollection->getSelect()->where('lp.list_id = ?', $listId);
        }
    }

    /**
     * @param $_productCollection
     */
    private function joinListProductTable($_productCollection)
    {
        if ($_productCollection instanceof CollectionAbstractDb) {
            $listProductTable = $_productCollection->getTable('ecc_list_product');
            $_productCollection->getSelect()->join(['lp' => $listProductTable], 'e.sku = lp.sku ', []);
        }
    }

    /**
     * The order is by position first with positive positions first and then
     * ones set to null, to accomplish this order needs to be set reversed this
     * is done by using the '-' and reverse the direction
     *
     * @param $_productCollection
     */
    private function setPositionOrderNullLast($_productCollection)
    {
        if ($_productCollection instanceof CollectionAbstractDb) {
            $dir = $this->getSortDir() ?? '';
            $nullLastDir = $dir === 'desc' ? 'asc' : 'desc';
            $_productCollection->getSelect()->order(new \Zend_Db_Expr('-lp.list_position '  . $nullLastDir));
        }
    }

    private function setOrderBySku($_productCollection)
    {
        if ($_productCollection instanceof CollectionAbstractDb) {
            $dir = $this->getSortDir() ?? 'asc';
            $_productCollection->getSelect()->order('e.sku '  . $dir);
        }
    }

    /**
     * @return mixed|string
     */
    public function getSortDir()
    {
        $dir = $this->httpRequest->getParam('dir');
        if (!$dir) {
            return $this->getQopConfigDirection();
        }
        return $dir;
    }

    /**
     * @return string
     */
    private function getQopConfigDirection()
    {
        if($this->isConfigOrderSkuAsc()){
            return 'asc';
        }
        if($this->isConfigOrderSkuDesc()){
            return 'desc';
        }
    }

    private function isSortBySku()
    {
        return $this->httpRequest->getParam('sort_by') === 'sku';
    }

    private function resetQueryOrders($_productCollection)
    {
        if ($_productCollection instanceof CollectionAbstractDb) {
            $_productCollection->getSelect()->reset(Zend_Db_Select::ORDER);
        }
    }

    /**
     * This provides a way of setting the _totalRecords property back to null
     * this is necessary as the this value gets set incorrectly as there is a mismatch
     * of list products and results from search engine by setting back to null means it will
     * get set correctly in \Magento\Framework\Data\Collection\AbstractDb::getSize
     *
     * @param $_productCollection
     */
    private function clearCollection($_productCollection)
    {
        if($_productCollection instanceof CollectionAbstractDb){
            $_productCollection->clear();
        }
    }

    /**
     * @return int|mixed
     */
    private function getCurrentPage()
    {
       return $this->httpRequest->getParam('p') ?? 1;
    }

    private function setListPositionAscNullLast($collection)
    {
        if($collection instanceof CollectionAbstractDb){
            $collection->getSelect()->order(new \Zend_Db_Expr("-lp.list_position desc"));
        }
    }

    /**
     * @return bool
     */
    private function isListSelectedWithPosition()
    {
        return $this->isListSelected() && $this->isConfigOrderByPosition();
    }

    public function isListSelected()
    {
        $listId = (int) $this->getListId();
        return $listId > 0;
    }

    /**
     * @return bool
     */
    public function isConfigOrderByPosition()
    {
        return $this->getQuickOrderPadConfigOrder() === SortDirections::QOP_LIST_POSITION_ORDER;
    }

    /**
     * @return bool
     */
    private function isConfigOrderSkuAsc()
    {
        return $this->getQuickOrderPadConfigOrder() === SortDirections::QOP_SORT_ASC;
    }

    /**
     * @return bool
     */
    private function isConfigOrderSkuDesc()
    {
        return $this->getQuickOrderPadConfigOrder() === SortDirections::QOP_SORT_DESC;
    }

    /**
     * @return int
     */
    public function getQuickOrderPadConfigOrder()
    {
        return (int) $this->scopeConfig->getValue(self::RESULTS_DEFAULT_ORDER_CONFIG);
    }

    /**
     * @return mixed
     */
    private function getProductsPerPage()
    {
        if ($urlLimit = $this->httpRequest->getParam('product_list_limit')) {
            return $urlLimit;
        }

        return $this->scopeConfig->getValue('catalog/frontend/grid_per_page', ScopeInterface::SCOPE_STORE);
    }
}