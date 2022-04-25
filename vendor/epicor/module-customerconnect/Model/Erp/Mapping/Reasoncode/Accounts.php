<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Erp\Mapping\Reasoncode;


class Accounts extends \Epicor\Database\Model\Erp\Mapping\Reasoncode\Accounts
{

    public function _construct()
    {
        $this->_init('Epicor\Customerconnect\Model\ResourceModel\Erp\Mapping\Reasoncode\Accounts');
    }

    public function deleteByCode($reasonCode)
    {
        $items = $this->getCollection()->addFieldToFilter('code', $reasonCode);
        foreach ($items as $item) {
            try {
                $item->delete();
            } catch (\Exception $e) {
                throw new \Exception('Could not delete record "Reason Code: "', '008');
            }
        }
    }

}
