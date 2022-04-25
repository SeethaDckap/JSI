<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Erp\Mapping;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Claimstatus extends \Epicor\Database\Model\ResourceModel\Erp\Mapping\Claimstatus
{
    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllStatus()
    {
        $connection = $this->getConnection();
        $columns = [
            'erp_code',
            'claim_status'
        ];
        $mainTable = [
            'main_table' => $this->getMainTable()
        ];
        $select = $connection->select()->from($mainTable, $columns);
        return $connection->fetchAll($select);
    }
}
