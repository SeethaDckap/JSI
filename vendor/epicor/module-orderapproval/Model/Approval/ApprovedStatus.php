<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Approval;


use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface as OrdersCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Epicor\OrderApproval\Model\Approval\OrderApprovals;
use Epicor\OrderApproval\Model\GroupSave\Utilities as GroupUtilities;
use Epicor\OrderApproval\Model\Approval\VisibleHistoryState;

class ApprovedStatus
{
    const TMP_APPROVAL_HISTORY_TBL = 'tmp_tbl_approval_approved_status';

    /**
     * @var OrdersCollectionFactory
     */
    private $ordersCollectionFactory;

    /**
     * @var \Epicor\OrderApproval\Model\Approval\OrderApprovals
     */
    private $orderApprovals;

    /**
     * @var GroupUtilities
     */
    private $utilities;

    /**
     * @var OrderCollection|null
     */
    private $approvalHistoryCollection;

    /**
     * @var string
     */
    private $approvedStatusTableData;

    /**
     * @var bool
     */
    private $tmpTableBuilt = false;

    /** @var VisibleHistoryState  */
    private  $visibleHistoryState;

    /**
     * ApprovedStatus constructor.
     * @param OrdersCollectionFactory $ordersCollectionFactory
     * @param \Epicor\OrderApproval\Model\Approval\OrderApprovals $orderApprovals
     * @param GroupUtilities $utilities
     * @param \Epicor\OrderApproval\Model\Approval\VisibleHistoryState $visibleHistoryState
     */
    public function __construct(
        OrdersCollectionFactory $ordersCollectionFactory,
        OrderApprovals $orderApprovals,
        GroupUtilities $utilities,
        VisibleHistoryState $visibleHistoryState
    ){
        $this->ordersCollectionFactory = $ordersCollectionFactory;
        $this->orderApprovals = $orderApprovals;
        $this->utilities = $utilities;
        $this->visibleHistoryState = $visibleHistoryState;
    }

    /**
     * @return array
     */
    public function getHistoryOrderIds()
    {
        /** @var OrderCollection $orderCollection */
        $orderCollection = $this->ordersCollectionFactory->create();
        $orderCollection->addFieldToSelect('entity_id');

        $this->orderApprovals->joinHistoryTable($orderCollection,false);
        $this->orderApprovals->joinShippingAddresses($orderCollection, false);
        $this->orderApprovals->addHistoryCustomerGroupIdsFilter($orderCollection);
        $this->orderApprovals->addRequestorFilter($orderCollection);

        $orderCollection->getSelect()->distinct(true);

        return $orderCollection->getAllIds();
    }

    /**
     * @return OrderCollection
     */
    public function getApprovalHistoryCollection()
    {
        $this->buildApprovalHistoryCollection();

        return $this->approvalHistoryCollection;
    }

    /**
     * @return void
     */
    private function buildApprovalHistoryCollection()
    {
        $this->approvalHistoryCollection = $this->ordersCollectionFactory->create();
        $this->approvalHistoryCollection->addFieldToSelect('*');
        $this->joinConvertedHistoryTable();
        $this->orderApprovals->joinShippingAddresses($this->approvalHistoryCollection);

        $this->addHistoryCustomerGroupIdsFilter();
        $this->orderApprovals->addRequestorFilter($this->approvalHistoryCollection);
    }

