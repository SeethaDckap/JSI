<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ResourceModel\ListModel\Label;


/**
 * Model Collection Class for List Label
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Collection extends \Epicor\Database\Model\ResourceModel\Lists\Label\Collection
{

    public function _construct()
    {
        $this->_init('Epicor\Lists\Model\ListModel\Label','Epicor\Lists\Model\ResourceModel\ListModel\Label');
    }

}
