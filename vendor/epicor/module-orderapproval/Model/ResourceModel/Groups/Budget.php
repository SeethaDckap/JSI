<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\ResourceModel\Groups;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Epicor\OrderApproval\Model\Status\Options;

/**
 * Model Resource Class for Budget
 *
 * @category   Epicor
 * @package    Epicor_OrderApproval
 * @author     Epicor Websales Team
 */
class Budget extends AbstractDb
{
    /**
     * Exclude Order Status.
     *
     * @var array
     */
    private $excludeOrderStatus = [
        Options::ECC_ORDER_APPROVAL_REJECTED_GOR_STATE
    ];

    /**
     * Include Order Status.
     *
     * @var array
     */
    private $includeOrderStatus = [
        Options::ECC_ORDER_APPROVAL_PENDING_GOR_STATE,
        Options::ECC_ORDER_NOT_SENT_GOR_STATE,
        Options::ECC_ORDER_ERP_ERROR_GOR_STATE,
        Options::ECC_ORDER_ERROR_RETRY_FAILED_GOR_STATE
    ];

    /**
     * Construct.
     */
    protected function _construct()
    {
        $this->_init('ecc_approval_group_budget', 'id');
    }

    /**
     * Get Order Total.
     *
     * @param string $customerIds
     * @param string $fromDate
     * @param string $endDate
     * @param bool   $excludeERP
     * @param array  $excludeOrderId
     *
     * @return int|string
     */
    public function getOrderTotal(
        $customerIds,
        $fromDate,
        $endDate,
        $excludeERP = false,
        $excludeOrderId = []
    ) {
        $connection = $this->getConnection();
        $table = $connection->getTableName('sales_order');
        $excludeStatus = implode(',', $this->excludeOrderStatus);
        $includeStatus = implode(',', $this->includeOrderStatus);

        /**
         * Get order total based on
         * erp period total getting by AST
         */
        if (!$excludeERP) {
            $sql = "SELECT SUM(grand_total) AS grand_total FROM $table
                WHERE `customer_id` IN ($customerIds) AND `ecc_gor_sent` NOT IN ($excludeStatus)
                  AND created_at >= '$fromDate' AND created_at  <= '$endDate'";
        } else {
            $sql = "SELECT SUM(grand_total) AS grand_total FROM $table
                WHERE `customer_id` IN ($customerIds) AND `ecc_gor_sent` IN ($includeStatus)
                  AND created_at >= '$fromDate' AND created_at  <= '$endDate'";
        }

        if ($excludeOrderId) {
            $excludeOrderId = implode(',', $excludeOrderId);
            $sql = $sql." AND `entity_id` NOT IN (".$excludeOrderId.")";
        }


        return $connection->fetchOne($sql) ?: 0;
    }

    /**
     * @return array
     */
    public function getExcludeOrderStatus()
    {
        return $this->excludeOrderStatus;
    }

    /**
     * @return array
     */
    public function getIncludeOrderStatus()
    {
        return $this->includeOrderStatus;
    }

    /**
     * Get Customer Ids By Erp Id.
     *
     * @param string $erpAccountId
     *
     * @return array
     */
    public function getCustomerIdsbyErpId($erpAccountId)
    {
        $connection = $this->getConnection();
        $table = $connection->getTableName('ecc_customer_erp_account');

        $sql = "SELECT `customer_id` FROM $table
            WHERE `erp_account_id` = '$erpAccountId'";

        return $connection->fetchCol($sql);
    }
}
