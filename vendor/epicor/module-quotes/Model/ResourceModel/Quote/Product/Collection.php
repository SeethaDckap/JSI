<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Model\ResourceModel\Quote\Product;


class Collection extends \Epicor\Database\Model\ResourceModel\Quote\Product\Collection
{


    public function _construct()
    {
        parent::_construct();
        $this->_init('Epicor\Quotes\Model\Quote\Product','Epicor\Quotes\Model\ResourceModel\Quote\Product');
    }

}
