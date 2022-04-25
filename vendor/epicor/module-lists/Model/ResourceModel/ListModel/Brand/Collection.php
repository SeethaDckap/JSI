<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ResourceModel\ListModel\Brand;


/**
 * Model Collection Class for List Brands
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Collection extends \Epicor\Database\Model\ResourceModel\Lists\Brand\Collection
{



    public function _construct()
    {
        $this->_init('Epicor\Lists\Model\ListModel\Brand','Epicor\Lists\Model\ResourceModel\ListModel\Brand');
    }

}
