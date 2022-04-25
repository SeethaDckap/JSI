<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Erp\Mapping;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Shippingstatus extends \Epicor\Common\Model\Erp\Mapping\AbstractModel
{

    protected function _construct()
    {
        // define table and primary key
        $this->_init('Epicor\Comm\Model\ResourceModel\Erp\Mapping\Shippingstatus');
    }

    public function toOptionArray() {
        $arr = array();
        $items = $this->getCollection()->getItems();
        foreach ($items as $item) {
            $arr[] = array('value' => $item->getShippingStatusCode(), 'label' => $item->getDescription());
        }
        return $arr;
    }

    public function toGridArray() {
        $arr = array();
        $items = $this->getCollection()->getItems();
        foreach ($items as $item) {
            $arr[$item->getShippingStatusCode()] = $item->getDescription();
        }
        return $arr;
    }

    public function getIdByCodeAndStore($shipstatusCode, $storeId) {
        $item = $this->getCollection()->addFieldToFilter('shipping_status_code', $shipstatusCode)->addFieldToFilter('store_id', $storeId)->getFirstItem();
        return $item->getId();
    }

    public function getIdByStore($storeId) {
        $item = $this->getCollection()->addFieldToFilter('store_id', array('in' => $storeId))->addFieldToFilter('is_default', 1);
        return $item;
    }

    public function getErpCodes($erpCode = array()) {
        $item = $this->getCollection()->addFieldToFilter('shipping_status_code', $erpCode); //->addFieldToFilter('is_default', 1);
        return $item;
    }

    public function getDefaultErpshipstatusCount($onlycode = null) {
        $item = $this->getCollection()->addFieldToFilter('is_default', 1);
        $onlycodes = array();
        if ($onlycode) {
            foreach ($item as $itemcode) {
                $onlycodes[] = $itemcode->getShippingStatusCode();
            }
            return $onlycodes;
        } else {
            return count($item->getData());
        }
    }

    public function getDefaultErpshipstatus() {
        $item = $this->getCollection()->addFieldToSelect('shipping_status_code')->addFieldToFilter('is_default', 1); //->getSelect()->columns('code');
        $codes = array();
        foreach ($item as $item) {
            $codes[] = $item->getShippingStatusCode();
        }
        return $codes;
    }
}
