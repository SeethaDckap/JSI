<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Erp\Mapping;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Products extends \Epicor\Common\Model\Erp\Mapping\AbstractModel
{

    protected function _construct()
    {
        // define table and primary key
        $this->_init('Epicor\Comm\Model\ResourceModel\Erp\Mapping\Products');
    }

}
