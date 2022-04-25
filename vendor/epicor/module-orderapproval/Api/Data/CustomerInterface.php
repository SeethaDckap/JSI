<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Api\Data;

/**
 * OrderApproval Customer interface.
 *
 * @api
 */
interface CustomerInterface
{

    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    const GROUP_ID = 'group_id';
    const CUSTOMER_ID = 'customer_id';
    const BY_GROUP = 'by_group';
    const BY_CUSTOMER = 'by_customer';

    /**
     * Get Group Id.
     *
     * @return int
     */
    public function getGroupId();

    /**
     * Get Customer Id.
     *
     * @return int
     */
    public function getCustomerId();

    /**
     * Get By Group.
     *
     * @return int
     */
    public function getByGroup();

    /**
     * Get By Customer.
     *
     * @return int
     */
    public function getByCustomer();

    /**
     * Set Group Id.
     *
     * @param int $groupId
     *
     * @return \Epicor\OrderApproval\Api\Data\CustomerInterface
     */
    public function setGroupId($groupId);

    /**
     * Set Customer Id.
     *
     * @param int $customerId
     *
     * @return \Epicor\OrderApproval\Api\Data\CustomerInterface
     */
    public function setCustomerId($customerId);

    /**
     * Set By Group.
     *
     * @param int $byGroup
     *
     * @return \Epicor\OrderApproval\Api\Data\CustomerInterface
     */
    public function setByGroup($byGroup);

    /**
     * Set By Customer.
     *
     * @param int $byCustomer
     *
     * @return \Epicor\OrderApproval\Api\Data\CustomerInterface
     */
    public function setByCustomer($byCustomer);
}
