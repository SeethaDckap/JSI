<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Approval;

use Magento\Customer\Model\Session as CustomerSession;
use Epicor\OrderApproval\Model\GroupManagement;
use Epicor\OrderApproval\Model\ResourceModel\Groups\Collection as GroupsCollection;
use Magento\Framework\App\ResourceConnection;
use Epicor\OrderApproval\Model\GroupsRepository;
use Magento\Sales\Api\OrderRepositoryInterface as OrderRepository;

class GroupApproval
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var GroupsCollection
     */
    private $groupsCollection;

    /**
     * @var GroupManagement
     */
    private $groupManagement;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var GroupsRepository
     */
    private $groupsRepository;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * GroupApproval constructor.
     * @param CustomerSession $customerSession
     * @param GroupManagement $groupManagement
     * @param GroupsCollection $groupsCollection
     * @param ResourceConnection $resourceConnection
     * @param GroupsRepository $groupsRepository
     * @param OrderRepository $orderRepository
     */
    public function __construct(
        CustomerSession $customerSession,
        GroupManagement $groupManagement,
        GroupsCollection $groupsCollection,
        ResourceConnection $resourceConnection,
        GroupsRepository $groupsRepository,
        OrderRepository $orderRepository
    ) {
        $this->customerSession = $customerSession;
        $this->groupsCollection = $groupsCollection;
        $this->groupManagement = $groupManagement;
        $this->resourceConnection = $resourceConnection;
        $this->groupsRepository = $groupsRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @return CustomerSession
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * @return GroupManagement
     */
    public function getGroupManagement()
    {
        return $this->groupManagement;
    }

    /**
     * @return GroupsCollection
     */
    public function getGroupsCollection()
    {
        return $this->groupsCollection;
    }

    /**
     * @return ResourceConnection
     */
    public function getResourceConnection()
    {
        return $this->resourceConnection;
    }

    /**
     * @return OrderRepository
     */
    public function getOrderRepository()
    {
        return $this->orderRepository;
    }
}
