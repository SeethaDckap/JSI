<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ListModel\Store;


/**
 * Model Class for List Store Group
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 * 
 * @method string getListId()
 * @method string getStoreGroupId()
 * 
 * @method string setListId()
 * @method string setStoreGroupId()
 */
class Group extends \Epicor\Database\Model\Lists\Store\Group
{

    public function _construct()
    {
        $this->_init('Epicor\Lists\Model\ResourceModel\ListModel\Store\Group');
    }

}
