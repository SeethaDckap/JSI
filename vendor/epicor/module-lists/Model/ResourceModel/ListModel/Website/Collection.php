<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ResourceModel\ListModel\Website;


/**
 * Model Collection Class for List Website
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Collection extends \Epicor\Database\Model\ResourceModel\Lists\Website\Collection
{



    public function _construct()
    {
        $this->_init('Epicor\Lists\Model\ListModel\Website','Epicor\Lists\Model\ResourceModel\ListModel\Website');
    }

}
