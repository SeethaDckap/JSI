<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Model\ResourceModel\Hierarchy;


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
class Collection extends \Epicor\Database\Model\ResourceModel\Salesrep\Hierarchy\Collection
{

    protected function _construct()
    {
        $this->_init('Epicor\SalesRep\Model\Hierarchy', 'Epicor\SalesRep\Model\ResourceModel\Hierarchy');
    }

}
