<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ResourceModel\ListModel\Product\Price;


/**
 * Model Collection Class for List Product Price
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Collection extends \Epicor\Database\Model\ResourceModel\Lists\Product\Price\Collection
{



    public function _construct()
    {
        $this->_init('Epicor\Lists\Model\ListModel\Product\Price','Epicor\Lists\Model\ResourceModel\ListModel\Product\Price');
    }

}
