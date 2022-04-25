<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ListModel;


/**
 * Model Class for List Website
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 *
 * @method string getListId()
 * @method string getWebsiteId()
 *
 * @method string setListId()
 * @method string setWebsiteId()
 */
class Website extends \Epicor\Database\Model\Lists\Website
{

    public function _construct()
    {
        $this->_init('Epicor\Lists\Model\ResourceModel\ListModel\Website');
    }

}
