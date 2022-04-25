<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ListModel;


/**
 * Model Class for List Label
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 * 
 * @method string getListId()
 * @method string getWebsiteId()
 * @method string getStoreGroupId()
 * @method string getStoreId()
 * @method string getLabel()
 * 
 * @method string setListId()
 * @method string setWebsiteId()
 * @method string setStoreGroupId()
 * @method string setStoreId()
 * @method string setLabel()
 */
class Label extends \Epicor\Database\Model\Lists\Label
{


    public function _construct()
    {
        $this->_init('Epicor\Lists\Model\ResourceModel\ListModel\Label');
    }

}
