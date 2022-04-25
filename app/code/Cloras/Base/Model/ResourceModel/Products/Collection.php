<?php

namespace Cloras\Base\Model\ResourceModel\Products;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Cloras\Base\Model\Products::class,
            \Cloras\Base\Model\ResourceModel\Products::class
        );
    }

    public function updateStatusRecords($condition, $columnData)
    {
        return $this->getConnection()->update(
            $this->getTable('cloras_products_index'),
            $columnData,
            $where = $condition
        );
    }

    public function isUrlExists($pathUrl)
    {

        if (!empty($pathUrl)) {
            $connection = $this->getConnection();
            $tableName = $this->getTable('url_rewrite');
            /**/
            $select = $connection->select()
                ->from(
                    ['o' =>  $tableName]
                )
                ->where('o.request_path=?', $pathUrl)
                ;
     
            /**/
     
            $result = $connection->fetchAll($select);
            
            if (count($result) == 0) {
                return false;
            } else {
                return true;
            }
        }
    }
}//end class
