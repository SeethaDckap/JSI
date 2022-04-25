<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Approval;

use Epicor\OrderApproval\Model\GroupSave\Utilities as GroupUtilities;
use Epicor\OrderApproval\Logger\Logger as ApprovalLogger;

class HistoryStatus
{
    /**
     * @var GroupUtilities
     */
    private $utilities;

    /**
     * @var ApprovalLogger
     */
    private $approvalLogger;

    /**
     * HistoryStatus constructor.
     * @param ApprovalLogger $approvalLogger
     * @param GroupUtilities $utilities
     */
    public function __construct(
        ApprovalLogger $approvalLogger,
        GroupUtilities $utilities
    ) {
        $this->utilities = $utilities;
        $this->approvalLogger = $approvalLogger;
    }

    /**
     * @param $isApprovalPending
     * @param $historyStatus
     * @return string
     */
    public function getApprovalHistoryStatus($isApprovalPending, $historyStatus)
    {
        if ($this->isSelfApproved($historyStatus)) {
            return 'Approved';
        }
        if ($isApprovalPending === '0') {
            return 'Approved';
        }
        if ($isApprovalPending === '2') {
            return 'Rejected';
        }

        if ($isApprovalPending === '1') {
            return 'Pending';
        }
    }

    /**
     * Shows approved for user that has self approved
     *
     * @param $historyStatus
     * @param $isApprovalPending
     * @return bool
     */
    public function isSelfApproved($historyStatus)
    {
        return $historyStatus === 'Self Approved';
    }

    /**
     * @param $orderId
     * @param $status
     * @return false
     */
    public function updateLastHistoryStatus($orderId, $status)
    {
        try {
            if (!$orderId || !$status) {
                throw new \InvalidArgumentException('Order id or status missing while updating history table');
            }

            $lastId = $this->getLastHistoryIdByOrder($orderId);
            if (!$lastId) {
                throw new \InvalidArgumentException('Unable to get history id');
            }
            $connection = $this->utilities->getResourceConnection()->getConnection();
            $table = $connection->getTableName('ecc_approval_order_history');
            $sql = "UPDATE $table set status = '$status' 
               where  id = $lastId";

            $connection->query($sql);
        } catch (\Exception $e) {
            $this->approvalLogger->addError($e->getMessage());
        }
    }

    /**
     * @param $orderId
     * @return string
     */
    private function getLastHistoryIdByOrder($orderId)
    {
        $connection = $this->utilities->getResourceConnection()->getConnection();
        $table = $connection->getTableName('ecc_approval_order_history');
        $sql = "SELECT MAX(id) FROM $table where order_id = $orderId";

        return $connection->fetchOne($sql);
    }

    /**
     * @param $order
     */
    public function resetApprovalProcess($order)
    {
        try {
            if (!$order instanceof \Magento\Sales\Model\Order) {
                throw new \InvalidArgumentException(
                    'Order not available, unable to reset history during resetting approval Process'
                );
            }
            $this->clearHistoryForOrder($order->getId());
            /** @var \Magento\Framework\Event\ManagerInterface $eventManager */
            $eventManager = $order->getData('event_manager');
            $order->setData('is_order_approval_reset', '1');
            $eventManager->dispatch('order_approval_reset_process', ['order' => $order]);
        } catch (\Exception $e) {
            $this->approvalLogger->addError($e->getMessage());
        }
    }

    /**
     * @param $orderId
     * @return \Zend_Db_Statement_Interface
     */
    private function clearHistoryForOrder($orderId)
    {
        try {
            if (!$orderId) {
                throw new \InvalidArgumentException('Unable to clear history Order id is missing');
            }
            $connection = $this->utilities->getResourceConnection()->getConnection();
            $table = $connection->getTableName('ecc_approval_order_history');
            $sql = "DELETE FROM $table WHERE order_id = $orderId";

            return $connection->query($sql);
        } catch (\Exception $e) {
            $this->approvalLogger->addError($e->getMessage());
        }

    }
}