<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Model;

use Magento\Framework\Model\AbstractModel;

class DealerReminder extends \Epicor\Dealerconnect\Model\AbstractClaim
{

    protected $_customerErps = false;

    protected $customerSession;


    protected $dealerconnectHelper;

    /**
     * Claimstatus constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $erpAccountCollection
     * @param \Epicor\Dealerconnect\ModelMessage\Request\DclsFactory $dclsFactory
     * @param \Epicor\Dealerconnect\ModelMessage\Request\DcldFactory $dcldFactory
     * @param \Epicor\Dealerconnect\Helper\Messaging $dealerMessagingHelper
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Epicor\Comm\Model\Erp\Mapping\Claimstatus $claimStatusMapping
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Dealerconnect\Model\Message\Request\DclsFactory $dclsFactory,
        \Epicor\Dealerconnect\Model\Message\Request\DcldFactory $dcldFactory,
        \Epicor\Dealerconnect\Helper\Messaging $dealerMessagingHelper,
        \Epicor\Dealerconnect\Helper\Reminder $dealerconnectHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Epicor\Comm\Model\Erp\Mapping\Claimstatus $claimStatusMapping,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->customerSession = $customerSession;
        $this->dealerconnectHelper = $dealerconnectHelper;
        parent::__construct(
            $context,
            $registry,
            $dclsFactory,
            $dcldFactory,
            $dealerMessagingHelper,
            $localeResolver,
            $claimStatusMapping,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
    }


    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Epicor\Dealerconnect\Model\ResourceModel\DealerReminder');
    }


    /**
     * Send Instant Email
     * @return mixed
     */
    public function instantEmailData()
    {
        if (!$this->isDclsActive()
            || !$this->isDcldActive()
        ) {
            return false;
        }
        $_erpAccounts[] = $this->_dealerMessagingHelper->getErpAccountNumber();
        $claimStatusData = $this->sendClaimMessages($_erpAccounts);
        return $claimStatusData;
    }


    /**
     * Get the Send Reminder Email
     * @param array $claimStatusData Erp Claims Data
     * @return mixed
     */

    public function checkAndSendReminder($claimStatusData)
    {
        if (!$this->isDclsActive()
            || !$this->isDcldActive()
        ) {
            return false;
        }
        $allReminderOption = $this->getCustomerErpAccounts();
        $_erpAccounts = array_keys($allReminderOption);
        $finalCustomerDatas = [];
        $customerDatas = [];
        if (!empty($_erpAccounts) && $claimStatusData && !empty($claimStatusData)) {
            foreach ($allReminderOption as $erpcode => $customersoptions) {
                if (isset($claimStatusData[$erpcode])) {
                    $cronDatas = $claimStatusData[$erpcode];
                    foreach ($customersoptions as $email => $customeroption) {
                        $checkReminders = $this->dealerconnectHelper->checkExpiredOrNot($customeroption);
                        if ($checkReminders) {
                            $erpAccountId = $customeroption['account_id'];
                            if ($customeroption['claims_due_today_enable'] && isset($cronDatas['Today']['claims'])
                                && count($cronDatas['Today']['claims'])) {
                                $emailData = [
                                    'messageReference' => count($cronDatas['Today']['claims']),
                                    'referenceName' => "Claims Due today",
                                    'email' => $email
                                ];
                                $customerDatas[$customeroption['account_id']][$email]['claims_due_today_enable'] =
                                    $this->dealerconnectHelper->saveCondition($customeroption, "claims_due_today_sent_at", "daily", $emailData);
                            }
                            if ($customeroption['claims_due_this_week_enable'] && isset($cronDatas['Week']['claims'])
                                && count($cronDatas['Week']['claims'])) {
                                $emailData = [
                                    'messageReference' => count($cronDatas['Week']['claims']),
                                    'referenceName' => "Claims Due this week",
                                    'email' => $email
                                ];
                                $customerDatas[$erpAccountId][$email]['claims_due_this_week_enable'] =
                                    $this->dealerconnectHelper->saveCondition($customeroption, "claims_due_this_week_sent_at", "weekly", $emailData);
                            }

                            if ($customeroption['upcoming_claims_enable'] && isset($cronDatas['Future']['claims'])
                                && count($cronDatas['Future']['claims'])) {
                                $emailData = [
                                    'messageReference' => count($cronDatas['Future']['claims']),
                                    'referenceName' => "Upcoming claims",
                                    'email' => $email
                                ];
                                $getclaimsUOptions = $customeroption['claims_upcoming_options'];
                                $customerDatas[$erpAccountId][$email]['upcoming_claims_enable'] =
                                    $this->dealerconnectHelper->saveCondition($customeroption, "upcoming_claims_sent_at", $getclaimsUOptions, $emailData);
                            }

                            if ($customeroption['all_overdue_claims_enable'] && isset($cronDatas['Overdue']['claims'])
                                && count($cronDatas['Overdue']['claims'])) {
                                $emailData = [
                                    'messageReference' => count($cronDatas['Overdue']['claims']),
                                    'referenceName' => "All Overdue claims",
                                    'email' => $email
                                ];
                                $getclaimsUOptions = $customeroption['all_overdue_claims_options'];
                                $customerDatas[$erpAccountId][$email]['all_overdue_claims_enable'] =
                                    $this->dealerconnectHelper->saveCondition($customeroption, "all_overdue_claims_sent_at", $getclaimsUOptions, $emailData);
                            }

                            if (!empty($customerDatas[$erpAccountId][$email])) {
                                $filterVals = array_filter($customerDatas[$erpAccountId][$email]);
                                if (!empty($filterVals)) {
                                    $finalCustomerDatas[$erpAccountId][$email] = $filterVals;
                                }
                            }

                        }

                    }
                }
            }
        }
        return $claimStatusData;
    }


