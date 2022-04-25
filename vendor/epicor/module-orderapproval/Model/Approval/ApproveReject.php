<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Approval;

use Epicor\OrderApproval\Model\Groups;
use Epicor\OrderApproval\Model\Status\Options as StatusOptions;
use Magento\Sales\Model\Order as SalesOrder;
use Magento\Framework\App\ResourceConnection;
use Epicor\OrderApproval\Model\GroupManagement;
use Epicor\OrderApproval\Model\ResourceModel\OrderHistory\CollectionFactory as HistoryCollectionFactory;
use Epicor\OrderApproval\Model\ResourceModel\OrderHistory\Collection as HistoryCollection;
use Epicor\OrderApproval\Model\OrderHistoryManagement;
use Epicor\OrderApproval\Model\HierarchyManagement;
use Epicor\OrderApproval\Model\GroupSave\Utilities as GroupUtilities;
use Epicor\OrderApproval\Model\GroupSave\ErpAccount as GroupErpAccount;
use Epicor\OrderApproval\Model\ResourceModel\Groups\Collection as GroupCollection;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;

class ApproveReject
{
    /**
     * @var $group Groups
     */
    private $group;

    /**
     * @var $order SalesOrder
     */
    private $order;

    /**
     * @var HistoryCollectionFactory
     */
    private $historyCollectionFactory;

    /**
     * @var HierarchyManagement
     */
    private $hierarchyManagement;

    /**
     * @var GroupUtilities
     */
    private $groupUtilities;

    /**
     * @var GroupErpAccount
     */
    private $groupErpAccount;

    /**
     * @var bool
     */
    private $hasErrors = false;

    /**
     * @var MessageManager
     */
    private $messageManager;

    /**
     * @var OrderResource
     */
    private $orderResource;

    /**
     * ApproveReject constructor.
     * @param HistoryCollectionFactory $historyCollectionFactory
     * @param HierarchyManagement $hierarchyManagement
     * @param GroupUtilities $groupUtilities
     * @param GroupErpAccount $groupErpAccount
     * @param MessageManager $messageManager
     * @param OrderResource $orderResource
     */
    public function __construct(
        HistoryCollectionFactory $historyCollectionFactory,
        HierarchyManagement $hierarchyManagement,
        GroupUtilities $groupUtilities,
        GroupErpAccount $groupErpAccount,
        MessageManager $messageManager,
        OrderResource $orderResource
    ) {
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->hierarchyManagement = $hierarchyManagement;
        $this->groupUtilities = $groupUtilities;
        $this->groupErpAccount = $groupErpAccount;
        $this->messageManager = $messageManager;
        $this->orderResource = $orderResource;
    }

