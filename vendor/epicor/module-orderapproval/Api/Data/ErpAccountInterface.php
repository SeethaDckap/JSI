<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\OrderApproval\Api\Data;

/**
 * OrderApproval Groups interface.
 * @api
 */
interface ErpAccountInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    const GROUP_ID       = 'group_id';
    const ERP_ACCOUNT_ID = 'erp_account_id';

    /**
     * Get Group Id
     *
     * @return int|null
     */
    public function getGroupId();

    /**
     * Get Erp Account Id.
     *
     * @return int|null
     */
    public function getErpAccountId();

    /**
     * Set Group Id
     *
     * @param int $groupId
     * @return ErpAccountInterface
     */
    public function setGroupId($groupId);

    /**
     * Set Erp Account Id.
     *
     * @param int $erpAccountId
     * @return ErpAccountInterface
     */
    public function setErpAccountId($erpAccountId);
}
