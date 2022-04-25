<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\QuickOrderPad\Model\ResourceModel\Position;

use Epicor\QuickOrderPad\Model\ColumnSort;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class PositionOrder
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param $collection AbstractCollection
     */
    public function setPositionOrderByConfig($collection)
    {
        $sortSetting = (int)$this->scopeConfig->getValue(ColumnSort::RESULTS_DEFAULT_ORDER_CONFIG);
        if ($sortSetting === 0) {
            $collection->getSelect()->order(new \Zend_Db_Expr("-list_position desc"));
            $collection->getSelect()->order("sku asc");
        }
        if ($sortSetting === 1) {
            $collection->getSelect()->order("sku asc");
        }
        if ($sortSetting === 2) {
            $collection->getSelect()->order("sku desc");
        }
    }
}