    /**
     * Get All Options of reminder oppt by customer
     * @return array
     */

    public function getCustomerErpAccounts()
    {
        //$sendReminders = $this->scopeConfig->getValue('supplierconnect_enabled_messages/SUSD_request/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if (!$this->_customerErps) {
            $this->customerErps = [];
            $collection = $this->getCollection();
            $joinTable = $this->_getResource()->getTable('customer_entity');
            $collection->getSelect()->joinLeft(
                ['cus' => $joinTable],
                'main_table.customer_id = cus.entity_id',
                ['email']
            );

            $joinTableerp = $this->_getResource()->getTable('ecc_erp_account');
            $collection->getSelect()->joinLeft(
                ['erp' => $joinTableerp],
                'erp.entity_id = main_table.account_id',
                ['account_number']
            );
            if (count($collection->getItems()) > 0) {
                foreach ($collection->getItems() as $rfqsCron) {
                    $this->_customerErps[$rfqsCron->getData('account_number')][$rfqsCron->getData('email')] = $rfqsCron->getData();
                }
            }
            return $this->_customerErps;
        }
    }


    /**
     * Check Reminder Expiry and Send Email
     * @return void
     */

    public function checkExpiryReminder()
    {
        if (!$this->isDclsActive()
            || !$this->isDcldActive()
        ) {
            return false;
        }
        $collection = $this->getCollection()
            ->addFieldToFilter('reminder_expiry_date_enable', array('eq' => 1))
            ->load();
        if (count($collection->getItems()) > 0) {
            foreach ($collection->getItems() as $claimsCron) {
                if ($claimsCron->getReminderExpiryDateEnable()) {
                    $expiryEnable = $claimsCron->getReminderExpiryDateEnable();
                    if ($expiryEnable) {
                        $this->dealerconnectHelper->reminderExpiryDealer($claimsCron);
                    }
                }
            }
        }

    }


    /**
     * Check Reminder Expiry and Send Email
     * @return void
     */

    public function loadcurrentData($data)
    {
        if (!$this->isDclsActive()
            || !$this->isDcldActive()
        ) {
            return false;
        }
        $collection = $this->getCollection()
            ->addFieldToFilter('customer_id', array('eq' => $data['customer_id']))
            ->addFieldToFilter('account_id', array('eq' => $data['account_id']));
        return $collection->getFirstItem();

    }
}
