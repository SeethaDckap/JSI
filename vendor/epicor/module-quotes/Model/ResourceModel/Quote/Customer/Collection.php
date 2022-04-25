<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Model\ResourceModel\Quote\Customer;


/**
 * Quote Customer resource model
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 * 
 * @method int getQuoteId()
 * @method int setQuoteId(int $value)
 * @method int getCustomerId()
 * @method int setCustomerId(int $value)
 */
class Collection extends \Epicor\Database\Model\ResourceModel\Quote\Customer\Collection
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('Epicor\Quotes\Model\Quote\Customer','Epicor\Quotes\Model\ResourceModel\Quote\Customer');
    }

}
