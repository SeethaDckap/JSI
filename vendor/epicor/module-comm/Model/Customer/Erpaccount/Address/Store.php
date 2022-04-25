<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Customer\Erpaccount\Address;


/**
 * Customer ERP Account group store
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 * 
 * @method setErpCustomerGroupCode(string $erpCode);
 * @method setErpCode(string $erpCode);
 * @method setStoreId(string $erpCode);
 * 
 * @method string getErpCustomerGroupCode();
 * @method string getErpCode();
 * @method string getStoreId();

 */
class Store extends \Epicor\Common\Model\AbstractModel
{

    protected function _construct()
    {
        // initialize resource model
        $this->_init('Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\Store');
    }

    protected function _afterSaveCommit()
    {
        parent::_afterSaveCommit();
    }

    protected function _beforeDelete()
    {
        parent::_beforeDelete();
    }

}
