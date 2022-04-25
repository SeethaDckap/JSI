<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Model\ResourceModel\Erpaccount;


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Collection
 *
 * @author Paul.Ketelle
 */
class Collection extends \Epicor\Database\Model\ResourceModel\Salesrep\Erp\Account\Collection
{

    protected function _construct()
    {
        // define which resource model to use
        $this->_init('Epicor\SalesRep\Model\Erpaccount', 'Epicor\SalesRep\Model\ResourceModel\Erpaccount');
    }

    public function toOptionArray()
    {
        $this->addOrder('name', 'ASC');
        return $this->_toOptionArray('id');
    }

    public function getErpAccountsBySalesRepAccount($sales_rep_account_id)
    {
        $this->addFieldToFilter('sales_rep_account_id', $sales_rep_account_id);
        return $this;
    }

    public function getSalesRepAccountsByErpAccount($erp_account_id)
    {
        $this->addFieldToFilter('erp_account_id', $erp_account_id);
        return $this;
    }

}
