<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\ResourceModel\Erp\Customer\Skus;


class Collection extends \Epicor\Database\Model\ResourceModel\Erp\Account\Sku\Collection
{

    private $_cacheTime;

    public function _construct()
    {
        parent::_construct();
        $this->_init('Epicor\Customerconnect\Model\Erp\Customer\Skus', 'Epicor\Customerconnect\Model\ResourceModel\Erp\Customer\Skus');
    }

    /**
     * Returns any cache time for this collection
     * 
     * @return integer 
     */
    public function getCacheTime()
    {
        return $this->_cacheTime;
    }

}
