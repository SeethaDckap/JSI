<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Erp\Mapping;


class Servicecallstatus extends \Epicor\Common\Model\Erp\Mapping\AbstractModel
{

    public function _construct()
    {
        $this->_init('Epicor\Customerconnect\Model\ResourceModel\Erp\Mapping\Servicecallstatus');
    }

    public function toOptionArray()
    {
        $arr = array();
        $items = $this->getCollection()->getItems();
        foreach ($items as $item) {
            $arr[] = array('value' => $item->getCode(), 'label' => $item->getCode());
        }
        return $arr;
    }

    public function toGridArray()
    {
        $arr = array();
        $items = $this->getCollection()->getItems();
        foreach ($items as $item) {
            $arr[$item->getCode()] = $item->getStatus();
        }
        return $arr;
    }

}
