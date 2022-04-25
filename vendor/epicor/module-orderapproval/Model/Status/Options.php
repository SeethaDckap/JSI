<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Status;

use http\Exception\InvalidArgumentException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface as SalesOrder;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Epicor\OrderApproval\Block\Html\Link\Current;
use Epicor\OrderApproval\Model\GroupSave\Utilities;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Epicor\OrderApproval\Model\Approval\HistoryStatus as ApprovalHistory;

class Options
{
    /**
     * Ecc GOR state for order not sent
     */
    const ECC_ORDER_NOT_SENT_GOR_STATE = '0';

    /**
     * Ecc GOR state for order sent
     */
    const ECC_ORDER_SENT_GOR_STATE = '1';

    /**
     * Ecc GOR state ERP error state
     */
    const ECC_ORDER_ERP_ERROR_GOR_STATE = '3';

    /**
     * Ecc GOR state for error retry failed state
     */
    const ECC_ORDER_ERROR_RETRY_FAILED_GOR_STATE = '4';

    /**
     * Ecc GOR state for order never sent
     */
    const ECC_ORDER_NEVER_SENT_GOR_STATE = '5';

    /**
     * Ecc GOR state for order approval when pending
     */
    const ECC_ORDER_APPROVAL_PENDING_GOR_STATE = '6';

    /**
     * Ecc GOR state for order approval when rejected
     */
    const ECC_ORDER_APPROVAL_REJECTED_GOR_STATE = '8';

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var MessageManager
     */
    private $messageManager;

    /**
     * @var Utilities
     */
    private $utilities;

    /**
     * @var int
     */
    private $orderId;

    /**
     * @var SalesOrder
     */
    private $order;

    /**
     * @var OrderResource
     */
    private $orderResource;

    /**
     * @var ApprovalHistory
     */
    private $approvalHistory;

    /**
     * Options constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param MessageManager $messageManager
     * @param Utilities $utilities
     * @param OrderResource $orderResource
     * @param ApprovalHistory $approvalHistory
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        MessageManager $messageManager,
        Utilities $utilities,
        OrderResource $orderResource,
        ApprovalHistory $approvalHistory
    ) {
        $this->orderRepository = $orderRepository;
        $this->messageManager = $messageManager;
        $this->utilities = $utilities;
        $this->orderResource = $orderResource;
        $this->approvalHistory = $approvalHistory;
    }

    /**
     * @param $stateOptions
     * @return mixed
     */
    public function setSelectOptions(&$stateOptions)
    {
        if (!$this->utilities->isOrderApprovalActive()) {
            return $stateOptions;
        }
        foreach ($this->getSelectOptions() as $state => $option) {
            $stateOptions[$state] = $option;
        }
    }

    /**
     * @return ApprovalHistory
     */
    public function getApprovalHistory()
    {
        return $this->approvalHistory;
    }

    /**
     * @param $orderId
     * @return \Magento\Sales\Model\Order
     */
    public function getOrderById($orderId)
    {
        if ($orderId) {
            return $this->orderRepository->get($orderId);
        }
    }

    /**
     * @return OrderResource
     */
    public function getOrderResource()
    {
        return $this->orderResource;
    }

    /**
     * @param $currentState
     * @return array|string[]
     */
    private function getAllowedStatusChanges($currentState)
    {
        $allowedStateChanges = $this->getAllowedStates();
        return $allowedStateChanges[$currentState] ?? [];
    }

    private function getAllowedStates()
    {
        return [
            self::ECC_ORDER_NOT_SENT_GOR_STATE => [
                self::ECC_ORDER_SENT_GOR_STATE,
                self::ECC_ORDER_ERP_ERROR_GOR_STATE,
                self::ECC_ORDER_ERROR_RETRY_FAILED_GOR_STATE,
                self::ECC_ORDER_NEVER_SENT_GOR_STATE
            ],
            self::ECC_ORDER_SENT_GOR_STATE => [
                self::ECC_ORDER_NOT_SENT_GOR_STATE,
                self::ECC_ORDER_ERP_ERROR_GOR_STATE,
                self::ECC_ORDER_ERROR_RETRY_FAILED_GOR_STATE,
                self::ECC_ORDER_NEVER_SENT_GOR_STATE
            ],
            self::ECC_ORDER_ERP_ERROR_GOR_STATE => [
                self::ECC_ORDER_NOT_SENT_GOR_STATE,
                self::ECC_ORDER_SENT_GOR_STATE,
                self::ECC_ORDER_ERROR_RETRY_FAILED_GOR_STATE,
                self::ECC_ORDER_NEVER_SENT_GOR_STATE
            ],
            self::ECC_ORDER_ERROR_RETRY_FAILED_GOR_STATE => [
                self::ECC_ORDER_NOT_SENT_GOR_STATE,
                self::ECC_ORDER_SENT_GOR_STATE,
                self::ECC_ORDER_ERP_ERROR_GOR_STATE,
                self::ECC_ORDER_NEVER_SENT_GOR_STATE
            ],
            self::ECC_ORDER_NEVER_SENT_GOR_STATE => [
                self::ECC_ORDER_NOT_SENT_GOR_STATE,
                self::ECC_ORDER_SENT_GOR_STATE,
                self::ECC_ORDER_ERP_ERROR_GOR_STATE,
                self::ECC_ORDER_ERROR_RETRY_FAILED_GOR_STATE,
                self::ECC_ORDER_APPROVAL_PENDING_GOR_STATE,
                self::ECC_ORDER_APPROVAL_REJECTED_GOR_STATE
            ],
            self::ECC_ORDER_APPROVAL_PENDING_GOR_STATE => [
                self::ECC_ORDER_APPROVAL_REJECTED_GOR_STATE,
                self::ECC_ORDER_ERP_ERROR_GOR_STATE,
                self::ECC_ORDER_ERROR_RETRY_FAILED_GOR_STATE,
                self::ECC_ORDER_NOT_SENT_GOR_STATE
            ],
            self::ECC_ORDER_APPROVAL_REJECTED_GOR_STATE => [
                self::ECC_ORDER_APPROVAL_PENDING_GOR_STATE,
            ],
        ];
    }

