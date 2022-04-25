<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Api\Data;

/**
 * OrderApproval Groups interface.
 *
 * @api
 */
interface OrderHistoryInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    const ID = 'id';
    const ORDER_ID = 'order_id';
    const GROUP_ID = 'group_id';
    const CUSTOMER_ID = 'customer_id';
    const STATUS = 'status';
    const RULES = 'rules';
    const CHILD_GROUP_ID = 'child_group_id';


    /**
     * Get Id
     *
     * @return int
     */
    public function getId();

    /**
     * Get Order Id
     *
     * @return int
     */
    public function getOrderId();

    /**
     * Get Group Id
     *
     * @return int
     */
    public function getGroupId();

    /**
     * Get Customer Id
     *
     * @return int
     */
    public function getCustomerId();

    /**
     * get Status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Get Rules
     *
     * @return string|null
     */
    public function getRules();

    /**
     * Get Source
     *
     * @return int|null
     */
    public function getChildGroupId();

    /**
     * Set Id
     *
     * @param int $id
     *
     * @return \Epicor\OrderApproval\Api\Data\OrderHistoryInterface
     */
    public function setId($id);

    /**
     * Set Order Id.
     *
     * @param int $orderId
     *
     * @return \Epicor\OrderApproval\Api\Data\OrderHistoryInterface
     */
    public function setOrderId($orderId);

    /**
     * Set Group Id
     *
     * @param int $groupId
     *
     * @return \Epicor\OrderApproval\Api\Data\OrderHistoryInterface
     */
    public function setGroupId($groupId);

    /**
     * Set Customer Id.
     *
     * @param int $customerId
     *
     * @return \Epicor\OrderApproval\Api\Data\OrderHistoryInterface
     */
    public function setCustomerId($customerId);

    /**
     * Set Status.
     *
     * @param string $status
     *
     * @return \Epicor\OrderApproval\Api\Data\OrderHistoryInterface
     */
    public function setStatus($status);

    /**
     * Set Rules.
     *
     * @param string|null $rules
     *
     * @return \Epicor\OrderApproval\Api\Data\OrderHistoryInterface
     */
    public function setRules($rules);

    /**
     * Set Child Group Id.
     *
     * @param int|null $childGroupId
     *
     * @return \Epicor\OrderApproval\Api\Data\OrderHistoryInterface
     */
    public function setChildGroupId($childGroupId);
}
