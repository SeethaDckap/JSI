<?php

namespace Cloras\Base\Model\ResourceModel\Orders;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(
            \Cloras\Base\Model\Orders::class,
            \Cloras\Base\Model\ResourceModel\Orders::class
        );
    }

    public function updateStatusRecords($condition, $columnData)
    {
        return $this->getConnection()->update(
            $this->getTable('cloras_orders_index'),
            $columnData,
            $where = $condition
        );
    }

    public function updateSalesOrderId($erpOrderId, $orderId)
    {
        if (!empty($orderId) && !empty($erpOrderId)) {
            $columnData = ['ext_order_id' => $erpOrderId ];
            $this->updataTableData('sales_order', $orderId, $columnData);
            $this->updataTableData('sales_order_grid', $orderId, $columnData);
        }
    }

    private function updataTableData($tableName, $orderId, $columnData)
    {

        return $this->getConnection()->update(
            $this->getTable($tableName),
            $columnData,
            ['entity_id = ?' => $orderId]
        );
        // $sql = 'UPDATE ' . $this->getConnection()->getTableName($tableName) .
        //     " SET ext_order_id = $erpOrderId where entity_id = $orderId";
        // $this->getConnection()->query($sql);
    }

    public function deleteOrderIndex($orderId)
    {
        if (!empty($orderId)) {
            $this->getConnection()->delete(
                $this->getTable('cloras_orders_index'),
                ['order_id = ?' => $orderId]
            );
            return;
        }
    }

    public function getOrdersCollection($requestParams)
    {
        
        $connection = $this->getConnection();
        $select     = $connection->select()->from(
            ['coi' => $this->getTable('cloras_orders_index')]
        )->joinLeft(
            ['so' => $this->getTable('sales_order')],
            'coi.order_id = so.entity_id',
            'so.entity_id'
        )->columns(['so.ext_order_id']);

        if (array_key_exists('page', $requestParams) && array_key_exists('limit', $requestParams)) {
            $select->limitPage($requestParams['page'], $requestParams['limit']);
        }

        $select->order('coi.updated_at DESC');
        
        $data = $connection->fetchAll($select);
        return $data;
    }
}//end class
