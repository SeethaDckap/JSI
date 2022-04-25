<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Model\ResourceModel\Quote\Note;


/**
 * Quote Customer collection model
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Collection extends \Epicor\Database\Model\ResourceModel\Quote\Note\Collection
{



    public function _construct()
    {
        parent::_construct();
        $this->_init('Epicor\Quotes\Model\Quote\Note','Epicor\Quotes\Model\ResourceModel\Quote\Note');
    }

}
