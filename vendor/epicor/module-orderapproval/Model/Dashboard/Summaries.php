<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Dashboard;

use Epicor\OrderApproval\Model\Approval\OrderApprovals;
use Epicor\OrderApproval\Model\Approval\ApprovedStatus;

class Summaries
{
    /**
     * @var string
     */
    private $statusFilter = 'aoh.status';
    /**
     * @var string
     */
    private $dateFormat = 'Y-m-d H:i:s';

    /**
     * @var OrderApprovals
     */
    private $orderApprovals;

    /**
     * @var ApprovedStatus
     */
    private $approvedStatus;

    /**
     * Summaries constructor.
     * @param OrderApprovals $orderApprovals
     * @param ApprovedStatus $approvedStatus
     */
    public function __construct(
        OrderApprovals $orderApprovals,
        ApprovedStatus $approvedStatus
    ) {
        $this->orderApprovals = $orderApprovals;
        $this->approvedStatus = $approvedStatus;
    }

    /**
     * @return array
     */
    public function getPendingCount()
    {
        $approvalsCollection = $this->orderApprovals->getApprovalOrdersCollection();
        $approvalsCollection->addFieldToFilter($this->statusFilter, ['eq' => 'Pending']);
        return $approvalsCollection->getAllIds();
    }

    /**
     * @return array
     */
    public function getTodayCount()
    {
        $approvalsCollection = $this->orderApprovals->getApprovalOrdersCollection();
        $approvalsCollection->addFieldToFilter('created_at', ['gteq' => $this->getStartToday()]);
        $approvalsCollection->addFieldToFilter('created_at', ['lteq' => $this->getEndToday()]);
        $approvalsCollection->addFieldToFilter($this->statusFilter, ['eq' => 'Pending']);

        return $approvalsCollection->getAllIds();
    }

    /**
     * @return array
     */
    public function getLastSevenDaysCount()
    {
        $approvalsCollection = $this->orderApprovals->getApprovalOrdersCollection();
        $approvalsCollection->addFieldToFilter('created_at', ['gt' => $this->getStartSevenDays()]);
        $approvalsCollection->addFieldToFilter('created_at', ['lteq' => $this->getYesterdayEndTime()]);
        $approvalsCollection->addFieldToFilter($this->statusFilter, ['eq' => 'Pending']);

        return $approvalsCollection->getAllIds();
    }

    /**
     * @return array
     */
    public function getApprovalsOlderThenSevenDays()
    {
        $approvalsCollection = $this->orderApprovals->getApprovalOrdersCollection();
        $approvalsCollection->addFieldToFilter('created_at', ['lteq' => $this->getStartSevenDays()]);
        $approvalsCollection->addFieldToFilter($this->statusFilter, ['eq' => 'Pending']);

        return $approvalsCollection->getAllIds();
    }

    /**
     * @return array
     */
    public function getAllApproved()
    {
        $approvalsCollection = $this->approvedStatus->getApprovalHistoryCollection();
        $approvalsCollection->addFieldToFilter('tmp.status', ['in' => ['Approved', 'Self Approved']]);
        return $approvalsCollection->getAllIds();
    }

    /**
     * @return array
     */
    public function getAllRejected()
    {
        $approvalsCollection = $this->orderApprovals->getApprovalOrdersCollection();
        $approvalsCollection->addFieldToFilter($this->statusFilter, ['eq' => 'Rejected']);
        return $approvalsCollection->getAllIds();
    }

    /**
     * @return array
     */
    public function getAllApprovalStatuses()
    {
        $approvalsCollection = $this->approvedStatus->getApprovalHistoryCollection();

        return $approvalsCollection->getAllIds();
    }

    /**
     * Returns start of today at 00:00:00
     * @return false|string
     */
    private function getStartToday()
    {
        return date($this->dateFormat, strtotime($this->getBasicDateToday() . ' 00:00:00'));
    }

    /**
     * Returns yesterday time at end of day ie 23:59:59
     * @return false|string
     */
    private function getYesterdayEndTime()
    {
        return date($this->dateFormat, strtotime($this->getYesterdayBasicDate() . ' 23:59:59'));
    }

    /**
     * Returns basic format eg 2020-10-25 for yesterday
     * @return false|string
     */
    private function getYesterdayBasicDate()
    {
        return date('Y-m-d', strtotime('yesterday'));
    }

    /**
     * Returns the date time for last second of today
     * @return false|string
     */
    private function getEndToday()
    {
        return date($this->dateFormat, strtotime($this->getBasicDateToday() . ' 23:59:59'));
    }

    /**
     * Returns the basic date for today ie 2020-10-25
     * @return false|string
     */
    private function getBasicDateToday()
    {
        return date('Y-m-d', strtotime('now'));
    }

    /**
     * Returns 7 days a ago from end of day yesterday (23:59:59) ie if today is
     * 15th then 7 days from 23:59:59 on the 14th = 7th at 23:59:59
     * @return false|string
     */
    private function getStartSevenDays()
    {
        return date($this->dateFormat, strtotime($this->getYesterdayEndTime() . ' -7days'));
    }
}
