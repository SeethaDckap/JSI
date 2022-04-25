<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Model;


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Account
 *
 * @author Paul.Ketelle
 */
class Erpaccount extends \Epicor\Common\Model\AbstractModel
{

    function _construct()
    {
        $this->_init('Epicor\SalesRep\Model\ResourceModel\Erpaccount');
    }

}