    /**
     * @param $group
     * @param null $order
     */
    public function approveOrder($group, $order = null)
    {
        try {
            $this->group = $group;
            if ($order) {
                $this->order = $order;
                $originalHistoryRecord = $this->getCurrentHistory()->getFirstItem();
                if (!$originalHistoryRecord) {
                    $this->hasErrors = true;
                    throw new \InvalidArgumentException('Error history record missing');
                }
            }

            if (!$this->group->getIsActive()) {
                $this->setHistoryStatus(GroupManagement::STATUS_SKIPPED);
                $this->approveParent();
            } else {
                $this->checkApprovalCustomerGroup();
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return $this->hasErrors;
    }

    /**
     * @param $group
     * @param null $order
     */
    public function rejectOrder($group, $order = null)
    {
        $this->group = $group;
        $this->order = $order;
        $gorSentState = StatusOptions::ECC_ORDER_APPROVAL_REJECTED_GOR_STATE;
        $message = StatusOptions::getGorMessageDescription($gorSentState);
        $this->order->setData('is_approval_pending', 2);
        $this->order->setData('ecc_gor_sent', $gorSentState);
        $this->order->setData('ecc_gor_message', $message);
        $this->saveOrderAttributes();
        $this->setHistoryStatus(GroupManagement::STATUS_REJECTED);
        $this->groupUtilities->getEmailSenders()->getRejectSender()->send($order);
    }

    /**
     * @return void
     */
    private function checkApprovalCustomerGroup()
    {
        if ($this->isCustomerInGroup()) {
            $this->checkApprovalByCustomer();
        } else {
            $this->checkApprovalMultiLevel();
        }
    }

    /**
     * @return void
     */
    private function checkApprovalByCustomer()
    {
        if ($this->isOrderTotalBelowRuleTotal()) {
            $this->setOrderApproved();
        } else {
            $this->setSelfApprovedStatus();
            $this->approveParent();
        }
    }

    /**
     * @return void
     */
    private function checkApprovalMultiLevel()
    {
        $this->group;
        if ($this->isMultiLevel()) {
            $this->setPendingState();
        } else {
            $this->checkRuleMultiLevel();
        }
    }

    /**
     * @return void
     */
    private function checkRuleMultiLevel()
    {
        $this->group;
        if ($this->isOrderTotalBelowRuleTotal()) {
            $this->setPendingState();
        } else {
            $this->setHistoryStatus(GroupManagement::STATUS_SKIPPED);
            $this->approveParent();
        }
    }

    /**
     * @return bool
     */
    private function isMultiLevel()
    {
        return (boolean)$this->group->getIsMultiLevel();
    }

    /**
     * @return void
     */
    private function setPendingState()
    {
        $this->setHistoryStatus(GroupManagement::STATUS_PENDING);
        $this->groupUtilities->getEmailSenders()->getApproverSender()->send($this->order);
    }

    /**
     * @throws \Exception
     * @return void
     */
    private function setOrderApproved()
    {
        $this->setHistoryStatus(GroupManagement::STATUS_APPROVED);

        $gorSentState = StatusOptions::ECC_ORDER_NOT_SENT_GOR_STATE;
        $message = StatusOptions::getGorMessageDescription($gorSentState);
        $this->order->setData('is_approval_pending', 0);
        $this->order->setData('ecc_gor_sent', $gorSentState);
        $this->order->setData('ecc_gor_message', $message);
        $this->saveOrderAttributes();
        $this->groupUtilities->getEmailSenders()->getApprovedSender()->send($this->order);
    }

    /**
     * @throws \Exception
     * @return void
     */
    private function saveOrderAttributes()
    {
        $this->orderResource->saveAttribute($this->order, 'is_approval_pending');
        $this->orderResource->saveAttribute($this->order, 'ecc_gor_sent');
        $this->orderResource->saveAttribute($this->order, 'ecc_gor_message');
    }

    /**
     * @return bool
     */
    private function isOrderTotalBelowRuleTotal()
    {
        $ruleTotal = $this->groupUtilities->getGroupManagement()->getRuleTotal($this->group);

        return $this->order->getGrandTotal() <= $ruleTotal;
    }

    /**
     * @return bool
     */
    private function isCustomerInGroup()
    {
        $groupByCustomer = $this->groupErpAccount->getMasterShopperApprovalGroups();

        return in_array($this->group->getGroupId(), $groupByCustomer);
    }

    /**
     * @return void
     */
    private function approveParent()
    {
        try {
            $parentGroup = $this->getParentGroup();
            if (!$parentGroup) {
                $this->setHistoryStatus(GroupManagement::STATUS_PENDING);
                $this->hasErrors = true;
                throw new \InvalidArgumentException(
                    'Unable to approve order: ' . $this->order->getEntityId() . ' from Group: '
                    . $this->group->getGroupId() . ' parent group is missing'
                );
            }
            $this->approveOrder($parentGroup);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }

    /**
     * @param $status
     */
    private function setHistoryStatus($status)
    {
        $historyCollection = $this->getCurrentHistory();
        $history = $historyCollection->getFirstItem();
        $historyId = $history->getData('id');
        if ($historyId) {
            $data = ['status' => $status];
            $historyCollection->getConnection()->update('ecc_approval_order_history', $data, 'id = ' . $historyId);
        } else {
            $history = $this->getRecentHistoryForOrder();

            $insertData = [
                'order_id' => $history->getData('order_id'),
                'group_id' => $this->group->getGroupId(),
                'child_group_id' => $history->getData('group_id'),
                'customer_id' => $history->getData('customer_id'),
                'status' => $status,
                'rules' => $history->getData('rules')
            ];
            $historyCollection->getConnection()->insert('ecc_approval_order_history', $insertData);
        }
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    private function getRecentHistoryForOrder()
    {
        $orderId = $this->order->getEntityId();
        $historyCollection = $this->historyCollectionFactory->create();
        $historyCollection->addFieldToFilter('order_id', ['eq' => $orderId]);
        $historyCollection->setOrder('id', 'DESC');

        return $historyCollection->getFirstItem();
    }

    /**
     * @return HistoryCollection
     */
    private function getCurrentHistory()
    {
        $orderId = $this->order->getEntityId();
        $groupId = $this->group->getGroupId();
        $historyCollection = $this->historyCollectionFactory->create();
        $historyCollection->addFieldToFilter('order_id', ['eq' => $orderId]);
        $historyCollection->addFieldToFilter('group_id', ['eq' => $groupId]);

        return $historyCollection;
    }

    /**
     * @return void
     */
    private function setSelfApprovedStatus()
    {
        $this->setHistoryStatus(GroupManagement::STATUS_SELF_APPROVED);
    }

    /**
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface|Groups
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getParentGroup()
    {
        $groupParentCollection = $this->hierarchyManagement->getParentCollection($this->group->getGroupId());
        if ($groupParentCollection instanceof GroupCollection) {
            $parentId = $groupParentCollection->getFirstItem()->getData('parent_group_id');
            if ($parentId) {
                return $this->groupUtilities->getGroupsRepository()->getById($parentId);
            }
        }
    }
}
