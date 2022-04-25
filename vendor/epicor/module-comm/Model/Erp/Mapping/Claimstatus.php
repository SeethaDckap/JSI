<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Erp\Mapping;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Claimstatus extends \Epicor\Common\Model\Erp\Mapping\AbstractModel
{

    protected function _construct()
    {
        // define table and primary key
        $this->_init('Epicor\Comm\Model\ResourceModel\Erp\Mapping\Claimstatus');
    }

    public function toOptionArray()
    {
        $arr = array();
        $items = $this->getCollection()->getItems();
        foreach ($items as $item) {
            $arr[] = array('value' => $item->getClaimStatus());
        }
        return $arr;
    }

    public function toGridArray()
    {
        $arr = array();
        $items = $this->getCollection()->getItems();
        foreach ($items as $item) {
            $arr[$item->getClaimstatus] = $item->getClaimStatus();
        }
        return $arr;
    }

    public function getIdByCodeAndStore($claimtatusCode, $storeId)
    {
        $item = $this->getCollection()->addFieldToFilter('claim_status', $claimtatusCode)->addFieldToFilter('store_id', $storeId)->getFirstItem();
        return $item->getId();
    }

    public function getIdByStore($storeId)
    {
        $item = $this->getCollection()->addFieldToFilter('store_id', array('in' => $storeId));
        return $item;
    }

    public function getClaimStatus($erpCode = array())
    {
        $item = $this->getCollection()->addFieldToFilter('claim_status', $erpCode); //->addFieldToFilter('is_default', 1);
        return $item;
    }

    public function getEccClaimStatus()
    {
         return array(
            array('value' => 'open', 'label' => "Open"),
            array('value' => 'waiting', 'label' => "Waiting"),
            array('value' => 'request', 'label' => "Request"),
            array('value' => 'closed', 'label' => "Closed"),
            array('value' => 'locked', 'label' => "Locked"),
        );
    }

    public function getAllStatus()
    {
        return $this->getResource()->getAllStatus();
    }

}
