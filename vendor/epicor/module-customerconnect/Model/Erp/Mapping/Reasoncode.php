<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Erp\Mapping;


class Reasoncode extends \Epicor\Common\Model\Erp\Mapping\AbstractModel
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('Epicor\Customerconnect\Model\ResourceModel\Erp\Mapping\Reasoncode');
    }

    public function toOptionArray()
    {
        $arr = array();
        $items = $this->getCollection()->getItems();
        foreach ($items as $item) {
            $arr[] = array('value' => $item->getCode(), 'label' => $item->getDescription());
        }
        return $arr;
    }

    public function toGridArray()
    {
        $arr = array();
        $items = $this->getCollection()->getItems();
        foreach ($items as $item) {
            $arr[$item->getCode()] = $item->getDescription();
        }
        return $arr;
    }

    public function getIdByCodeAndStore($reasonCode, $storeId)
    {
        $item = $this->getCollection()->addFieldToFilter('code', $reasonCode)->addFieldToFilter('store_id', $storeId)->getFirstItem();
        return $item->getId();
    }

}
