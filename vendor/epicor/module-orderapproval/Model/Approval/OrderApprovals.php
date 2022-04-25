<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Approval;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface as OrdersCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Epicor\OrderApproval\Model\GroupSave\ErpAccount as GroupErpAccount;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Epicor\OrderApproval\Model\GroupSave\Utilities as GroupUtilities;

class OrderApprovals
{
    /**
     * @var OrdersCollectionFactory
     */
    private $ordersCollectionFactory;

    /**
     * @var GroupErpAccount
     */
    private $groupErpAccount;

    /**
     * @var OrderCollection
     */
    private $orderCollection;

    /**
     * @var string
     */
    private $historyStatusField = 'aoh.status';

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Order
     */
    private $currentOrder;

    /**
     * @var GroupUtilities
     */
    private $groupUtilities;

    /**
     * OrderApprovals constructor.
     * @param OrdersCollectionFactory $ordersCollectionFactory
     * @param GroupErpAccount $groupErpAccount
     * @param OrderRepositoryInterface $orderRepository
     * @param GroupUtilities $groupUtilities
     */
    public function __construct(
        OrdersCollectionFactory $ordersCollectionFactory,
        GroupErpAccount $groupErpAccount,
        OrderRepositoryInterface $orderRepository,
        GroupUtilities $groupUtilities
    ) {
        $this->ordersCollectionFactory = $ordersCollectionFactory;
        $this->groupErpAccount = $groupErpAccount;
        $this->orderRepository = $orderRepository;
        $this->groupUtilities = $groupUtilities;
    }

    /**
     * @return array
     */
    public function getApprovalOrderIds()
    {
        $this->setApprovalOrdersCollection();
        return $this->orderCollection->getAllIds();
    }

    /**
     * @param $historyId
     * @return array
     */
    public function getHistoryItem($historyId)
    {
        $connection = $this->groupUtilities->getResourceConnection()->getConnection();
        $table = $connection->getTableName('ecc_approval_order_history');
        $sql = "SELECT * FROM $table WHERE id = $historyId";

        return $connection->fetchRow($sql);
    }

    /**
     * @return void
     */
    private function setApprovalOrdersCollection()
    {
        /** @var OrderCollection $orderCollection */
        $this->orderCollection = $this->ordersCollectionFactory->create();
        $this->orderCollection->addFieldToSelect('entity_id');
        $this->orderCollection->addFieldToSelect('is_approval_pending');
        $this->buildApprovalOrderCollection($this->orderCollection);
    }

    /**
     * @param null|array $summaryFilter
     * @param bool $useSelfApprovedFilter
     * @return OrderCollection
     */
    public function getApprovalOrdersCollection($summaryFilter = null, $useSelfApprovedFilter = true)
    {
        /** @var OrderCollection $orderCollection */
        $this->orderCollection = $this->ordersCollectionFactory->create();
        $this->orderCollection->addFieldToSelect('*');
        $this->buildApprovalOrderCollection($this->orderCollection, $useSelfApprovedFilter);
        if($summaryFilter){
            $this->orderCollection->addFieldToFilter('entity_id', ['in', $summaryFilter]);
        }

        return $this->orderCollection;
    }

    /**
     * @param $orderId
     * @return float|mixed|null
     */
    public function getIsOrderApprovalPending($orderId)
    {
        $order = $this->getOrder($orderId);
        if ($order instanceof Order) {
            return $order->getData('is_approval_pending');
        }
    }

    /**
     * @param $orderId
     * @return bool
     */
    public function isOrderPendingApproval($orderId)
    {
        return $this->getIsOrderApprovalPending($orderId) === '1';
    }

    /**
     * @param $orderId
     * @return bool
     */
    public function isOrderApproved($orderId)
    {
        return $this->getIsOrderApprovalPending($orderId) === '0';
    }

    /**
     * @param $orderId
     * @return bool
     */
    public function isOrderRejected($orderId)
    {
        return $this->getIsOrderApprovalPending($orderId) === '2';
    }

    /**
     * @param $orderId
     * @return array|mixed|null
     */
    public function getHistoryIdByOrderId($orderId)
    {
        $approvalsCollection = $this->getApprovalOrdersCollection(null, false);
        $approvalsCollection->addFieldToFilter('entity_id', ['eq' => $orderId]);

        return $approvalsCollection->getFirstItem()->getData('history_id');
    }

    /**
     * @param $orderId
     * @return string
     */
    public function getOrderSubmitterFullName($orderId)
    {
        $order = $this->getOrder($orderId);
        if ($order instanceof Order) {
            return $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
        }
    }

    /**
     * @param $orderId
     * @return false|\Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder($orderId)
    {
        if (!$orderId) {
            return false;
        }

        if (!$this->currentOrder) {
            $this->currentOrder = $this->orderRepository->get($orderId);
        }

        return $this->currentOrder;
    }

    /**
     * @param OrderCollection $collection
     * @param bool $useSelfApprovedFilter
     */
    private function buildApprovalOrderCollection($collection, $useSelfApprovedFilter = true)
    {
        $this->joinHistoryTable($collection);
        $this->joinShippingAddresses($collection);
        $this->addStatusFilter($collection, $useSelfApprovedFilter);
        $this->addHistoryCustomerGroupIdsFilter($collection);
        $this->addRequestorFilter($collection);
    }

    /**
     * @param OrderCollection $collection
     * @return void
     */
    public function joinHistoryTable($collection, $addSelectors = true)
    {
        $selectors = $addSelectors
            ? ['history_status' => $this->historyStatusField, 'history_id' => 'aoh.id' ,'aoh.*'] : [];
        $collection->getSelect()->joinLeft(
            ['aoh' => 'ecc_approval_order_history'],
            'aoh.order_id = main_table.entity_id',
            $selectors
        );
    }

    /**
     * @param OrderCollection $collection
     * @return void
     */
    public function joinShippingAddresses($collection, $addSelectors = true)
    {
        $selectors = $addSelectors ? ['s_firstname' => 'soa.firstname', 's_lastname' => 'soa.lastname'] : [];
        $subQuery = new \Zend_Db_Expr('(select * from sales_order_address where address_type = "shipping" )');
        $collection->getSelect()->joinLeft(
            ['soa' => $subQuery],
            'main_table.entity_id = soa.parent_id',
            $selectors
        );
    }

    /**
     * @param OrderCollection $collection
     * @param bool $useSelfApprovedFilter
     * @return void
     */
    public function addStatusFilter($collection, $useSelfApprovedFilter = true)
    {
        $filter = $useSelfApprovedFilter
            ? ['Pending', 'Rejected', 'Approved', 'Self Approved'] : ['Pending', 'Rejected', 'Approved'];
        $collection->addFieldToFilter(
            $this->historyStatusField,
            ['in' => $filter]
        );
    }

    /**
     * @param $collection
     * @return void
     */
    public function addHistoryCustomerGroupIdsFilter($collection)
    {
        $collection->addFieldToFilter(
            'aoh.group_id',
            ['in' => $this->getCustomerGroupIds()]
        );
    }

    /**
     * @param $collection
     * @return void
     */
    public function addRequestorFilter($collection)
    {
        $collection->addFieldToFilter(
            'main_table.customer_email',
            ['neq' => $this->groupUtilities->getCustomer()->getEmail()]
        );
    }

    /**
     * @return array|false|string
     */
    public function getCustomerGroupIds()
    {
        return $this->groupErpAccount->getMasterShopperApprovalGroups();
    }
}
