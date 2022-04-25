<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\OrderApproval\Observer;

use Epicor\OrderApproval\Model\GroupManagementFactory as GroupManagementFactory;
use Epicor\OrderApproval\Model\GroupManagement;
use Epicor\OrderApproval\Model\ResourceModel\OrderHistoryFactory as OrderHistoryFactory;
class SaveOrderApprovalHierarchy implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var GroupManagementFactory
     */
    private $groupManagementFactory;
    /**
     * @var OrderHistoryFactory
     */
    private $orderHistoryFactory;

    /**
     * SaveOrderApprovalHierarchy constructor.
     *
     * @param GroupManagementFactory $groupManagementFactory
     * @param OrderHistoryFactory    $orderHistoryFactory
     */
    public function __construct(
        GroupManagementFactory $groupManagementFactory,
        OrderHistoryFactory $orderHistoryFactory
    ) {
        $this->groupManagementFactory = $groupManagementFactory;
        $this->orderHistoryFactory = $orderHistoryFactory;
    }
    
    /**
     * Guest customer/user sending GOR
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Epicor\Comm\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        if($order && $order->getIsApprovalPending() == 1) {
            /** @var GroupManagement $groupManagement */
            $groupManagement = $this->groupManagementFactory->create();
            $group = $groupManagement->getAppliedGroupByOrder($order);
            if($group) {
                $history = $groupManagement->getOrderApprovalHistory();
                if($history){
                    foreach ($history as $key => $value) {
                        $history[$key]['customer_id'] = $order->getCustomerId();
                        $history[$key]['order_id'] = $order->getId();
                    }
                    $this->orderHistoryFactory->create()->massInsert($history);
                }

            }
        }

        return $this;
    }

   
        

}