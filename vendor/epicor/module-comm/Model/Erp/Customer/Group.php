<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Erp\Customer;


class Group extends \Epicor\Common\Model\AbstractModel
{

    protected $_eventPrefix = 'ecc_erp_account';
    protected $_eventObject = 'erp_customer_group';

    protected function _construct()
    {
        // initialize resource model
        $this->_init('Epicor\Comm\Model\ResourceModel\Erp\Customer\Group');
    }

}
