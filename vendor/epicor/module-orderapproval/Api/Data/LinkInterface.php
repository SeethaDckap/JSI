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
interface LinkInterface
{

    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    const ID = 'id';
    const GROUP_ID = 'group_id';
    const PARENT_GROUP_ID = 'parent_group_id';
    const BY_GROUP = 'by_group';
    const BY_CUSTOMER = 'by_customer';

    /**
     * Get Id.
     *
     * @return int
     */
    public function getId();

    /**
     * Get Group Id.
     *
     * @return int
     */
    public function getGroupId();

    /**
     * Get Parent Group Id.
     *
     * @return int
     */
    public function getParentGroupId();

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
     * Set Id.
     *
     * @param int $id
     *
     * @return \Epicor\OrderApproval\Api\Data\LinkInterface
     */
    public function setId($id);

    /**
     * Set Group Id.
     *
     * @param int $groupId
     *
     * @return \Epicor\OrderApproval\Api\Data\LinkInterface
     */
    public function setGroupId($groupId);

    /**
     * Set Parent Group Id.
     *
     * @param int $parent
     *
     * @return \Epicor\OrderApproval\Api\Data\LinkInterface
     */
    public function setParentGroupId($parent);

    /**
     * Set By Group.
     *
     * @param int $byGroup
     *
     * @return \Epicor\OrderApproval\Api\Data\LinkInterface
     */
    public function setByGroup($byGroup);

    /**
     * Set By Customer.
     *
     * @param int $byCustomer
     *
     * @return \Epicor\OrderApproval\Api\Data\LinkInterface
     */
    public function setByCustomer($byCustomer);
}
