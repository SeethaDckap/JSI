<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Api\Data;

/**
 * OrderApproval Budget interface.
 *
 * @api
 */
interface BudgetInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
	const ID                = 'id';
	const GROUP_ID          = 'group_id';
	const TYPE              = 'type';
	const START_DATE        = 'start_date';
	const DURATION          = 'duration';
	const AMOUNT            = 'amount';
	const IS_ERP_INCLUDE    = 'is_erp_include';
	const IS_ALLOW_CHECKOUT = 'is_allow_checkout';
	const CREATED_AT        = 'created_at';
	const UPDATED_AT        = 'updated_at';


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
     * Get Type.
     *
     * @return string
     */
    public function getType();

    /**
     * Get Start Date.
     *
     * @return string
     */
    public function getStartDate();

    /**
     * Get Duration.
     *
     * @return string
     */
    public function getDuration();

    /**
     * Get Amount.
     *
     * @return string
     */
    public function getAmount();

    /**
     * Get Is Erp Include.
     *
     * @return boolean
     */
    public function getIsErpInclude();

    /**
     * Set Id.
     *
     * @param int $id
     *
     * @return \Epicor\OrderApproval\Api\Data\budgetInterface
     */
    public function setId($id);

    /**
     * Set Group Id.
     *
     * @param int $groupId
     *
     * @return \Epicor\OrderApproval\Api\Data\budgetInterface
     */
    public function setGroupId($groupId);

    /**
     * Set Type.
     *
     * @param string $type
     *
     * @return \Epicor\OrderApproval\Api\Data\budgetInterface
     */
    public function setType($type);

    /**
     * Set Start Date.
     *
     * @param string $startDate
     *
     * @return \Epicor\OrderApproval\Api\Data\budgetInterface
     */
    public function setStartDate($startDate);

    /**
     * Set Duration.
     *
     * @param string $duration
     *
     * @return \Epicor\OrderApproval\Api\Data\budgetInterface
     */
    public function setDuration($duration);

    /**
     * Set Amount.
     *
     * @param string $amount
     *
     * @return \Epicor\OrderApproval\Api\Data\budgetInterface
     */
    public function setAmount($amount);

    /**
     * Set Is Erp Include.
     *
     * @param boolean $isErpInclude
     *
     * @return \Epicor\OrderApproval\Api\Data\budgetInterface
     */
    public function setIsErpInclude($isErpInclude);

    /**
     * Set Is Allow Checkout.
     *
     * @param boolean $isAllowCheckout
     *
     * @return \Epicor\OrderApproval\Api\Data\budgetInterface
     */
    public function setIsAllowCheckout($isAllowCheckout);

    /**
     * Set Created At
     *
     * @param string $createdAt
     * @return \Epicor\OrderApproval\Api\Data\budgetInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Set Updated At
     *
     * @param string $updatedAt
     * @return \Epicor\OrderApproval\Api\Data\budgetInterface
     */
    public function setUpdatedAt($updatedAt);

}
