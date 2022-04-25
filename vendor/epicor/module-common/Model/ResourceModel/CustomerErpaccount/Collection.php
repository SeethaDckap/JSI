<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model\ResourceModel\CustomerErpaccount;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

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
class Collection extends AbstractCollection
{

    protected function _construct()
    {
        // define which resource model to use
        $this->_init('Epicor\Common\Model\CustomerErpaccount', 'Epicor\Common\Model\ResourceModel\CustomerErpaccount');
    }

    public function toOptionArray()
    {
        $this->addOrder('name', 'ASC');
        return $this->_toOptionArray('id');
    }

    public function getEntityByCustomer($customerId)
    {
        $this->addFieldToFilter('customer_id', $customerId);
        return $this;
    }

    public function getEntityByErpAccount($erp_account_id)
    {
        $this->addFieldToFilter('erp_account_id', $erp_account_id);
        return $this;
    }

}
