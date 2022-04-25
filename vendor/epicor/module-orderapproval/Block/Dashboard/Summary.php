<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\OrderApproval\Block\Dashboard;

use Magento\Framework\View\Element\Template;
use Epicor\OrderApproval\Model\Dashboard\Summaries as DashboardSummaries;
use Epicor\OrderApproval\Model\GroupSave\Customers as GroupCustomers;
use Epicor\OrderApproval\Model\Dashboard\Management as DashboardManagement;

class Summary extends \Epicor\AccessRight\Block\Template
{
    const FRONTEND_RESOURCE = 'Epicor_Customer::my_account_approvals_dashboard';

    /**
     * @var DashboardSummaries
     */
    private $dashboardSummaries;

    /**
     * @var GroupCustomers
     */
    private $groupCustomers;
    
    /**
     * @var DashboardManagement
     */
    private $dashboardManagement;

    /**
     * Summary constructor.
     * @param Template\Context $context
     * @param DashboardSummaries $dashboardSummaries
     * @param GroupCustomers $groupCustomers
     * @param DashboardManagement $dashboardManagement
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        DashboardSummaries $dashboardSummaries,
        GroupCustomers $groupCustomers,
        DashboardManagement $dashboardManagement,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dashboardSummaries = $dashboardSummaries;
        $this->groupCustomers = $groupCustomers;
        $this->dashboardManagement = $dashboardManagement;
    }

    /**
     * @return int
     */
    public function getPendingSummary()
    {
        $summaryIds = $this->dashboardSummaries->getPendingCount();

        if (count($summaryIds)) {
            return $this->getSummaryLink($summaryIds);
        }

        return count($summaryIds);
    }


    /**
     * @return array|string
     */
    public function getTodaySummary()
    {
        $summaryIds = $this->dashboardSummaries->getTodayCount();

        if (count($summaryIds)) {
            return $this->getSummaryLink($summaryIds);
        }
        return count($summaryIds);
    }

    /**
     * @return array|string
     */
    public function getLastSevenSummary()
    {
        $summaryIds = $this->dashboardSummaries->getLastSevenDaysCount();
        if (count($summaryIds)) {
            return $this->getSummaryLink($summaryIds);
        }

        return count($summaryIds);
    }

    /**
     * @return int|string|void
     */
    public function getAllRejectedSummary()
    {
        $summaryIds = $this->dashboardSummaries->getAllRejected();

        if (count($summaryIds)) {
            return $this->getSummaryLink($summaryIds);
        }

        return count($summaryIds);
    }

    /**
     * @return int|string|void
     */
    public function getAllApprovedSummary()
    {
        $summaryIds = $this->dashboardSummaries->getAllApproved();

        if (count($summaryIds)) {
            return $this->getSummaryLink($summaryIds);
        }

        return count($summaryIds);
    }

    /**
     * @param $summaryIds
     * @return string
     */
    private function getSummaryLink($summaryIds)
    {
        $pendingCount = count($summaryIds);
        $args = ['summary_view' => base64_encode(json_encode($summaryIds))];
        $approvalUrl = $this->getUrl('epicor_orderapproval/manage/approvals', $args);
        return '<a href="' . $approvalUrl . '">' . $pendingCount . '</a>';
    }

    /**
     * @return int|string|void
     */
    public function getAllSummary()
    {
        $summaryIds = $this->dashboardSummaries->getAllApprovalStatuses();

        if (count($summaryIds)) {
            return $this->getSummaryLink($summaryIds);
        }

        return count($summaryIds);
    }


    /**
     * @return array|string
     */
    public function getOtherSummary()
    {
        $summaryIds = $this->dashboardSummaries->getApprovalsOlderThenSevenDays();
        if (count($summaryIds)) {
            return $this->getSummaryLink($summaryIds);
        }

        return count($summaryIds);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function _toHtml()
    {
        if ($this->dashboardManagement->canDisplayDashboard()
            && $this->_isAllowed() === true
        ) {
            return parent::_toHtml();
        }
    }

}