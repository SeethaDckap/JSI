<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ResourceModel\ListModel\Customer;


/**
 * Model Collection Class for List Customer
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Collection extends \Epicor\Database\Model\ResourceModel\Lists\Customer\Collection
{


    public function _construct()
    {
        $this->_init('Epicor\Lists\Model\ListModel\Customer','Epicor\Lists\Model\ResourceModel\ListModel\Customer');
    }

}
