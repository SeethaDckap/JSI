<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Dashboard;

use Epicor\OrderApproval\Model\GroupSave\Customers as groupCustomers;
use Epicor\OrderApproval\Model\HierarchyManagement;
use Epicor\OrderApproval\Model\GroupSave\Utilities as GroupUtilities;
use Epicor\OrderApproval\Logger\Logger;

class Management
{
    /**
     * @var groupCustomers
     */
    private $groupCustomers;

    /**
     * @var HierarchyManagement
     */
    private $hierarchyManagement;

    /**
     * @var GroupUtilities
     */
    private $utilities;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * Management constructor.
     * @param groupCustomers $groupCustomers
     * @param HierarchyManagement $hierarchyManagement
     * @param GroupUtilities $utilities
     * @param Logger $logger
     */
    public function __construct(
        groupCustomers $groupCustomers,
        HierarchyManagement $hierarchyManagement,
        GroupUtilities $utilities,
        Logger $logger
    ) {

        $this->groupCustomers = $groupCustomers;
        $this->hierarchyManagement = $hierarchyManagement;
        $this->utilities = $utilities;
        $this->logger = $logger;
    }

    /**
     * @return bool
     */
    public function canDisplayDashboard()
    {
        try {
            return $this->utilities->isOrderApprovalActive()
                && $this->groupCustomers->isMasterShopperB2B()
                && $this->isMemberOfParentGroup();
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage());
        }
    }

    /**
     * @return bool
     */
    private function isMemberOfParentGroup()
    {
        try {
            if ($approvalGroups = $this->getCustomerApprovalGroups()) {
                foreach ($approvalGroups as $group) {
                    $groupId = $group->getData('group_id');
                    $childIds = $this->hierarchyManagement->getChildrenCollection($groupId)->getAllIds();
                    if (count($childIds) > 0) {
                        return true;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage());
        }

        return false;
    }

    /**
     * @return \Epicor\OrderApproval\Model\ResourceModel\Groups\Collection|false
     */
    private function getCustomerApprovalGroups()
    {
        try {
            $erpAccountId = $this->groupCustomers->getCustomerAttribute('ecc_erpaccount_id');
            $customerId = $this->utilities->getCustomer()->getId();
            return $this->utilities->getGroupManagement()->getGroupByCustomer($customerId, $erpAccountId);
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage());
        }
    }
}