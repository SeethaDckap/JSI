<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Groups;

use Epicor\OrderApproval\Api\Data\BudgetInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Model Class for Budget
 *
 * @category   Epicor
 * @package    Epicor_OrderApproval
 * @author     Epicor Websales Team
 *
 */
class Budget extends AbstractModel implements BudgetInterface
{
    public function _construct()
    {
        $this->_init('Epicor\OrderApproval\Model\ResourceModel\Groups\Budget');
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ID);
    }

    /**
     * Get Group ID.
     *
     * @return int
     */
    public function getGroupId()
    {
        return parent::getData(self::GROUP_ID);
    }

    /**
     * Get Type.
     *
     * @return string
     */
    public function getType()
    {
        return parent::getData(self::TYPE);
    }

    /**
     * Get Start Date.
     *
     * @return string
     */
    public function getStartDate()
    {
        return parent::getData(self::START_DATE);
    }

    /**
     * Get Duration.
     *
     * @return string
     */
    public function getDuration()
    {
        return parent::getData(self::DURATION);
    }

    /**
     * Get Amount.
     *
     * @return string
     */
    public function getAmount()
    {
        return parent::getData(self::AMOUNT);
    }

    /**
     * Get Is Erp Include.
     *
     * @return boolean
     */
    public function getIsErpInclude()
    {
        return parent::getData(self::IS_ERP_INCLUDE);
    }

    /**
     * Get IsAllowCheckout.
     *
     * @return boolean
     */
    public function getIsAllowCheckout()
    {
        return parent::getData(self::IS_ALLOW_CHECKOUT);
    }



    /**
     * Get Created At.
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_AT);
    }

    /**
     * Get Created At.
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return parent::getData(self::UPDATED_AT);
    }

    /**
     * Set Id.
     *
     * @param int $id
     *
     * @return \Epicor\OrderApproval\Api\Data\BudgetInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Set Group Id.
     *
     * @param int $groupId
     *
     * @return \Epicor\OrderApproval\Api\Data\BudgetInterface
     */
    public function setGroupId($groupId)
    {
        return $this->setData(self::GROUP_ID, $groupId);
    }

    /**
     * Set Type.
     *
     * @param string $type
     *
     * @return \Epicor\OrderApproval\Api\Data\BudgetInterface
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * Set Start Date
     *
     * @param string $startDate
     *
     * @return \Epicor\OrderApproval\Api\Data\BudgetInterface
     */
    public function setStartDate($startDate)
    {
        return $this->setData(self::START_DATE, $startDate);
    }

    /**
     * Set Duration.
     *
     * @param string $duration
     *
     * @return \Epicor\OrderApproval\Api\Data\BudgetInterface
     */
    public function setDuration($duration)
    {
        return $this->setData(self::DURATION, $duration);
    }

    /**
     * Set Amount.
     *
     * @param string $amount
     *
     * @return \Epicor\OrderApproval\Api\Data\BudgetInterface
     */
    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * Set Is Erp Include.
     *
     * @param boolean $isErpInclude
     *
     * @return \Epicor\OrderApproval\Api\Data\BudgetInterface
     */
    public function setIsErpInclude($isErpInclude)
    {
        return $this->setData(self::IS_ERP_INCLUDE, $isErpInclude);
    }

    /**
     * Set Is Allow Checkout.
     *
     * @param boolean $isAllowCheckout
     *
     * @return \Epicor\OrderApproval\Api\Data\BudgetInterface
     */
    public function setIsAllowCheckout($isAllowCheckout)
    {
        return $this->setData(self::IS_ALLOW_CHECKOUT, $isAllowCheckout);
    }


    /**
     * Set Created At.
     *
     * @param string $createdAt
     *
     * @return \Epicor\OrderApproval\Api\Data\BudgetInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Set Updated At.
     *
     * @param string $updatedAt
     *
     * @return \Epicor\OrderApproval\Api\Data\BudgetInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
