<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Customer\Erpaccount;


/**
 * Customer ERP Account group store
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 * 
 * @method setErpCode(string $erpCode);
 * @method setStoreId(string $erpCode);
 * 
 * @method string getStoreId();
 * @method string getErpCode();
 */
class Store extends \Epicor\Common\Model\AbstractModel
{

    protected function _construct()
    {
        // initialize resource model
        $this->_init('Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Store');
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
