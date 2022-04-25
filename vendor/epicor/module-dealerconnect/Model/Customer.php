<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model;


/**
 * Customer model override
 * 
 * Overrides customer address functionality
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Customer extends \Epicor\Comm\Model\Customer
{
     
      /**
     * Returns whether this customer is an erp customer
     * 
     * @return boolean
     */
    public function isCustomer($session = true)
    { 
        $erpAccount = false;
        $customer = false;
        $helper = $this->commHelper->create();

        if ($this->isSalesRep() && $helper->isMasquerading()) {
            $erpAccount = $helper->getErpAccountInfo(null, 'customer');
        } else if ($this->getEccErpaccountId()) {
            if ($session == true) {
                $erpAccount = $helper->getErpAccountInfo(null, 'customer');
            } else {
                $erpAccount = $this->getCustomerErpAccount();
            }
        }
        if ($erpAccount) { 
            if($erpAccount->isTypeB2b() || $erpAccount->isTypeDealer() ||$erpAccount->isTypeDistributor()){
                $customer = true;
            }else{ 
                $customer = false;
            }
        } 
        return $customer;
    }
    
    /**
     * Returns whether this customer is an Dealer customer
     * @return boolean
     */
    public function isDealer($session = true)
    {
        $erpAccount = false;
        $customer = false;
        $helper = $this->commHelper->create();

        if ($this->getEccErpaccountId()) {
            if ($session == true) {
                $erpAccount = $helper->getErpAccountInfo(null, 'customer');
            } else {
                $erpAccount = $this->getCustomerErpAccount();
            }
        }
        if ($erpAccount) {
            $customer = $erpAccount->isTypeDealer() ? true : false;
        }
        return $customer;
    }
     /**
     * Returns whether this customer is an Distributor customer
     * @return boolean
     */
    public function isDistributor($session = true)
    {
        $erpAccount = false;
        $customer = false;
        $helper = $this->commHelper->create();

        if ($this->getEccErpaccountId()) {
            if ($session == true) {
                $erpAccount = $helper->getErpAccountInfo(null, 'customer');
            } else {
                $erpAccount = $this->getCustomerErpAccount();
            }
        }
        if ($erpAccount) {
            $customer = $erpAccount->isTypeDistributor() ? true : false;
        }
        return $customer;
    }
}
