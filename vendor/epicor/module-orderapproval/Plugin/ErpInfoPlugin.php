<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\OrderApproval\Plugin;

use Epicor\OrderApproval\Model\Status\Options as OrderApprovalStatus;

class ErpInfoPlugin
{
    /**
     * @var OrderApprovalStatus
     */
    private $orderApprovalStatus;

    /**
     * ErpInfoPlugin constructor.
     * @param OrderApprovalStatus $orderApprovalStatus
     */
    public function __construct(
        OrderApprovalStatus $orderApprovalStatus
    ) {
        $this->orderApprovalStatus = $orderApprovalStatus;
    }

    /**
     * @param \Epicor\Comm\Block\Adminhtml\Sales\Order\View\Tab\Erpinfo $subject
     * @param $result
     * @return mixed
     */
    public function afterGetStatuses(\Epicor\Comm\Block\Adminhtml\Sales\Order\View\Tab\Erpinfo $subject, $result)
    {
        $this->orderApprovalStatus->setSelectOptions($result);

        return $result;
    }
}
