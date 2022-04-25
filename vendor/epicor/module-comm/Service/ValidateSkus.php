<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Service;

use Magento\Framework\App\ResourceConnection;

/**
 * Class ValidateSkus
 * @package Epicor\Comm\Service
 */
class ValidateSkus
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * ValidateSkus constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param $skus
     * @return array
     */
    public function isSkuExist($skus)
    {
        $s = '';
        foreach ($skus as $sku) {
            $s = $s . "'" . $sku . "',";
        }
        $s = rtrim($s, ',');
        $tableName = $this->resourceConnection->getTableName('catalog_product_entity');
        $connection = $this->resourceConnection->getConnection();

        $sqlQuery = "SELECT sku FROM {$tableName} WHERE sku IN ({$s})";

        $result = $connection->fetchAll($sqlQuery);
        if (count($result) > 0) {
            $availableSkus = array();
            foreach ($result as $avs) {
                array_push($availableSkus, $avs['sku']);
            }
            return $availableSkus;
        }

        return $result;
    }
}
