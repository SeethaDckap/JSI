<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Approval;

use Epicor\OrderApproval\Model\GroupManagement;
use Epicor\OrderApproval\Model\ResourceModel\OrderHistory\CollectionFactory as HistoryCollectionFactory;
use Epicor\OrderApproval\Model\GroupsRepository;
use Epicor\OrderApproval\Model\Groups as ApprovalGroups;

class VisibleHistoryState
{
    /**
     * @var GroupManagement
     */
    private $groupManagement;

    /**
     * @var HistoryCollectionFactory
     */
    private $historyCollectionFactory;

    /**
     * @var GroupsRepository
     */
    private $groupsRepository;

    /**
     * @var array
     */
    private $approvalGroupPath = [];

    /**
     * @var bool
     */
    private $groupFound = true;

    /**
     * @var \Epicor\OrderApproval\Api\Data\GroupsInterface|\Epicor\OrderApproval\Model\Groups
     */
    private $currentGroup;

    /**
     * VisibleHistoryState constructor.
     * @param GroupManagement $groupManagement
     * @param HistoryCollectionFactory $historyCollectionFactory
     * @param GroupsRepository $groupsRepository
     */
    public function __construct(
        GroupManagement $groupManagement,
        HistoryCollectionFactory $historyCollectionFactory,
        GroupsRepository $groupsRepository
    ){
        $this->groupManagement = $groupManagement;
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->groupsRepository = $groupsRepository;
    }

    /**
     * @param string $cusId
     * @param string $erpId
     * @return array
     */
    private function getCustomerGroupIds($cusId, $erpId)
    {
        $groupIds = [];
        $customerGroupIds = $this->groupManagement->getGroupByCustomer($cusId, $erpId);
        foreach ($customerGroupIds as $group) {
            $groupIds[] = $group->getGroupId();
        }

        return $groupIds;
    }

    /**
     * Gets the approval state that is visible to logged in approver this depends
     * on the groups they are member and to what point in the history the state is
     * visible, this is used to determine when self approved is approved, pending or rejected
     *
     * @param string $orderId
     * @param string $cusId
     * @param string $erpId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStateBasedOnSelfApproval($orderId, $cusId, $erpId)
    {
        $approvalPath = $this->getApprovalGroupPath($orderId);
        $customerGroups = $this->getCustomerGroupIds($cusId, $erpId);
        $groupsForCurrentApproval = array_intersect($approvalPath, $customerGroups);

        $historyForOrder = $this->getHistoryByOrderId($orderId);
        $approverOrderState = 'Approved';
        $finalStateSet = false;
        foreach($groupsForCurrentApproval as $groupId){
            $groupStateExists = false;
            foreach ($historyForOrder as $history){
                if($history->getGroupId() === $groupId && $this->isViewableState($history->getStatus())){
                    $approverOrderState = $history->getStatus();
                    $groupStateExists = true;
                    $finalStateSet = true;
                    break;
                }
                if($history->getGroupId() === $groupId && $this->isSelfApproved($history->getStatus())){
                    $groupStateExists = true;
                    break;
                }
            }
            if(!$groupStateExists){
                //Order is not yet available for approver in the current approval flow
                $approverOrderState = '';
                break;
            }
            if($finalStateSet){
                break;
            }
        }

        return $approverOrderState;

    }

    /**
     * @param string $state
     * @return bool
     */
    private function isViewableState($state)
    {
        return in_array($state, ['Pending', 'Rejected', 'Approved']);
    }

    /**
     * @param string $state
     * @return bool
     */
    private function isSelfApproved($state)
    {
        return in_array($state, ['Self Approved']);
    }

    /**
     * Gets all Group ids involved the approval process for an order, starts
     * with the first group to handle the order and all possible related
     *
     * parent groups
     * @param string $orderId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getApprovalGroupPath($orderId)
    {
        $firstGroupId = $this->getApprovalStartingGroupId($orderId);
        $this->currentGroup = $this->groupsRepository->getById($firstGroupId);
        if(!in_array($this->currentGroup->getGroupId(), $this->approvalGroupPath)){
            $this->approvalGroupPath[] = $this->currentGroup->getGroupId();
        }

        while ($this->groupFound) {
            $this->processNextGroup();
        }

        return $this->approvalGroupPath;
    }

    /**
     * @return void
     */
    private function processNextGroup()
    {
        if ($this->isGroupsActive()) {
            $parentGroup = $this->groupManagement->getParentGroup($this->currentGroup);
            $this->setNextGroup($parentGroup);
        } else {
            $this->groupFound = false;
        }
    }

    /**
     * @param ApprovalGroups $parentGroup
     */
    private function setNextGroup($parentGroup)
    {
        if ($this->isValidNextGroup($parentGroup)) {
            $this->currentGroup = $parentGroup;
            $this->approvalGroupPath[] = $this->currentGroup->getGroupId();
        } else {
            $this->groupFound = false;
        }
    }

    /**
     * @param ApprovalGroups $parentGroup
     * @return bool
     */
    private function isValidNextGroup($parentGroup)
    {
        return $parentGroup instanceof ApprovalGroups
            && ($parentGroup->getGroupId() !== $this->currentGroup->getGroupId());
    }

    /**
     * @return bool
     */
    private function isGroupsActive()
    {
        return $this->groupManagement->isMultiLevel($this->currentGroup)
            && $this->groupManagement->isGroupActive($this->currentGroup) && $this->groupManagement->isGroupEnable();
    }

    /**
     * @param string $orderId
     * @return mixed
     */
    private function getApprovalStartingGroupId($orderId)
    {
        $history = $this->getHistoryByOrderId($orderId);
        return $history->getFirstItem()->getGroupId();
    }

    /**
     * @param string $orderId
     * @return \Epicor\OrderApproval\Model\ResourceModel\OrderHistory\Collection#
     */
    private function getHistoryByOrderId($orderId)
    {
        $historyCollection = $this->historyCollectionFactory->create();
        $historyCollection->addFieldToFilter('order_id', ['eq' => $orderId]);

        return $historyCollection;
    }
}