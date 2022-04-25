<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Dashboard;

class Sendinstantemail extends \Epicor\Dealerconnect\Controller\Dashboard
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    protected $customerSession;

    protected $dealerReminderFactory;


    protected $dealerconnectHelper;

    protected $_localeDate;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Registry $registry,
        \Epicor\Dealerconnect\Model\DealerReminderFactory $dealerReminderFactory,
        \Epicor\Dealerconnect\Helper\Reminder $dealerconnectHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
    )
    {
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->dealerReminderFactory = $dealerReminderFactory;
        $this->dealerconnectHelper = $dealerconnectHelper;
        $this->_localeDate = $localeDate;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }

    /**
     * Index action
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost();
        $instantEmailData = ($data['claimStatusData']) ? $data['claimStatusData'] : [];
        $postdata = ($data['options']) ? $data['options'] : [];

        $claims_due_today_enable = (isset($postdata['claims_due_today_enable'])) ? 1 : 0;
        $claims_due_this_week_enable = (isset($postdata['claims_due_this_week_enable'])) ? 1 : 0;
        $upcoming_claims_enable = (isset($postdata['upcoming_claims_enable'])) ? 1 : 0;
        $reminder_expiry_date_enable = (isset($postdata['reminder_expiry_date_enable'])) ? 1 : 0;
        $all_overdue_claims_enable = (isset($postdata['all_overdue_claims_enable'])) ? 1 : 0;
        $reminder_expiry_date = ($postdata['reminder_expiry_date']) ? $postdata['reminder_expiry_date'] : "0000-00-00";
        $email_reminder_enable = (isset($postdata['email_reminder_enable'])) ? 1 : 0;
        $customer = $this->customerSession->getCustomer();
        $erpAccountNumer = $this->dealerconnectHelper->getErpAccountNumber();
        $dueTodayCount = (isset($instantEmailData[$erpAccountNumer]['Today']['claims'])) ? count(($instantEmailData[$erpAccountNumer]['Today']['claims'])) : 0;
        $dueWeekCount = (isset($instantEmailData[$erpAccountNumer]['Week']['claims'])) ? count(($instantEmailData[$erpAccountNumer]['Week']['claims'])) : 0;
        $dueUpcomingCount = (isset($instantEmailData[$erpAccountNumer]['Future']['claims'])) ? count(($instantEmailData[$erpAccountNumer]['Future']['claims'])) : 0;
        $OverdueCount = (isset($instantEmailData[$erpAccountNumer]['Overdue']['claims'])) ? count(($instantEmailData[$erpAccountNumer]['Overdue']['claims'])) : 0;
        $allOverdueCount = $OverdueCount;

        $emailSent = false;
        if ($claims_due_today_enable && $dueTodayCount) {
            $emailSent = true;
            $this->dealerconnectHelper->sendclaimsReminder($dueTodayCount, "Claims Due today", $customer);
        }
        if ($claims_due_this_week_enable && $dueWeekCount) {
            $emailSent = true;
            $this->dealerconnectHelper->sendclaimsReminder($dueWeekCount, "Claims Due this week", $customer);
        }
        if ($upcoming_claims_enable && $dueUpcomingCount) {
            $emailSent = true;
            $this->dealerconnectHelper->sendclaimsReminder($dueUpcomingCount, "Upcoming claims", $customer);
        }
        if ($all_overdue_claims_enable && $allOverdueCount) {
            $this->dealerconnectHelper->sendclaimsReminder($allOverdueCount, "All Overdue claims", $customer);
        }
        if ($emailSent) {
            echo "Email Sent";
        } else {
            echo "Email Not Sent";
        }


    }

}
