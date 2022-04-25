<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ListModel;


/**
 * Model Class for List Customer
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 * 
 * @method string getListId()
 * @method string getCustomerId()
 * 
 * @method string setListId()
 * @method string setCustomerId()
 */
class Customer extends \Epicor\Database\Model\Lists\Customer
{



    public function _construct()
    {
        $this->_init('Epicor\Lists\Model\ResourceModel\ListModel\Customer');
    }

}
