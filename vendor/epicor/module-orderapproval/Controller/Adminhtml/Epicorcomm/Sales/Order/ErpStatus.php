<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Controller\Adminhtml\Epicorcomm\Sales\Order;

use Epicor\OrderApproval\Model\Status\Options as OrderApprovalStatus;

class ErpStatus extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Sales\Order\Erpstatus
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var OrderApprovalStatus
     */
    private $orderApprovalStatus;

    /**
     * ErpStatus constructor.
     * @param \Epicor\Comm\Controller\Adminhtml\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJson
     * @param \Epicor\Comm\Helper\Data $commHelper
     * @param \Magento\Sales\Model\OrderFactory $salesOrderFactory
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     * @param \Epicor\Comm\Helper\BsvAndGor $bsvAndGorHelper
     * @param OrderApprovalStatus|null $orderApprovalStatus
     */
    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJson,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Sales\Model\OrderFactory $salesOrderFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Comm\Helper\BsvAndGor $bsvAndGorHelper,
        OrderApprovalStatus $orderApprovalStatus = null
    ) {
        $this->orderApprovalStatus = $orderApprovalStatus;
        $this->resultJsonFactory = $resultJson;
        parent::__construct(
            $context,
            $resultJson,
            $commHelper,
            $salesOrderFactory,
            $backendAuthSession,
            $bsvAndGorHelper
        );
    }

    public function execute()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        $stateChange = $this->getRequest()->getParam('ecc_gor_sent');
        if (!$this->orderApprovalStatus->isOrderApprovalRestrictedStateChange($order_id, $stateChange)) {
            $this->changeErpstatus($order_id, $stateChange);
            $this->messageManager->addSuccessMessage(__('Order Erp Status changed'));

        }
        $this->updateOrderPendingStatus($order_id, $stateChange);
        $url = $this->getUrl('sales/order/view', ['order_id' => $order_id, 'active_tab' => 'order_design_details']);
        $response = array('error' => false, 'success' => true, 'ajaxExpired' => true, 'ajaxRedirect' => $url);

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($response);

        return $resultJson;
    }

    /**
     * @param $orderId
     * @param $statueChange
     * @return \Magento\Sales\Model\ResourceModel\Order
     * @throws \Exception
     */
    private function updateOrderPendingStatus($orderId, $stateChange)
    {
        $order = $this->orderApprovalStatus->getOrderById($orderId);
        $orderResource = $this->orderApprovalStatus->getOrderResource();
        switch ($stateChange) {
            case OrderApprovalStatus::ECC_ORDER_APPROVAL_PENDING_GOR_STATE:
                $order->setData('is_approval_pending', 1);
                $this->setLastHistoryStatus($orderId, 'Pending');
                $this->resetApprovalProcess($order);
                break;
            case OrderApprovalStatus::ECC_ORDER_APPROVAL_REJECTED_GOR_STATE:
                $order->setData('is_approval_pending', 2);
                $this->setLastHistoryStatus($orderId, 'Rejected');
                break;
            default:
                $order->setData('is_approval_pending', 0);
        }


        return $orderResource->saveAttribute($order, 'is_approval_pending');
    }

    /**
     * @param $orderId
     * @param $status
     */
    private function setLastHistoryStatus($orderId, $status)
    {
        $this->orderApprovalStatus->getApprovalHistory()->updateLastHistoryStatus($orderId, $status);
    }

    /**
     * @param $order
     */
    private function resetApprovalProcess($order)
    {
        $order->setData('event_manager', $this->_eventManager);

        $this->orderApprovalStatus->resetApprovalProcess($order);
    }

}