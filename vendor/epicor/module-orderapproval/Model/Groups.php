<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Model\AbstractModel;
use Epicor\OrderApproval\Api\Data\GroupsInterface;

/**
 * Model Class for OrderApproval
 *
 * @category   Epicor
 * @package    Epicor_OrderApproval
 * @author     Epicor Websales Team
 *
 */
class Groups extends AbstractModel implements GroupsInterface
{
    public function _construct()
    {
        $this->_init('Epicor\OrderApproval\Model\ResourceModel\Groups');
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getGroupId()
    {
        return parent::getData(self::GROUP_ID);
    }

    /**
     * Get Name
     *
     * @return string
     */
    public function getName()
    {
        return parent::getData(self::NAME);
    }

    /**
     * Get ID
     *
     * @return string
     */
    public function getIsActive()
    {
        return parent::getData(self::IS_ACTIVE);
    }

    /**
     * Get ID
     *
     * @return string
     */
    public function getIsMultiLevel()
    {
        return parent::getData(self::IS_MULTI_LEVEL);
    }

    /**
     * Get ID
     *
     * @return string
     */
    public function getRules()
    {
        return parent::getData(self::RULES);
    }

    /**
     * Get ID
     *
     * @return string
     */
    public function getSource()
    {
        return parent::getData(self::SOURCE);
    }

    /**
     * Get Priority
     *
     * @return int
     */
    public function getPriority()
    {
        return parent::getData(self::PRIORITY);
    }

    /**
     * get Is Budget Active
     *
     * @return boolean
     */
    public function getIsBudgetActive()
    {
        return parent::getData(self::IS_BUDGET_ACTIVE);
    }

    /**
     * Get Created At
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_AT);
    }

    /**
     * Get Created At
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return parent::getData(self::UPDATED_AT);
    }

    /**
     * Set Group Id
     *
     * @param int $groupId
     *
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface
     */
    public function setGroupId($groupId)
    {
        return $this->setData(self::GROUP_ID, $groupId);
    }

    /**
     * Set Name
     *
     * @param string $name
     *
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Set Is Active
     *
     * @param string $isActive
     *
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * Set Is MultiLevel
     *
     * @param string $isMultiLevel
     *
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface
     */
    public function setIsMultiLevel($isMultiLevel)
    {
        return $this->setData(self::IS_MULTI_LEVEL, $isMultiLevel);
    }

    /**
     * Set Group Id
     *
     * @param string $rules
     *
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface
     */
    public function setRules($rules)
    {
        return $this->setData(self::RULES, $rules);
    }

    /**
     * Set Group Id
     *
     * @param string $source
     *
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface
     */
    public function setSource($source)
    {
        return $this->setData(self::SOURCE, $source);
    }

    /**
     * Set Group Order
     *
     * @param int $priority
     *
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface
     */
    public function setPriority($priority)
    {
        return $this->setData(self::PRIORITY, $priority);
    }

    /**
     * Set Is Budget Active.
     *
     * @param boolean $isBudget
     *
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface
     */
    public function setIsBudgetActive($isBudget)
    {
        return $this->setData(self::IS_BUDGET_ACTIVE, $isBudget);
    }



    /**
     * Set Created At
     *
     * @param string $createdAt
     *
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Set Updated At
     *
     * @param string $updatedAt
     *
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
