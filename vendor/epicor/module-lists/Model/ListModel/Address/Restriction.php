<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ListModel\Address;


/**
 * Model Class for List Restricted Purchase
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Restriction extends \Epicor\Database\Model\Lists\Address\Restrictions
{

    const TYPE_ADDRESS = 'A';
    const TYPE_STATE = 'S';
    const TYPE_COUNTRY = 'C';
    const TYPE_ZIP = 'Z';

    public function _construct()
    {
        $this->_init('Epicor\Lists\Model\ResourceModel\ListModel\Address\Restriction');
    }

}
