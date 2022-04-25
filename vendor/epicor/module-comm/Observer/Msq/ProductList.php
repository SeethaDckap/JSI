<?php
/**
 * Copyright © 2010-2021 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer\Msq;

use Magento\Catalog\Model\Config;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Helper\Product\ProductList As ProductListHelper;
use Epicor\Comm\Helper\Messaging;
use Epicor\Comm\Helper\Product As ProductHelper;
use Magento\Catalog\Model\Product\ProductList\ToolbarMemorizer;
use Magento\Catalog\Model\Product\ProductList\Toolbar as ToolbarModel;

/**
 * Class ProductList
 * @package Epicor\Comm\Observer\Msq
 */
class ProductList implements ObserverInterface
{
    /**
     * @var Messaging
     */
    private $commMessagingHelper;

    /**
     * @var ProductHelper
     */
    private $productHelper;

    /**
     * @var ProductListHelper
     */
    private $productListHelper;

    /**
     * @var ToolbarMemorizer
     */
    private $toolbarMemorizer;

    /**
     * @var Config
     */
    private $catalogConfig;

    /**
     * @var string|null
     */
    private $currentMode = null;

    /**
     * @var ToolbarModel
     */
    private $toolbarModel;

    /**
     * @var Http
     */
    private $request;

    /**
     * ProductList constructor.
     * @param Messaging $commMessagingHelper
     * @param ProductHelper $productHelper
     * @param ProductListHelper $productListHelper
     * @param ToolbarMemorizer $toolbarMemorizer
     * @param Config $catalogConfig
     * @param ToolbarModel $toolbarModel
     * @param Http $request
     */
    public function __construct(
        Messaging $commMessagingHelper,
        ProductHelper $productHelper,
        ProductListHelper $productListHelper,
        ToolbarMemorizer $toolbarMemorizer,
        Config $catalogConfig,
        ToolbarModel $toolbarModel,
        Http $request
    ) {
        $this->commMessagingHelper = $commMessagingHelper;
        $this->productHelper = $productHelper;
        $this->productListHelper = $productListHelper;
        $this->toolbarMemorizer = $toolbarMemorizer;
        $this->catalogConfig = $catalogConfig;
        $this->toolbarModel = $toolbarModel;
        $this->request = $request;
    }

    /**
     * Sending MSQ for Product List Page
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        $helper = $this->commMessagingHelper;
        /* @var $helper \Epicor\Comm\Helper\Messaging */
        $prodHelper = $this->productHelper;
        /* @var $prodHelper \Epicor\Comm\Helper\Product */
        $isLazyload = $prodHelper->isLazyLoad();
        $collection = $observer->getEvent()->getCollection();
        $this->addToolBar($collection);

        if ($isLazyload) {
            $moduleControllerAction = $this->request->getModuleName() . "_" . $this->request->getControllerName() . "_" . $this->request->getActionName();
            $urlToIgnore = array('catalog_category_view', 'catalogsearch_result_index', 'catalogsearch_advanced_result');
            if (!in_array($moduleControllerAction, $urlToIgnore)) {
                $helper->sendMsq($collection, 'product_list');
            }
        } else {
            $helper->sendMsq($collection, 'product_list');
        }
        return $this;
    }

    /**
     * Set collection to pager
     * @param \Magento\Framework\Data\Collection $collection
     * @return $this
     */
    private function addToolBar($collection)
    {
        $collection->setCurPage($this->getCurrentPage());
        $limit = (int)$this->getLimit();
        if ($limit) {
            $collection->setPageSize($limit);
        }

        $currentOrder = $this->getCurrentOrder();
        if ($currentOrder) {
            $currentDirection = $this->getCurrentDirection();
            if ($currentOrder == 'position') {
                $collection->addAttributeToSort(
                    $currentOrder,
                    $currentDirection
                );
            } else {
                $collection->setOrder($currentOrder, $currentDirection);
            }
        }
        return $this;
    }

    /**
     * Return current page from request
     * @return int
     */
    private function getCurrentPage()
    {
        return $this->toolbarModel->getCurrentPage();
    }

    /**
     * Get specified products limit display per page
     * @return string
     */
    private function getLimit()
    {
        $limits = $this->productListHelper->getAvailableLimit($this->getCurrentMode());
        $defaultLimit = $this->getDefaultPerPageValue();
        if (!$defaultLimit || !isset($limits[$defaultLimit])) {
            $keys = array_keys($limits);
            $defaultLimit = $keys[0];
        }

        $limit = $this->toolbarMemorizer->getLimit();
        if (!$limit || !isset($limits[$limit])) {
            $limit = $defaultLimit;
        }
        return $limit;
    }

    /**
     * Retrieve default per page values
     * @return string (comma separated)
     */
    private function getDefaultPerPageValue()
    {
        $mode = $this->getCurrentMode();
        return $this->productListHelper->getDefaultLimitPerPageValue($mode);
    }

    /**
     * Retrieve current View mode
     * @return string
     */
    private function getCurrentMode()
    {
        if ($this->currentMode === null) {
            $modes = $this->productListHelper->getAvailableViewMode();
            $defaultMode = $this->productListHelper->getDefaultViewMode($modes);
            $mode = $this->toolbarMemorizer->getMode();
            if (!$mode || !isset($modes[$mode])) {
                $mode = $defaultMode;
            }
            $this->currentMode = $mode;
        }
        return $this->currentMode;
    }

    /**
     * Get grid products sort order field
     * @return string
     */
    private function getCurrentOrder()
    {
        $orders = $this->catalogConfig->getAttributeUsedForSortByArray();
        $defaultOrder = $this->productListHelper->getDefaultSortField();

        if (!isset($orders[$defaultOrder])) {
            $keys = array_keys($orders);
            $defaultOrder = $keys[0];
        }

        $order = $this->toolbarMemorizer->getOrder();
        if (!$order || !isset($orders[$order])) {
            $order = $defaultOrder;
        }
        return $order;
    }

    /**
     * Retrieve current direction
     * @return string
     */
    private function getCurrentDirection()
    {
        $directions = ['asc', 'desc'];
        $dir = strtolower($this->toolbarMemorizer->getDirection());
        if (!$dir || !in_array($dir, $directions)) {
            $dir = ProductListHelper::DEFAULT_SORT_DIRECTION;
        }
        return $dir;
    }

}