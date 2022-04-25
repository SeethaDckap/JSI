<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Dashboard;

class ReminderSave extends \Epicor\Dealerconnect\Controller\Dashboard
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Dealerconnect\Model\Dashboard
     */
    protected $dashboard;

    protected $customerSession;

    protected $dealerReminderFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Registry $registry,
        \Epicor\Dealerconnect\Model\Dashboard $dashboard,
        \Epicor\Dealerconnect\Model\DealerReminderFactory $dealerReminderFactory
    )
    {
        $this->registry = $registry;
        $this->dashboard = $dashboard;
        $this->customerSession = $customerSession;
        $this->dealerReminderFactory = $dealerReminderFactory;
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
        $claims_due_today_enable = (isset($data['claims_due_today_enable'])) ? 1 : 0;
        $claims_due_this_week_enable = (isset($data['claims_due_this_week_enable'])) ? 1 : 0;
        $upcoming_claims_enable = (isset($data['upcoming_claims_enable'])) ? 1 : 0;
        $claims_upcoming_options = $data['claims_upcoming_options'][0];
        $all_overdue_claims_enable = (isset($data['all_overdue_claims_enable'])) ? 1 : 0;
        $all_overdue_claims_options = $data['all_overdue_claims_options'][0];
        $reminder_expiry_date_enable = (isset($data['reminder_expiry_date_enable'])) ? 1 : 0;
        $reminder_expiry_date = ($data['reminder_expiry_date']) ? $data['reminder_expiry_date'] : "0000-00-00";
        $email_reminder_enable = (isset($data['email_reminder_enable'])) ? 1 : 0;


        $customer = $this->customerSession->getCustomer();
        $claimsRemainderFactorModel = $this->dealerReminderFactory->create();
        $claimsRemainderFactor=$claimsRemainderFactorModel->loadcurrentData(
            [
                'customer_id' => $customer->getId(),
                'account_id' => $customer->getEccErpaccountId()
            ]
        );
        if (empty($claims_due_today_enable) &&
            empty($claims_due_this_week_enable) &&
            empty($upcoming_claims_enable) &&
            empty($reminder_expiry_date_enable) &&
            empty($all_overdue_claims_enable)
        ) {
            $claimsRemainderFactor->delete();
        } else {
            $claimsRemainderFactor->setIsActive(1);
            $claimsRemainderFactor->setClaimsDueTodayEnable($claims_due_today_enable);
            $claimsRemainderFactor->setClaimsDueThisWeekEnable($claims_due_this_week_enable);
            $claimsRemainderFactor->setUpcomingClaimsEnable($upcoming_claims_enable);
            $claimsRemainderFactor->setClaimsUpcomingOptions($claims_upcoming_options);
            $claimsRemainderFactor->setAllOverdueClaimsEnable($all_overdue_claims_enable);
            $claimsRemainderFactor->setAllOverdueClaimsOptions($all_overdue_claims_options);
            $claimsRemainderFactor->setReminderExpiryDateEnable($reminder_expiry_date_enable);
            $claimsRemainderFactor->setReminderExpiryDate($reminder_expiry_date);
            $claimsRemainderFactor->setEmailReminderEnable($email_reminder_enable);
            $claimsRemainderFactor->setCustomerId($customer->getId());
            $claimsRemainderFactor->setAccountId($customer->getEccErpaccountId());
            $claimsRemainderFactor->save();
        }
        $this->messageManager->addSuccessMessage(__('Manage Dashbpard Saved Successfully'));
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

}
