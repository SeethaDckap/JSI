<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model\Customer;


/**
 * Customer group class for Erp
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Erpaccount extends \Epicor\Comm\Model\Customer\Erpaccount
{

  /**
     * Checks whether the account type is B2B
     * 
     * @return boolean
     */
    public function isTypeDealer()
    {
        return $this->isType('Dealer');
    }
    /**
     * Checks whether the account type is B2B
     * 
     * @return boolean
     */
    public function isTypeDistributor()
    {
        return $this->isType('Distributor'); 
    }
    
    /**
     * Checks whether the account type is B2C
     * 
     * @return boolean
     */
    public function isTypeCustomer()
    {
        return $this->isType('B2C') || $this->isType('B2B') || $this->isType('Dealer') || $this->isType('Distributor');
    }

}
