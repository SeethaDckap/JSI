<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Elasticsearch index resource model
 * get all location code by Product Id
 *
 * Class Index
 * @package Epicor\Elasticsearch\Model\ResourceModel
 */
class Index extends AbstractDb
{

    /**
     * Index constructor.
     * @param Context $contex
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    protected function _construct()
    {
    }

    /**
     * Prepare full location index data for products.
     *
     * @param int $storeId
     * @param null|array $productIds
     * @return array
     * @since 100.1.0
     */
    public function getFullLocationProductIndexData($storeId = null, $productIds = null)
    {
        $connection = $this->getConnection();
        $tableName = $connection->getTableName('ecc_location_product');
        $select = $connection->select()->from(
            $tableName,
            ['id', 'product_id', 'location_code']
        );

        if ($productIds) {
            $select->where('product_id IN (?)', $productIds);
        }

        $result = [];
        foreach ($connection->fetchAll($select) as $row) {
            $result[$row['product_id']][] = $row;
        }

        return $result;
    }
}
