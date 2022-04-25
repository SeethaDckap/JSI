<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model;

use Magento\Framework\Model\AbstractModel;
use Epicor\OrderApproval\Api\Data\OrderHistoryInterface;

/**
 * Model Class for OrderApproval
 *
 * @category   Epicor
 * @package    Epicor_OrderApproval
 * @author     Epicor Websales Team
 *
 */
class OrderHistory extends AbstractModel implements OrderHistoryInterface
{
    public function _construct()
    {
        $this->_init('Epicor\OrderApproval\Model\ResourceModel\OrderHistory');
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ID);
    }

    /**
     * Get Order ID
     *
     * @return int
     */
    public function getOrderId()
    {
        return parent::getData(self::ORDER_ID);
    }

    /**
     * Get Group ID
     *
     * @return int
     */
    public function getGroupId()
    {
        return parent::getData(self::GROUP_ID);
    }

    /**
     * Get Customer ID
     *
     * @return int
     */
    public function getCustomerId()
    {
        return parent::getData(self::CUSTOMER_ID);
    }

    /**
     * Get Status
     *
     * @return string
     */
    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }

    /**
     * Get Rules
     *
     * @return string
     */
    public function getRules()
    {
        return parent::getData(self::RULES);
    }

    /**
     * Get Child Group Id
     *
     * @return int
     */
    public function getChildGroupId()
    {
        return parent::getData(self::CHILD_GROUP_ID);
    }

    /**
     * Set Id
     *
     * @param int $id
     *
     * @return \Epicor\OrderApproval\Api\Data\OrderHistoryInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Set Order Id
     *
     * @param int $orderId
     *
     * @return \Epicor\OrderApproval\Api\Data\OrderHistoryInterface
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Set Group Id
     *
     * @param int $groupId
     *
     * @return \Epicor\OrderApproval\Api\Data\OrderHistoryInterface
     */
    public function setGroupId($groupId)
    {
        return $this->setData(self::GROUP_ID, $groupId);
    }

    /**
     * Set Customer Id
     *
     * @param int $customerId
     *
     * @return \Epicor\OrderApproval\Api\Data\OrderHistoryInterface
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Set Status
     *
     * @param string $status
     *
     * @return \Epicor\OrderApproval\Api\Data\OrderHistoryInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Set Rules
     *
     * @param string $rules
     *
     * @return \Epicor\OrderApproval\Api\Data\OrderHistoryInterface
     */
    public function setRules($rules)
    {
        return $this->setData(self::RULES, $rules);
    }

    /**
     * Set Child Group Id
     *
     * @param string $childGroupId
     *
     * @return \Epicor\OrderApproval\Api\Data\OrderHistoryInterface
     */
    public function setChildGroupId($childGroupId)
    {
        return $this->setData(self::CHILD_GROUP_ID, $childGroupId);
    }
}