    /**
     * @return string[]
     */
    private function getRestrictedStates()
    {
        return [
            self::ECC_ORDER_APPROVAL_PENDING_GOR_STATE,
            self::ECC_ORDER_APPROVAL_REJECTED_GOR_STATE,
            self::ECC_ORDER_ERP_ERROR_GOR_STATE,
            self::ECC_ORDER_ERROR_RETRY_FAILED_GOR_STATE,
            self::ECC_ORDER_SENT_GOR_STATE,
            self::ECC_ORDER_NEVER_SENT_GOR_STATE,
            self::ECC_ORDER_NOT_SENT_GOR_STATE
        ];
    }

    /**
     * @param $orderId
     * @param $stateChange
     * @return bool
     */
    public function isOrderApprovalRestrictedStateChange($orderId, $stateChange)
    {
        $this->orderId = $orderId;
        if (!$this->utilities->isOrderApprovalActive()) {
            return false;
        }

        if ($this->isInOrderApprovalStatus() && !$this->isAllowedStateChange($stateChange)) {
            $message = 'Status can not be changed to state: "'
                . $this->getStatusDescription($stateChange) . '" when ' . $this->getStatusDescription();
            $this->messageManager->addErrorMessage($message);
            return true;
        }
        return false;
    }

    /**
     * @return string[]
     */
    public static function stateDescriptions()
    {
        return [
            self::ECC_ORDER_ERROR_RETRY_FAILED_GOR_STATE => 'Error - Retry Attempt Failure',
            self::ECC_ORDER_ERP_ERROR_GOR_STATE => 'Erp Error',
            self::ECC_ORDER_APPROVAL_PENDING_GOR_STATE => 'Order Pending Approval',
            self::ECC_ORDER_APPROVAL_REJECTED_GOR_STATE => 'Order Rejected',
            self::ECC_ORDER_NEVER_SENT_GOR_STATE => 'Order Never Send',
            self::ECC_ORDER_NOT_SENT_GOR_STATE => 'Order Not Sent',
            self::ECC_ORDER_SENT_GOR_STATE => 'Order Sent'
        ];
    }

    /**
     * @param $gorState
     * @return string
     */
    public static function getGorMessageDescription($gorState)
    {
        $stateMessages = self::stateDescriptions();

        return $stateMessages[$gorState] ?? '';
    }

    /**
     * @param $order
     */
    public function resetApprovalProcess($order)
    {
        $this->approvalHistory->resetApprovalProcess($order);
    }

    /**
     * @return string[]
     */
    private function getSelectOptions()
    {
        return [
            self::ECC_ORDER_APPROVAL_PENDING_GOR_STATE => 'Order Pending Approval',
            self::ECC_ORDER_APPROVAL_REJECTED_GOR_STATE => 'Order Rejected'
        ];
    }

    /**
     * @param $orderId
     * @return bool
     */
    private function isInOrderApprovalStatus()
    {
        return in_array($this->getCurrentOrderGorStatus(), $this->getRestrictedStates());
    }

    /**
     * @param null $status
     * @return string
     */
    private function getStatusDescription($status = null)
    {
        $descriptions = $this->stateDescriptions();
        if ($status === null) {
            $status = $this->getCurrentOrderGorStatus();
        }

        return $descriptions[$status] ?? '';
    }

    /**
     * @return SalesOrder
     */
    private function getCurrentOrder()
    {
        if (!$this->order && $this->orderId) {
            $this->order = $this->orderRepository->get($this->orderId);
        }
        return $this->order;
    }

    /**
     * @return mixed
     */
    private function getCurrentOrderGorStatus()
    {
        try {
            $currentOrder = $this->getCurrentOrder();
            if ($currentOrder instanceof SalesOrder) {
                return $currentOrder->getData('ecc_gor_sent');
            } else {
                throw new InvalidArgumentException('Order Not set while changing status');
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }

    /**
     * @param $stateChange
     * @return bool
     */
    private function isAllowedStateChange($stateChange)
    {
        $currentState = $this->getCurrentOrderGorStatus();
        if (!is_numeric($currentState)) {
            return false;
        }
        $allowedStatusChanges = $this->getAllowedStatusChanges($currentState);

        return in_array($stateChange, $allowedStatusChanges);
    }
}