    /**
     * @return void
     */
    private function createTemporaryTable()
    {
        $createTempTableSql = "CREATE TEMPORARY TABLE IF NOT EXISTS " . self::TMP_APPROVAL_HISTORY_TBL ." (
                                   `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Incremental ID',
                                   `history_id` int(10) unsigned NOT NULL,
                                   `status` varchar(30) NOT NULL,
                                   `order_id` int(10) unsigned NOT NULL,
                                   `group_id` int(10) unsigned NOT NULL,
                                   `child_group_id` int(10) unsigned NOT NULL DEFAULT '0',
                                   `customer_id` int(10) unsigned NOT NULL,
                                    PRIMARY KEY (`id`)
                               );";
        $connection = $this->utilities->getResourceConnection()->getConnection();
        $connection->query($createTempTableSql);
    }

    /**
     * @return void
     */
    private function addHistoryCustomerGroupIdsFilter()
    {
        if($this->approvalHistoryCollection){
            $this->approvalHistoryCollection->addFieldToFilter(
                'tmp.group_id',
                ['in' => $this->orderApprovals->getCustomerGroupIds()]
            );
        }
    }

    /**
     * @return void
     */
    private function joinConvertedHistoryTable()
    {
        if(!$this->tmpTableBuilt){
            $this->buildTempData();
        }

        $this->joinTempApprovedTable();
    }

    /**
     * @param OrderCollection $collection
     */
    private function joinTempApprovedTable()
    {
        if($this->approvalHistoryCollection){
            $this->approvalHistoryCollection->getSelect()->joinLeft(
                ['tmp' => 'tmp_tbl_approval_approved_status'],
                'main_table.entity_id = tmp.order_id',
                ['approved_status' => 'tmp.status', 'history_id' => 'tmp.history_id' ,'tmp.*']
            );
        }
    }

    /**
     * @param $orderId
     * @return array|mixed|null
     */
    public function getHistoryIdByOrderId($orderId)
    {
        $approvalsCollection = $this->getApprovalHistoryCollection();
        $approvalsCollection->addFieldToFilter('entity_id', ['eq' => $orderId]);

        $data = $approvalsCollection->getSelectSql(true);

        $connection = $this->utilities->getResourceConnection()->getConnection();
        $result = $connection->query($data)->fetchAll();

        return $result[0]['history_id']??'';
    }

    /**
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    private function getTemporaryApprovedStatusTableData()
    {
        $updatedApprovedData = [];
        $approverId = $this->utilities->getCustomerId();
        $erpAccountId = $this->utilities->getCustomerErpAccountId();
        foreach($this->getHistoryOrderIds() as $id){
            $orderHistory = $this->getHistoryData($id);
            foreach($orderHistory as $historyData){
                $historyId = $historyData['id']??'';
                $orderId = $historyData['order_id']??'';
                $groupId = $historyData['group_id']??'';
                $childGroupId = $historyData['child_group_id']??'';
                $customerId = $historyData['customer_id']??'';

                $visibleState = $this->visibleHistoryState
                    ->getStateBasedOnSelfApproval($orderId, $approverId, $erpAccountId);
                if ($visibleState) {
                    $updatedApprovedData[$id] = '("'
                        . $historyId . '","' . $visibleState . '","' . $orderId . '","'
                        . $groupId . '","' . $childGroupId . '","' . $customerId . '")';
                }
            }
        }

        return $updatedApprovedData;
    }

    /**
     * @return void
     */
    private function buildTempData()
    {
        $this->createTemporaryTable();
        $this->insertApprovedStatusTableData();
    }

    /**
     * @throws \Zend_Db_Statement_Exception
     */
    private function insertApprovedStatusTableData()
    {
        if (!$this->approvedStatusTableData) {
            $this->approvedStatusTableData = implode(',', $this->getTemporaryApprovedStatusTableData());
            if ($this->approvedStatusTableData) {
                $connection = $this->utilities->getResourceConnection()->getConnection();
                $sql = "INSERT INTO "
                    . self::TMP_APPROVAL_HISTORY_TBL
                    . "(history_id, status, order_id, group_id, child_group_id, customer_id ) VALUES
               $this->approvedStatusTableData";

                $connection->query($sql);
            }
        }
    }

    /**
     * @param string $orderId
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    private function getHistoryData($orderId, $useSelfApprovedFilter = true)
    {
        $collection = $this->orderApprovals->getApprovalOrdersCollection(null, $useSelfApprovedFilter);
        $collection->addFieldToFilter('main_table.entity_id', ['eq' => $orderId]);
        $sql = $collection->getSelectSql(true);

        $connection = $this->utilities->getResourceConnection()->getConnection();

        return $connection->query($sql)->fetchAll();
    }

    /**
     * @return array
     */
    public function getApprovalOrdersNotPending()
    {
        $collection = $this->getApprovalHistoryCollection();

        $collection->addFieldToFilter(
            ['main_table.is_approval_pending', 'tmp.status'],
            [
                ['neq' => '1'],
                ['neq' => 'Pending']
            ]
        );

        return $collection->getAllIds();
    }
}
