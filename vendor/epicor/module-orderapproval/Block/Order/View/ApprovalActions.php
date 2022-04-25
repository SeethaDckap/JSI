<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Order\View;

use Magento\Framework\View\Element\Template;
use Epicor\OrderApproval\Model\Approval\HistoryStatus;
use Epicor\OrderApproval\Model\Approval\OrderApprovals;
use Epicor\OrderApproval\Model\Approval\ApprovedStatus;

class ApprovalActions extends \Epicor\AccessRight\Block\Template
{
    const FRONTEND_RESOURCE = 'Epicor_Customer::my_account_approvals_approved_reject';
    /**
     * @var OrderApprovals
     */
    private $orderApprovals;

    /**
     * @var array
     */
    private $historyItem = [];

    /**
     * @var HistoryStatus
     */
    private $historyStatus;

    /**
     * @var ApprovedStatus
     */
    private $approvedStatus;

    /**
     * ApprovalActions constructor.
     * @param OrderApprovals $orderApprovals
     * @param HistoryStatus $historyStatus
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        OrderApprovals $orderApprovals,
        HistoryStatus $historyStatus,
        Template\Context $context,
        ApprovedStatus $approvedStatus,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->orderApprovals = $orderApprovals;
        $this->historyStatus = $historyStatus;
        $this->approvedStatus = $approvedStatus;
    }

    /**
     * @return bool
     */
    public function isApprovalSectionVisible()
    {
        $orderId = $this->getOrderId();
        if (!$orderId) {
            return false;
        }
        if (in_array($orderId, $this->orderApprovals->getApprovalOrderIds())) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    private function getHistoryItem()
    {
        if (!$this->historyItem) {
            $this->historyItem = $this->orderApprovals->getHistoryItem($this->getHistoryId());
        }

        return $this->historyItem;
    }

    /**
     * @return mixed|string
     */
    private function getHistoryStatus()
    {
        return $this->getHistoryItem()['status'] ?? '';
    }

    /**
     * @return string
     */
    public function getDisabled()
    {
        if($this->getHistoryStatus() === 'Pending'){
            return '';
        }

        return 'disabled="disabled"';
    }

    /**
     * @return false|string
     */
    public function getEncodedOrderId()
    {
        $orderId = $this->getOrderId();
        if ($orderId) {
            return json_encode([$orderId]);
        }
    }

    /**
     * @return string
     */
    public function getApproveRejectUrl()
    {
        $path = 'epicor_orderapproval/manage/approvereject';

        return $this->getUrl($path);
    }

    /**
     * @return float|mixed|null
     */
    public function getOrderGrandTotal()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->orderApprovals->getOrder($orderId);

        return $order->getData('grand_total');
    }

    /**
     * @return mixed
     */
    public function getHistoryId()
    {
        $orderId = $this->getOrderId();

        return $this->approvedStatus->getHistoryIdByOrderId($orderId);
    }

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->getRequest()->getParam('order_id');
    }

    /**
     * @return string
     */
    public function getSubmitter()
    {
        if ($orderId = $this->getOrderId()) {
            return $this->orderApprovals->getOrderSubmitterFullName($orderId);
        }
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        if ($this->_isAllowed() === false) {
            return '';
        }

        return parent::_toHtml();
    }
}