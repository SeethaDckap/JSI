<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model;


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Account
 *
 * @author Paul.Ketelle
 */
class CustomerErpaccount extends \Epicor\Common\Model\AbstractModel
{

    function _construct()
    {
        $this->_init('Epicor\Common\Model\ResourceModel\CustomerErpaccount');
    }

    /**
     * @return $this
     */
    public function saveRel()
    {
        $this->getResource()->saveRel($this);
        $this->_registry->unregister('erp_acct_counts_'.$this->getCustomerId());
        return $this;
    }

    public function getErpAcctCounts()
    {
        return $this->getResource()->getErpAcctCounts($this);
    }

    public function deleteByErpId()
    {
        return $this->getResource()->deleteByErpId($this);
    }

    public function getAllErpAcctids()
    {
        return $this->getResource()->getAllErpAcctids($this);
    }

    public function updateByCustomerId()
    {
        $this->getResource()->updateByCustomerId($this);
        $this->_registry->unregister('erp_acct_counts_'.$this->getCustomerId());
        return $this;
    }

    public function updateFavourite()
    {
        return $this->getResource()->updateFavourite($this);
    }

    public function unselectFavourite()
    {
        return $this->getResource()->unselectFavourite($this);
    }

}
