<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Approval;

use Epicor\OrderApproval\Model\Approval\Email\Sender\RejectedSender;
use Epicor\OrderApproval\Model\Approval\Email\Sender\ApprovedSender;
use Epicor\OrderApproval\Model\Approval\Email\Sender\ApproverSender;

class EmailSenders
{
    /**
     * @var RejectedSender
     */
    private $rejectedSender;

    /**
     * @var ApprovedSender
     */
    private $approvedSender;

    /**
     * @var ApproverSender
     */
    private $approverSender;

    /**
     * EmailSenders constructor.
     * @param RejectedSender $rejectedSender
     * @param ApprovedSender $approvedSender
     * @param ApproverSender $approverSender
     */
    public function __construct(
        RejectedSender $rejectedSender,
        ApprovedSender $approvedSender,
        ApproverSender $approverSender
    ) {
        $this->rejectedSender = $rejectedSender;
        $this->approvedSender = $approvedSender;
        $this->approverSender = $approverSender;
    }

    /**
     * @return RejectedSender
     */
    public function getRejectSender()
    {
        return $this->rejectedSender;
    }

    /**
     * @return ApprovedSender
     */
    public function getApprovedSender()
    {
        return $this->approvedSender;
    }

    /**
     * @return ApproverSender
     */
    public function getApproverSender()
    {
        return $this->approverSender;
    }
}
