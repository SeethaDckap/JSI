<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\OrderApproval\Plugin\Block\Sales\Order;

use Epicor\Comm\Block\Sales\Order\Returnlink;

class ReturnLinkPlugin
{
    /**
     * @var \Epicor\OrderApproval\Block\Order\View\ApprovalActions
     */
    private $approvalActions;

    /**
     * ReturnLinkPlugin constructor.
     * @param \Epicor\OrderApproval\Block\Order\View\ApprovalActions $approvalActions
     */
    public function __construct(
        \Epicor\OrderApproval\Block\Order\View\ApprovalActions $approvalActions
    ) {

        $this->approvalActions = $approvalActions;
    }

    /**
     * @param Returnlink $subject
     * @param $result
     * @return mixed
     */
    public function afterCanReturn(Returnlink $subject, $result)
    {
        if (!$this->approvalActions->isApprovalSectionVisible()) {
            return $result;
        }
    }
}