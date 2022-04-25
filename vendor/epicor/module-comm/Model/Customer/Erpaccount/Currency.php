<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Customer\Erpaccount;


/**
 * Customer group currency class for Erp
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Paul Ketelle
 * 
 * @method setErpAccountId(int $value)
 * @method setIsDefault(bool $value)
 * @method setCurrenyCode(string $value)
 * @method setOnstop(int $value)
 * @method setBalance(float $value)
 * @method setCreditLimit(float $value)
 * @method setUnallocatedCash(float $value)
 * @method setMinOrderAmount(float $value)
 * @method setLastPaymentDate(string $value)
 * @method setLastPaymentValue(float $value)
 * @method setCreatedAt(string $value)
 * @method setUpdatedAt(string $value)
 * @method int getErpAccountId()
 * @method string getCurrenyCode()
 * @method int getOnstop()
 * @method float getBalance()
 * @method float getCreditLimit()
 * @method float getUnallocatedCash()
 * @method float getMinOrderAmount()
 * @method string getLastPaymentDate()
 * @method float getLastPaymentValue()
 * @method string getCreatedAt()
 * @method string getUpdatedAt()
 */
class Currency extends \Epicor\Common\Model\AbstractModel
{

    protected $_eventPrefix = 'epicor_comm_customer_erpaccount_currency';
    protected $_eventObject = 'currency';

    protected function _construct()
    {
        // initialize resource model
        $this->_init('Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Currency');
    }

    public function getIsDefault()
    {
        return (bool) $this->getData('is_default');
    }

}
