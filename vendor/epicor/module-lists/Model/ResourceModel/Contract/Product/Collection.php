<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ResourceModel\Contract\Product;


/**
 * Model Collection Class for Contract Products
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Collection extends \Epicor\Database\Model\ResourceModel\Contract\Collection
{


    public function _construct()
    {
        $this->_init('Epicor\Lists\Model\Contract\Product','Epicor\Lists\Model\ResourceModel\Contract\Product');
    }

}
