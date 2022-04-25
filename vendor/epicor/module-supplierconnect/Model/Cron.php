<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Supplierconnect\Model;


class Cron
{

    /**
     * @var \Epicor\Quotes\Model\ResourceModel\Quote\CollectionFactory
     */
    protected $SupplierReminderFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $_localeDate;

    protected $supplierconnectHelper;

    public function __construct(
        \Epicor\Supplierconnect\Model\ResourceModel\SupplierReminder\CollectionFactory $SupplierReminderFactory,
        \Epicor\Supplierconnect\Helper\Crondata $supplierconnectHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->SupplierReminderFactory = $SupplierReminderFactory;
        $this->scopeConfig = $scopeConfig;
        $this->_localeDate = $localeDate;
        $this->supplierconnectHelper = $supplierconnectHelper;
    }

    public function checkAndSendEmailRfqs()
    {
        $sendReminders = $this->scopeConfig->getValue('supplierconnect_enabled_messages/SUSD_request/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $customerDatas = [];
        $finalCustomerDatas = [];
        if ($sendReminders) {
            $Items = $this->getCustomerErpAccounts();
            if (count($Items) > 0) {
                foreach ($Items as $erpAccountId => $rfqsCron) {
                    foreach ($rfqsCron as $emaiDatas => $cronDatas) {
                        $checkReminders = $this->supplierconnectHelper->checkExpiredOrNot($cronDatas);
                        if ($checkReminders) {
                            if ($cronDatas['rfqs_due_today_enable']) {
                                $customerDatas[$erpAccountId][$emaiDatas]['rfqs_due_today_enable'] = $this->supplierconnectHelper->sendDailyCondition($cronDatas, "getDueToday", "getRfqs", "getRfqsDueTodaySentAt", "setRfqsDueTodaySentAt", "getRfqsDueTodayEnable", "Rfqs Due today");
                            }
                            if ($cronDatas['rfqs_due_this_week_enable']) {
                                $customerDatas[$erpAccountId][$emaiDatas]['rfqs_due_this_week_enable'] = $this->supplierconnectHelper->sendDueThisWeek($cronDatas);
                            }
                            if ($cronDatas['upcoming_rfqs_enable']) {
                                $getRfqsUOptions = $cronDatas['rfqs_upcoming_options'];
                                $customerDatas[$erpAccountId][$emaiDatas]['upcoming_rfqs_enable'] ='';
                                if ($getRfqsUOptions == "daily") {
                                    $customerDatas[$erpAccountId][$emaiDatas]['upcoming_rfqs_enable'] = $this->supplierconnectHelper->sendDailyCondition($cronDatas, "getDueFuture", "getRfqs", "getUpcomingRfqsSentAt", "setUpcomingRfqsSentAt", "getUpcomingRfqsEnable", "Upcoming Rfqs");
                                } elseif ($getRfqsUOptions == "weekly") {
                                    $customerDatas[$erpAccountId][$emaiDatas]['upcoming_rfqs_enable'] = $this->supplierconnectHelper->sendWeeklyCondition($cronDatas, "getDueFuture", "getRfqs", "getUpcomingRfqsSentAt", "setUpcomingRfqsSentAt", "getUpcomingRfqsEnable", "Upcoming Rfqs");
                                } elseif ($getRfqsUOptions == "monthly") {
                                    $customerDatas[$erpAccountId][$emaiDatas]['upcoming_rfqs_enable'] = $this->supplierconnectHelper->sendMonthlyCondition($cronDatas, "getDueFuture", "getRfqs", "getUpcomingRfqsSentAt", "setUpcomingRfqsSentAt", "getUpcomingRfqsEnable", "Upcoming Rfqs");
                                }
                            }
                            if ($cronDatas['all_open_rfqs_enable']) {
                                $getOpenOptions = $cronDatas['rfqs_open_options'];
                                $customerDatas[$erpAccountId][$emaiDatas]['all_open_rfqs_enable'] = '';
                                if ($getOpenOptions == "daily") {
                                    $customerDatas[$erpAccountId][$emaiDatas]['all_open_rfqs_enable'] =  $this->supplierconnectHelper->sendDailyCondition($cronDatas, "getOpen", "getRfqs", "getAllOpenRfqsSentAt", "setAllOpenRfqsSentAt", "getAllOpenRfqsEnable", "All Open Rfqs");
                                } elseif ($getOpenOptions == "weekly") {
                                    $customerDatas[$erpAccountId][$emaiDatas]['all_open_rfqs_enable'] =  $this->supplierconnectHelper->sendWeeklyCondition($cronDatas, "getOpen", "getRfqs", "getAllOpenRfqsSentAt", "setAllOpenRfqsSentAt", "getAllOpenRfqsEnable", "All Open Rfqs");
                                } elseif ($getOpenOptions == "monthly") {
                                    $customerDatas[$erpAccountId][$emaiDatas]['all_open_rfqs_enable'] =  $this->supplierconnectHelper->sendMonthlyCondition($cronDatas, "getOpen", "getRfqs", "getAllOpenRfqsSentAt", "setAllOpenRfqsSentAt", "getAllOpenRfqsEnable", "All Open Rfqs");
                                }
                            }
                            if ($cronDatas['all_overdue_rfqs_enable']) {
                                $getOverdueOptions = $cronDatas['all_overdue_rfqs_options'];
                                $customerDatas[$erpAccountId][$emaiDatas]['all_overdue_rfqs_enable'] ='';
                                if ($getOverdueOptions == "daily") {
                                    $customerDatas[$erpAccountId][$emaiDatas]['all_overdue_rfqs_enable'] = $this->supplierconnectHelper->sendDailyCondition($cronDatas, "getOverDue", "getRfqs", "getAllOverdueRfqsSentAt", "setAllOverdueRfqsSentAt", "getAllOverdueRfqsEnable", "All Overdue Rfqs");
                                } elseif ($getOverdueOptions == "weekly") {
                                    $customerDatas[$erpAccountId][$emaiDatas]['all_overdue_rfqs_enable'] =$this->supplierconnectHelper->sendWeeklyCondition($cronDatas, "getOverDue", "getRfqs", "getAllOverdueRfqsSentAt", "setAllOverdueRfqsSentAt", "getAllOverdueRfqsEnable", "All Overdue Rfqs");
                                } elseif ($getOverdueOptions == "monthly") {
                                    $customerDatas[$erpAccountId][$emaiDatas]['all_overdue_rfqs_enable'] = $this->supplierconnectHelper->sendMonthlyCondition($cronDatas, "getOverDue", "getRfqs", "getAllOverdueRfqsSentAt", "setAllOverdueRfqsSentAt", "getAllOverdueRfqsEnable", "All Overdue Rfqs");
                                }
                            }
                            if ($cronDatas['order_open_po_enable']) {
                                $getPosOptions = $cronDatas['order_open_po_options'];
                                $customerDatas[$erpAccountId][$emaiDatas]['order_open_po_enable'] ='';
                                if ($getPosOptions == "daily") {
                                    $customerDatas[$erpAccountId][$emaiDatas]['order_open_po_enable'] = $this->supplierconnectHelper->sendDailyCondition($cronDatas, "getOpen", "getPurchaseOrders", "getOrderOpenPoSentAt", "setOrderOpenPoSentAt", "getOrderOpenPoEnable", "All Open POs");
                                } elseif ($getPosOptions == "weekly") {
                                    $customerDatas[$erpAccountId][$emaiDatas]['order_open_po_enable'] = $this->supplierconnectHelper->sendWeeklyCondition($cronDatas, "getOpen", "getPurchaseOrders", "getOrderOpenPoSentAt", "setOrderOpenPoSentAt", "getOrderOpenPoEnable", "All Open POs");
                                } elseif ($getPosOptions == "monthly") {
                                    $customerDatas[$erpAccountId][$emaiDatas]['order_open_po_enable'] = $this->supplierconnectHelper->sendMonthlyCondition($cronDatas, "getOpen", "getPurchaseOrders", "getOrderOpenPoSentAt", "setOrderOpenPoSentAt", "getOrderOpenPoEnable", "All Open POs");
                                }
                            }
                            if ($cronDatas['order_po_line_enable']) {
                                $getPosLineOptions = $cronDatas['order_po_line_options'];
                                $customerDatas[$erpAccountId][$emaiDatas]['order_po_line_enable'] ='';
                                if ($getPosLineOptions == "daily") {
                                    $customerDatas[$erpAccountId][$emaiDatas]['order_po_line_enable'] = $this->supplierconnectHelper->sendDailyCondition($cronDatas, "getChanges", "getPurchaseOrders", "getOrderOpenPoLineSentAt", "setOrderOpenPoLineSentAt", "getOrderPoLineEnable", "PO Line / Release Changes");
                                } elseif ($getPosLineOptions == "weekly") {
                                    $customerDatas[$erpAccountId][$emaiDatas]['order_po_line_enable'] = $this->supplierconnectHelper->sendWeeklyCondition($cronDatas, "getChanges", "getPurchaseOrders", "getOrderOpenPoLineSentAt", "setOrderOpenPoLineSentAt", "getOrderPoLineEnable", "PO Line / Release Changes");
                                } elseif ($getPosLineOptions == "monthly") {
                                    $customerDatas[$erpAccountId][$emaiDatas]['order_po_line_enable'] = $this->supplierconnectHelper->sendMonthlyCondition($cronDatas, "getChanges", "getPurchaseOrders", "getOrderOpenPoLineSentAt", "setOrderOpenPoLineSentAt", "getOrderPoLineEnable", "PO Line / Release Changes");
                                }
                            }

                            $filterVals = array_filter($customerDatas[$erpAccountId][$emaiDatas]);
                            if(!empty($filterVals)) {
                                $finalCustomerDatas[$erpAccountId][$emaiDatas] =$filterVals;
                            }

                        }

                    }

                }
            }
        }
        return $this->sendEmails($finalCustomerDatas);
    }

    public function getCustomerErpAccounts()
    {
        $collection = $this->SupplierReminderFactory->create()->load();
        $sendReminders = $this->scopeConfig->getValue('supplierconnect_enabled_messages/SUSD_request/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $customerErps = [];
        if ($sendReminders) {
            if (count($collection->getItems()) > 0) {
                foreach ($collection->getItems() as $rfqsCron) {
                    $customerInfo = $this->supplierconnectHelper->getcustomerInformation($rfqsCron->getCustomerId());
                    if(($customerInfo->getEccErpAccountType() == "supplier") && ($customerInfo->getEccSupplierErpaccountId())) {
                        $customerErps[$customerInfo->getEccSupplierErpaccountId()][$customerInfo->getEmail()] = $rfqsCron->getData();
                    }
                }
            }
            return $customerErps;
        }
    }

    public function sendEmails($finalCustomerDatas) {
        if(!empty($finalCustomerDatas)) {
            foreach ($finalCustomerDatas as $erpAccountId=>$customerData) {
                $messages = $this->supplierconnectHelper->sendSusdMessage($erpAccountId);
                if(!empty($messages)) {
                    foreach ($customerData as $emailId => $keyDatas) {
                        if (isset($keyDatas['rfqs_due_today_enable']) && ($messages->getRfqs()->getDueToday() > 0)) {
                            $this->supplierconnectHelper->sendRfqsReminder($messages->getRfqs()->getDueToday(),"Rfqs Due today",$emailId);
                        }
                        if (isset($keyDatas['rfqs_due_this_week_enable']) && ($messages->getRfqs()->getDueWeek() > 0) ) {
                            $this->supplierconnectHelper->sendRfqsReminder($messages->getRfqs()->getDueWeek(),"Rfqs Due this week",$emailId);
                        }

                        if (isset($keyDatas['upcoming_rfqs_enable']) && ($messages->getRfqs()->getDueFuture() > 0)) {
                            $this->supplierconnectHelper->sendRfqsReminder($messages->getRfqs()->getDueFuture(),"Upcoming Rfqs",$emailId);
                        }

                        if (isset($keyDatas['all_open_rfqs_enable']) && ($messages->getRfqs()->getOpen() > 0)) {
                            $this->supplierconnectHelper->sendRfqsReminder($messages->getRfqs()->getOpen(),"All Open Rfqs",$emailId);
                        }

                        if (isset($keyDatas['all_overdue_rfqs_enable']) && ($messages->getRfqs()->getOverDue() > 0)) {
                            $this->supplierconnectHelper->sendRfqsReminder($messages->getRfqs()->getOverDue(),"All Overdue Rfqs",$emailId);
                        }

                        if (isset($keyDatas['order_open_po_enable']) && ($messages->getPurchaseOrders()->getOpen() > 0)) {
                            $this->supplierconnectHelper->sendRfqsReminder($messages->getPurchaseOrders()->getOpen(),"All Open POs",$emailId);
                        }

                        if (isset($keyDatas['order_po_line_enable']) && ($messages->getPurchaseOrders()->getChanges() > 0)) {
                            $this->supplierconnectHelper->sendRfqsReminder($messages->getPurchaseOrders()->getChanges(),"PO Line / Release Changes",$emailId);
                        }
                    }
                }
                //$messages = $this->supplierconnectHelper->sendSusdMessage($erpAccountId);
            }
        }
    }

    public function checkExpiryReminder()
    {
        $collection = $this->SupplierReminderFactory->create()
            ->addFieldToFilter('reminder_expiry_date_enable', array('eq' => 1))
            ->load();
        //$collection->setPage(1, 100)->load();
        $sendReminders = $this->scopeConfig->getValue('supplierconnect_enabled_messages/SUSD_request/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($sendReminders) {
            if (count($collection->getItems()) > 0) {
                foreach ($collection->getItems() as $rfqsCron) {
                    if ($rfqsCron->getReminderExpiryDateEnable()) {
                        $expiryEnable = $rfqsCron->getReminderExpiryDateEnable();
                        if ($expiryEnable) {
                            $this->supplierconnectHelper->reminderExpirySupplier($rfqsCron);
                        }
                    }
                }
            }
        }
    }

}