<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ResourceModel\ListModel\Store\Group;


/**
 * Model Collection Class for List Store Group
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Collection extends \Epicor\Database\Model\ResourceModel\Lists\Store\Group\Collection
{

    public function _construct()
    {
        $this->_init('Epicor\Lists\Model\ListModel\Store\Group','Epicor\Lists\Model\ResourceModel\ListModel\Store\Group');
    }

}
