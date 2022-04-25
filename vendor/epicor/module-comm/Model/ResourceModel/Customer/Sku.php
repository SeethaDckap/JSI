<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\ResourceModel\Customer;


class Sku extends \Epicor\Database\Model\ResourceModel\Erp\Account\Sku
{
    /**
     * @param $filters
     * @param array $erpIds
     * @param array $condition
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSkuByCustomerSku(
        $filters,
        $erpIds = [],
        $condition = ['isOr' => false, 'isPrefix' => false, 'isSuffix' => false]
    ) {

        $connection = $this->getConnection();
        $sql = $this->getInitQuery($connection);
        if (count($erpIds)) {
            $sql = $sql->where("`main_table`.`customer_group_id` IN(?)", $erpIds);
        }
        $sql = $this->setFilter($sql, $filters, $condition);

        return $connection->fetchAll($sql);
    }

    /**
     * @param \Magento\Framework\DB\Adapter\Pdo\Mysql $connection
     * @return \Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getInitQuery($connection)
    {
        $sql = $connection->select()
            ->from(['main_table' => $this->getMainTable()])
            ->joinInner(array('product' => $connection->getTableName('catalog_product_entity')),
                'product.entity_id = main_table.product_id',
                array('product.sku' => 'product.sku'),
                null
            );

        return $sql;
    }

    /**
     * @param $sql
     * @param array $filters
     * @param $condition
     * @return mixed
     */
    public function setFilter($sql, $filters = [], $condition)
    {
        $isOr = (isset($condition['isOr']) && $condition['isOr']) ? $condition['isOr'] : false;
        $prefix = (isset($condition['isPrefix']) && $condition['isPrefix']) ? '' : "([[:<:]]|^)";
        $suffix = (isset($condition['isSuffix']) && $condition['isSuffix']) ? '' : "([[:>:]]|$)";
        if (is_string($filters)) {
            $sql = $sql->where("`main_table`.`sku` REGEXP ? ", $prefix . "(" . $filters . ")" . $suffix);
        } else {
            if (is_array($filters)) {
                $likeQuery = '';
                foreach ($filters as $filterKey => $filter) {

                    if ($isOr) {
                        if ($filterKey > 0) {
                            $likeQuery = $likeQuery . " OR ";
                        }
                        $likeQuery = $likeQuery . "`main_table`.`sku` REGEXP '" . $prefix . "(" . $filter . ")" . $suffix . "'";
                    } else {
                        $sql = $sql->where("`main_table`.`sku` REGEXP ? ", $prefix . "(" . $filter . ")" . $suffix);
                    }
                }

                if ($isOr) {
                    $sql = $sql->where($likeQuery);
                }
            }
        }

        return $sql;
    }
}
