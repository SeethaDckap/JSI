<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Model\ResourceModel\Account;


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
class Collection extends \Epicor\Database\Model\ResourceModel\Salesrep\Account\Collection
{

    protected function _construct()
    {
        // define which resource model to use
        $this->_init('Epicor\SalesRep\Model\Account', 'Epicor\SalesRep\Model\ResourceModel\Account');
    }

    public function toOptionArray()
    {
        $this->addOrder('name', 'ASC');
        return $this->_toOptionArray('id');
    }

}
