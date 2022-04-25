<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ResourceModel\ListModel\Address\Restriction;


/**
 * Model Collection Class for List Restricted Purchase
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Collection extends \Epicor\Database\Model\ResourceModel\Lists\Address\Collection
{

    public function _construct()
    {
        $this->_init('Epicor\Lists\Model\ListModel\Address\Restriction','Epicor\Lists\Model\ResourceModel\ListModel\Address\Restriction');
    }

}
