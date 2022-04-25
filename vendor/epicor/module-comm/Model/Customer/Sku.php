<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Customer;


/**
 * Customer group class for Erp
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Sku extends \Epicor\Database\Model\Erp\Account\Sku
{

    protected $_eventPrefix = 'epicor_comm_customer_sku';
    protected $_eventObject = 'customer_sku';

    protected function _construct()
    {
        // initialize resource model
        $this->_init('Epicor\Comm\Model\ResourceModel\Customer\Sku');
    }

}
