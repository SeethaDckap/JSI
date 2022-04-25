<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Model\Quote;


/**
 * Quote Customer model
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
class Customer extends \Epicor\Database\Model\Quote\Customer
{
   


    public function _construct()
    {
        $this->_init('Epicor\Quotes\Model\ResourceModel\Quote\Customer');
    }

}
