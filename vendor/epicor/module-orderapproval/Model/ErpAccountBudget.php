<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model;

use Magento\Framework\Model\AbstractModel;
use Epicor\OrderApproval\Api\Data\ErpAccountBudgetInterface;

class ErpAccountBudget extends AbstractModel implements ErpAccountBudgetInterface
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('Epicor\OrderApproval\Model\ResourceModel\ErpAccountBudget');
    }

    /**
     * @return int|null
     */
    public function getErpId()
    {
        return parent::getData(self::ERP_ID);
    }

    /**
     * @return string|null
     */
    public function getType()
    {
        return parent::getData(self::TYPE);
    }

    /**
     * @return string|null
     */
    public function getStartDate()
    {
        return parent::getData(self::START_DATE);
    }

    /**
     * @return int|null
     */
    public function getDuration()
    {
        return parent::getData(self::DURATION);
    }

    /**
     * @return float|null
     */
    public function getAmount()
    {
        return parent::getData(self::AMOUNT);
    }

    /**
     * @return int|null
     */
    public function getIsErpInclude()
    {
        return parent::getData(self::IS_ERP_INCLUDE);
    }

    /**
     * @return int|null
     */
    public function getIsAllowCheckout()
    {
        return parent::getData(self::IS_ALLOW_CHECKOUT);
    }

    /**
     * @return string|null
     */
    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_AT);
    }

    /**
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return parent::getData(self::UPDATED_AT);
    }

    /**
     * Set Erp Account Id.
     *
     * @param string $erpId
     * @return ErpAccountBudgetInterface
     */
    public function setErpId($erpId)
    {
        return $this->setData(self::ERP_ID, $erpId);
    }

    /**
     * Set Budget Type
     *
     * @param string $type
     * @return ErpAccountBudgetInterface
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * Set Start date
     *
     * @param string $startDate
     * @return ErpAccountBudgetInterface
     */
    public function setStartDate($startDate)
    {
        return $this->setData(self::START_DATE, $startDate);
    }

    /**
     * Set duration
     *
     * @param string $duration
     * @return ErpAccountBudgetInterface
     */
    public function setDuration($duration)
    {
        return $this->setData(self::DURATION, $duration);
    }

    /**
     * Set budget amount
     *
     * @param string $amount
     * @return ErpAccountBudgetInterface
     */
    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * Set is Erp include
     *
     * @param string $isErpInclude
     * @return ErpAccountBudgetInterface
     */
    public function setIsErpInclude($isErpInclude)
    {
        return $this->setData(self::IS_ERP_INCLUDE, $isErpInclude);
    }

    /**
     * Set is allow checkout
     *
     * @param string $isAllowCheckout
     * @return ErpAccountBudgetInterface
     */
    public function setIsAllowCheckout($isAllowCheckout)
    {
        return $this->setData(self::IS_ALLOW_CHECKOUT, $isAllowCheckout);
    }

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return ErpAccountBudgetInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return ErpAccountBudgetInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
