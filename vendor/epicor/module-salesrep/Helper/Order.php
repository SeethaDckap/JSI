<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Helper;


class Order extends \Epicor\SalesRep\Helper\Data
{

    protected $_orderAccounts = array();

    public function getOrderErpAccount($erpAccountId, $field = null)
    {
        if (!isset($this->_orderAccounts[$erpAccountId])) {
            $erpAccount = $this->commCustomerErpaccountFactory->create()->load($erpAccountId);
            $this->_orderAccounts[$erpAccountId] = $erpAccount;
        } else {
            $erpAccount = $this->_orderAccounts[$erpAccountId];
        }

        return $field ? $erpAccount->getData($field) : $erpAccount;
    }

}
