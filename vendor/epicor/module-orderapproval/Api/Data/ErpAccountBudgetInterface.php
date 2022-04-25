<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\OrderApproval\Api\Data;

/**
 * Erp Account Budget interface.
 * @api
 */
interface ErpAccountBudgetInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */

    const ERP_ID = 'erp_id';
    const TYPE = 'type';
    const START_DATE = 'start_date';
    const DURATION = 'duration';
    const AMOUNT = 'amount';
    const IS_ERP_INCLUDE = 'is_erp_include';
    const IS_ALLOW_CHECKOUT = 'is_allow_checkout';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get Erp Account Id.
     *
     * @return int|null
     */
    public function getErpId();

    /**
     * Get Budget Type
     *
     * @return string|null
     */
    public function getType();

    /**
     * Get Start date
     *
     * @return string|null
     */
    public function getStartDate();

    /**
     * Get duration
     *
     * @return int|null
     */
    public function getDuration();

    /**
     * Get budget amount
     *
     * @return float|null
     */
    public function getAmount();

    /**
     * Get is Erp include
     *
     * @return int|null
     */
    public function getIsErpInclude();

    /**
     * Get is allow checkout
     *
     * @return int|null
     */
    public function getIsAllowCheckout();

    /**
     * Get created at
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Get updated at
     *
     * @return string|null
     */
    public function getUpdatedAt();


    /**
     * Set Erp Account Id.
     *
     * @param $erpId
     * @return int|null
     */
    public function setErpId($erpId);

    /**
     * Set Budget Type
     *
     * @param $type
     * @return string|null
     */
    public function setType($type);

    /**
     * Set Start date
     *
     * @param $startDate
     * @return string|null
     */
    public function setStartDate($startDate);

    /**
     * Set duration
     *
     * @param $duration
     * @return int|null
     */
    public function setDuration($duration);

    /**
     * Set budget amount
     *
     * @param $amount
     * @return float|null
     */
    public function setAmount($amount);

    /**
     * Set is Erp include
     *
     * @param $isErpInclude
     * @return int|null
     */
    public function setIsErpInclude($isErpInclude);

    /**
     * Set is allow checkout
     *
     * @param $isAllowCheckout
     * @return int|null
     */
    public function setIsAllowCheckout($isAllowCheckout);

    /**
     * Set created at
     *
     * @param $createdAt
     * @return string|null
     */
    public function setCreatedAt($createdAt);

    /**
     * Set updated at
     *
     * @param $updatedAt
     * @return string|null
     */
    public function setUpdatedAt($updatedAt);
}
