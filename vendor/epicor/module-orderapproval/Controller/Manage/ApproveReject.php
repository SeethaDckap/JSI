<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Controller\Manage;

use http\Exception\InvalidArgumentException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Epicor\AccessRight\Controller\Action as AccessRightController;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface as OrderCollectionFactory;
use Epicor\OrderApproval\Model\Status\Options as StatusOptions;
use Epicor\OrderApproval\Model\ResourceModel\OrderHistory\CollectionFactory as ApprovalHistoryFactory;
use Magento\Framework\App\ResourceConnection;
use Epicor\OrderApproval\Model\GroupManagement;
use Epicor\OrderApproval\Model\GroupsRepository;
use Epicor\OrderApproval\Model\Approval\ApproveReject as OrderApprovalApproveReject;
use Epicor\OrderApproval\Model\GroupSave\Utilities as GroupUtilities;

class ApproveReject extends AccessRightController
{
    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var ApprovalHistoryFactory
     */
    private $approvalHistoryFactory;

    /**
     * @var GroupManagement
     */
    private $groupManagement;

    /**
     * @var GroupsRepository
     */
    private $groupsRepository;
    
    /**
     * @var OrderApprovalApproveReject
     */
    private $approveReject;

    /**
     * @var []
     */
    private $approvedOrderIds;

    /**
     * @var []
     */
    private $rejectedOrderIds;

    /**
     * @var GroupUtilities
     */
    private $utilities;

    /**
     * @var mixed
     */
    private $orderId;

    /**
     * @var mixed
     */
    private $approvalStatus;

    /**
     * @var string
     */
    private $ordersList = '';

    /**
     * ApproveReject constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param ApprovalHistoryFactory $approvalHistoryFactory
     * @param ResourceConnection $resourceConnection
     * @param GroupManagement $groupManagement
     * @param GroupsRepository $groupsRepository
     * @param OrderApprovalApproveReject $approveReject
     * @param GroupUtilities $utilities
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        OrderCollectionFactory $orderCollectionFactory,
        ApprovalHistoryFactory $approvalHistoryFactory,
        GroupManagement $groupManagement,
        GroupsRepository $groupsRepository,
        OrderApprovalApproveReject $approveReject,
        GroupUtilities $utilities
    ) {
        parent::__construct($context);
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->approvalHistoryFactory = $approvalHistoryFactory;
        $this->groupManagement = $groupManagement;
        $this->groupsRepository = $groupsRepository;
        $this->approveReject = $approveReject;
        $this->utilities = $utilities;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $approvalsPath = '*/*/approvals';
        if (!$this->isValidOrderIds()) {
            $this->_redirect($approvalsPath);
        }

        if($this->isSingleApproveReject()){
            $this->singleApproveReject();
            return $this->_redirect($approvalsPath);
        }else{
            $this->massApproveReject();
        }

        $this->_redirect($approvalsPath);
    }

    /**
     * @return void
     */
    private function massApproveReject()
    {
        $this->approveOrders();
        $this->rejectOrders();
    }

    /**
     * @return void
     */
    private function singleApproveReject()
    {
        if ($this->approvalStatus === 'approved') {
            $this->approveOrders([$this->getOrderId()]);
        }
        if ($this->approvalStatus === 'rejected') {
            $this->rejectOrders([$this->getOrderId()]);
        }
    }

    /**
     * @return bool
     */
    private function isSingleApproveReject()
    {
        $this->approvalStatus = $this->getRequest()->getParam('approval_status');
        return $this->getOrderId() && $this->approvalStatus;
    }

    /**
     * @return mixed
     */
    private function getOrderId()
    {
        if (!$this->orderId) {
            $this->orderId = $this->getRequest()->getParam('order_id');
        }

        return $this->orderId;
    }

    /**
     * @param null $ids
     */
    private function approveOrders($ids = null)
    {
        if (!$ids) {
            $ids = $this->getOrderIdsByType('approved');
        }
        try {
            if (!is_array($ids)) {
                throw new InvalidArgumentException('Error updating approval state: approved');
            }
            $orderCollection = $this->orderCollectionFactory->create();
            $orderCollection->addFieldToFilter('entity_id', ['in' => $ids]);
            $this->ordersList = '';
            foreach ($orderCollection as $order) {
                $group = $this->utilities->getGroupFromHistory($order->getData('entity_id'));
                $this->approveReject->approveOrder($group, $order);
                $this->addToOrdersList($order);
            }
            if ($this->ordersList) {
                $this->messageManager->addSuccessMessage('Status approved for orders: '.$this->ordersList );
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }

    /**
     * @param $order
     */
    private function addToOrdersList($order)
    {
        if (!$this->ordersList) {
            $this->ordersList .= $order->getData('increment_id');
        } else {
            $this->ordersList .= ', ' . $order->getData('increment_id');
        }
    }

    /**
     * @param null $ids
     */
    private function rejectOrders($ids = null)
    {
        if (!$ids) {
            $ids = $this->getOrderIdsByType('rejected');
        }

        try {
            if (!is_array($ids)) {
                throw new InvalidArgumentException('Error updating approval state: rejected');
            }
            $orderCollection = $this->orderCollectionFactory->create();
            $orderCollection->addFieldToFilter('entity_id', ['in' => $ids]);
            $this->ordersList = '';
            foreach ($orderCollection as $order) {
                $group = $this->utilities->getGroupFromHistory($order->getData('entity_id'));
                $this->approveReject->rejectOrder($group, $order);
                $this->addToOrdersList($order);
            }
            if ($this->ordersList) {
                $this->messageManager->addSuccessMessage('Status rejected for orders: ' . $this->ordersList);
            }

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }

    /**
     * @param $type
     * @return array|mixed
     */
    private function getOrderIdsByType($type)
    {
        $ids = [];
        if (!in_array($type, ['approved', 'rejected'])) {
            return $ids;
        }
        $approvedParam = $this->getRequest()->getParam($type);
        if ($approvedParam) {
            $approvedData = json_decode($approvedParam, true);
            $ids = $approvedData[$type] ?? [];

        }

        return $ids;
    }

    /**
     * @return bool
     */
    private function isValidOrderIds()
    {
        if (!$this->isUniqueApproveRejectIds()) {
            $duplicateIds = '';
            foreach ($this->getCompareApproveReject() as $id) {
                if (!$duplicateIds) {
                    $duplicateIds .= $id;
                } else {
                    $duplicateIds .= ',' . $id;
                }
            }
            $this->messageManager
                ->addErrorMessage('Unable to change status duplicate state for orders: ' . $duplicateIds);
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    private function isUniqueApproveRejectIds()
    {
        return empty($this->getCompareApproveReject());
    }

    /**
     * @return array|mixed
     */
    private function getCompareApproveReject()
    {
        return array_intersect($this->getApprovedOrderIds(), $this->getRejectedOrderIds());
    }

    /**
     * @return array|mixed
     */
    private function getApprovedOrderIds()
    {
        if (!$this->approvedOrderIds) {
            $this->approvedOrderIds = $this->getOrderIdsByType('approved');
        }

        return $this->approvedOrderIds;
    }

    /**
     * @return array|mixed
     */
    private function getRejectedOrderIds()
    {
        if (!$this->rejectedOrderIds) {
            $this->rejectedOrderIds = $this->getOrderIdsByType('rejected');
        }

        return $this->rejectedOrderIds;
    }
}
