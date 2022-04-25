<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Supplierconnect\Helper;


class Crondata extends \Epicor\Comm\Helper\Messaging
{

    /**
     * @var \Epicor\Common\Helper\Locale\Format\Date
     */


    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;


    protected $commonLocaleFormatDateHelper;

    protected $translationStateInterface;

    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    protected $_localeDate;

    protected $supplierconnectMessageRequestSusd;

    protected $commHelper;

    protected $_customerInfos;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    protected $logger;

    protected $supplierReminderFactory;

    protected $collectionFactory;

    public function __construct(
        \Epicor\Comm\Helper\Messaging\Context $context,
        \Epicor\Common\Helper\Locale\Format\Date $commonLocaleFormatDateHelper,
        \Magento\Framework\Data\CollectionFactory $collectionFactory,
        \Epicor\Supplierconnect\Logger\Logger $logger,
        \Epicor\Supplierconnect\Model\Message\Request\Susd $supplierconnectMessageRequestSusd,
        \Epicor\Supplierconnect\Model\ResourceModel\SupplierReminder\CollectionFactory $supplierReminderFactory
    )
    {
        $this->commonLocaleFormatDateHelper = $commonLocaleFormatDateHelper;
        $this->transportBuilder = $context->getEmailTemplateFactory();
        $this->translationStateInterface = $context->getTranslateInterface();
        $this->storeManager = $context->getStoreManager();
        $this->customerFactory = $context->getCustomerFactory();
        $this->_localeDate = $context->getTimezone();
        $this->supplierconnectMessageRequestSusd = $supplierconnectMessageRequestSusd;
        $this->commHelper = $context->getCommHelper();
        $this->urlBuilder = $context->getUrlBuilder();
        $this->logger = $logger;
        $this->supplierReminderFactory = $supplierReminderFactory;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Converts a date / timestamp to the format specified, using magento locale dates
     *
     * @param string $timestamp
     * @param string $format
     *
     * @return string
     */
    public function getLocalDate($timestamp, $format = \IntlDateFormatter::MEDIUM, $showTime = false)
    {

        $helper = $this->commonLocaleFormatDateHelper;
        return $helper->getLocalDate($timestamp, $format, $showTime);
    }

    public function sendSusdMessage($erpAccountId)
    {
        $message = $this->supplierconnectMessageRequestSusd;
        $helper = $message->getHelper("supplierconnect/messaging");
        $messageTypeCheck = $helper->getMessageType('SUSD');
        if (($message->isActive()) && ($messageTypeCheck)) {
            $message->setLanguageCode($helper->getLanguageMapping($this->_localeResolver->getLocale()));
            $setAccountNumber = $this->commHelper->getSupplierAccountNumber($erpAccountId);
            $message->setAccountNumber($setAccountNumber);
            if ($message->sendMessage()) {
                $message->setStatusDescriptionText('');
                if ($responses = $message->getResponse()) {
                    return $responses;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function sendRfqsReminder($messageReference, $referenceName,$customerId)
    {
        $customerModel = $this->getcustomerInformationEmail($customerId);
        $this->logger->info('Reminder :' . "\n" . $messageReference . " " . $referenceName . " " . $customerId);
        $name = $customerModel->getName();
        $translate = $this->translationStateInterface;
        /* @var $translate Mage_Core_Model_Translate */
        $translate->suspend();
        $templateVars = array(
            'name' => $name,
            'epicreminderreference' => $referenceName,
            'messageReference' => $messageReference,
            'supplierUrl' => $this->urlBuilder->getUrl('supplierconnect/account/index')
        );
        $fromEmail = $this->scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $from = array('email' => $fromEmail, 'name' => 'admin');
        $storeId = $this->storeManager->getStore()->getId();
        $to = array($customerId);
        //$to = array('arjun.manoharan@epicor.com');
        $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId);
        try {
            $transport = $this->transportBuilder->setTemplateIdentifier('supplier_email_rfqs_reminder_email_template')
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($to)
                ->getTransport();
            $transport->sendMessage();
            $this->translationStateInterface->resume(true);
        } catch (\Exception $e) {
            $this->translationStateInterface->resume(true);
        }
    }

    public function getcustomerInformationEmail($customerId)
    {
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId(1);
        $customerModel = $customer->loadByEmail($customerId);
        $this->_customerInfos = $customerModel;
        return $customerModel;
        // $isSupplier = $customerModel->getEccErpAccountType();
    }

    public function sendDailyCondition($rfqsCron, string $checkCondition, string $functionVals, string $getSentAt, string $setSentAt, string $enableValues, string $emailTitle)
    {
        $rfqsCron = $this->convertArrayToObject($rfqsCron);
        $susdEnabled = $this->checkSusdEnabled();
        if ($susdEnabled) {
            $curentDate = $this->_localeDate->date()->format("Ymd");
            $curentDbDate = $this->_localeDate->date()->format("Y-m-d H:i:s");
            $currentTimestamp = strtotime($curentDate);
            $getColumnsSentAt = $this->_localeDate->date($rfqsCron->{"$getSentAt"}())->format("Ymd");
            $exactDate = $curentDate == date('Ymd', strtotime($getColumnsSentAt));
            $interval = strtotime($getColumnsSentAt) - $currentTimestamp;
            $days = floor($interval / 86400); // 1 day
            $intervalColumnDate = array('-2', '-1');
            $previous = false;
            if ((strtotime($curentDate) > strtotime($getColumnsSentAt)) && (in_array($days, $intervalColumnDate))) {
                $previous = true;
            }
            if ($rfqsCron->{"$enableValues"}() && ((!$exactDate && $previous) || ($rfqsCron->{"$getSentAt"}() == "0000-00-00 00:00:00"))) {
                $collections = $this->updateSupplierConnect($rfqsCron->getId());
                $collections->{"$setSentAt"}($curentDbDate);
                $collections->save();
                return true;
            } else {
                if (abs($days) > 3) {
                    $collections = $this->updateSupplierConnect($rfqsCron->getId());
                    $collections->{"$setSentAt"}($curentDbDate);
                    $collections->save();
                    return false;
                } else {
                    return false;
                }
            }
        }
    }

    public function convertArrayToObject($items) {
        $arrayItems[0] = $items;
        $collection = $this->collectionFactory->create();
        foreach ($arrayItems as $item) {
            $varienObject = new \Magento\Framework\DataObject();
            $varienObject->setData($item);
            $collection->addItem($varienObject);
        }

        return $collection->getFirstItem();
    }

    public function checkSusdEnabled()
    {
        $message = $this->supplierconnectMessageRequestSusd;
        /* @var $message Epicor_Supplierconnect_Model_Message_Request_Susd */
        $helper = $message->getHelper("supplierconnect/messaging");
        $messageTypeCheck = $helper->getMessageType('SUSD');
        if (($message->isActive()) && ($messageTypeCheck)) {
            return true;
        } else {
            return false;
        }
    }

    public function updateSupplierConnect($id) {
        $collection = $this->supplierReminderFactory->create();
        $collection->addFieldToFilter('id',$id);
        return $collection->getFirstItem();
    }

    public function sendWeeklyCondition($rfqsCron, $checkCondition, $functionVals, $getSentAt, $setSentAt, $enableValues, $emailTitle)
    {
        $rfqsCron = $this->convertArrayToObject($rfqsCron);
        $susdEnabled = $this->checkSusdEnabled();
        if ($susdEnabled) {
            $curentDate = $this->_localeDate->date()->format("Ymd");
            $curentDbDate = $this->_localeDate->date()->format("Y-m-d H:i:s");
            $currentTimestamp = strtotime($curentDate);
            $formatColumnSentAt = $this->_localeDate->date($rfqsCron->{"$getSentAt"}())->format("Ymd");

            $exactDate = $curentDate == date('Ymd', strtotime($formatColumnSentAt));
            $intervalDate = strtotime($formatColumnSentAt) - $currentTimestamp;
            $daysThisUpcoming = floor($intervalDate / 86400); // 1 day
            $previousDaily = false;
            if ((strtotime($curentDate) > strtotime($formatColumnSentAt))) {
                $previousDaily = true;
            }
            $UpcomingMonday = date('w', strtotime($curentDate));
            if ($rfqsCron->{"$enableValues"}() && ((!$exactDate && $previousDaily && $UpcomingMonday == "1") || ($rfqsCron->{"$getSentAt"}() == "0000-00-00 00:00:00" && $UpcomingMonday == "1"))) {
                $collections = $this->updateSupplierConnect($rfqsCron->getId());
                $collections->{"$setSentAt"}($curentDbDate);
                $collections->save();
                return true;
            } else {
                if (abs($daysThisUpcoming) > 3) {
                    $collections = $this->updateSupplierConnect($rfqsCron->getId());
                    $collections->{"$setSentAt"}($curentDbDate);
                    $collections->save();
                    return false;
                } else {
                    return false;
                }
            }
        }
    }

    public function sendMonthlyCondition($rfqsCron, $checkCondition, $functionVals, $getSentAt, $setSentAt, $enableValues, $emailTitle)
    {
        $rfqsCron = $this->convertArrayToObject($rfqsCron);
        $susdEnabled = $this->checkSusdEnabled();
        if ($susdEnabled) {
            $curentDate = $this->_localeDate->date()->format("Ymd");
            $curentDbDate = $this->_localeDate->date()->format("Y-m-d H:i:s");
            $currentTimestamp = strtotime($curentDate);
            $formatColumnSentAt = $this->_localeDate->date($rfqsCron->{"$getSentAt"}())->format("Ymd");

            $exactUpcomingDate = $curentDate == date('Ymd', strtotime($formatColumnSentAt));
            $intervalDate = strtotime($formatColumnSentAt) - $currentTimestamp;
            $daysThisUpcoming = floor($intervalDate / 86400); // 1 day
            $previousMonthly = false;
            if ((strtotime($curentDate) > strtotime($formatColumnSentAt))) {
                $previousMonthly = true;
            }
            $UpcomingMonth = date('Ym01', strtotime('this month'));
            if (($rfqsCron->{"$enableValues"}()) && ((!$exactUpcomingDate && $previousMonthly && $UpcomingMonth == $curentDate) || ($rfqsCron->{"$getSentAt"}() == "0000-00-00 00:00:00" && $UpcomingMonth == $curentDate))) {
                $collections = $this->updateSupplierConnect($rfqsCron->getId());
                $collections->{"$setSentAt"}($curentDbDate);
                $collections->save();
                return true;
            } else {
                if (abs($daysThisUpcoming) > 3) {
                    $collections = $this->updateSupplierConnect($rfqsCron->getId());
                    $collections->{"$setSentAt"}($curentDbDate);
                    $collections->save();
                    return false;
                } else {
                    return false;
                }
            }

        }
    }

    public function sendDueThisWeek($rfqsCron)
    {
        $rfqsCron = $this->convertArrayToObject($rfqsCron);
        $susdEnabled = $this->checkSusdEnabled();
        if ($susdEnabled) {
            $curentDate = $this->_localeDate->date()->format("Ymd");
            $curentDbDate = $this->_localeDate->date()->format("Y-m-d H:i:s");
            $currentTimestamp = strtotime($curentDate);
            $RfqsDueThisWeekSent = $this->_localeDate->date($rfqsCron->getRfqsDueThisWeekSentAt())->format("Ymd");
            $exactThisWeekDate = $curentDate == date('Ymd', strtotime($RfqsDueThisWeekSent));
            $intervalThisWeekDate = strtotime($RfqsDueThisWeekSent) - $currentTimestamp;
            $daysThisWeekDate = floor($intervalThisWeekDate / 86400); // 1 day
            $previousThisWeek = false;
            $intervalColumnDate = array('-2', '-1');
            if ((strtotime($curentDate) > strtotime($RfqsDueThisWeekSent)) && (in_array($daysThisWeekDate, $intervalColumnDate))) {
                $previousThisWeek = true;
            }
            if ($rfqsCron->getRfqsDueThisWeekEnable() && ((!$exactThisWeekDate && $previousThisWeek) || ($rfqsCron->getRfqsDueThisWeekSentAt() == "0000-00-00 00:00:00"))) {
                $collections = $this->updateSupplierConnect($rfqsCron->getId());
                $collections->setRfqsDueThisWeekSentAt($curentDbDate);
                $collections->save();
                return true;
            } else {
                if (abs($daysThisWeekDate) > 3) {
                    $collections = $this->updateSupplierConnect($rfqsCron->getId());
                    $collections->setRfqsDueThisWeekSentAt($curentDbDate);
                    $collections->save();
                    return false;
                } else {
                    return false;
                }
            }
        }
    }

    public function checkExpiredOrNot($rfqsCron)
    {
        $reminderExpiryEnable = $rfqsCron['reminder_expiry_date_enable'];
        if ($reminderExpiryEnable) {
            $curentDate = date("Y-m-d");
            $expiryDate = $rfqsCron['reminder_expiry_date'];
            $reminderSent = date("Y-m-d", strtotime($expiryDate));
            $currentTimestamp = strtotime($curentDate);
            $intervalDate = strtotime($reminderSent) - $currentTimestamp;
            $days = floor($intervalDate / 86400);
            if ($days < 0) {
                $collections = $this->updateSupplierConnect($rfqsCron->getId());
                $collections->delete();
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    public function reminderExpirySupplier($rfqsCron)
    {
        $reminderExpiryEnable = $rfqsCron->getReminderExpiryDateEnable();
        $sendEmail = $rfqsCron->getEmailReminderEnable();
        if ($reminderExpiryEnable && $sendEmail) {
            $curentDate = date("Y-m-d");
            $curentDbDate = $this->_localeDate->date()->format("Y-m-d H:i:s");
            $expiryDate = $rfqsCron->getReminderExpiryDate();
            $reminderSent = date("Y-m-d", strtotime($expiryDate));
            $currentTimestamp = strtotime($curentDate);
            $intervalDate = strtotime($reminderSent) - $currentTimestamp;
            $days = floor($intervalDate / 86400);
            $reminderSent = ($rfqsCron->getReminderEmailSentAt()  =="0000-00-00 00:00:00") ? "0000-00-00 00:00:00": date("Y-m-d", strtotime($rfqsCron->getReminderEmailSentAt()));
            if (($days == "7") && ($curentDate != $reminderSent)) {
                $this->sendExpiryReminder($expiryDate, $rfqsCron->getCustomerId(), "Before 7 Days");
                $rfqsCron->setReminderEmailSentAt($curentDbDate);
                $rfqsCron->save();
            }
            if (($days == "30") && ($curentDate != $reminderSent)) {
                $this->sendExpiryReminder($expiryDate, $rfqsCron->getCustomerId(), "Before 30 Days");
                $rfqsCron->setReminderEmailSentAt($curentDbDate);
                $rfqsCron->save();
            }
            if ($days < 0) {
                $this->logger->info('Reminder Expired & Deleted:' . $expiryDate . " " . $rfqsCron->getCustomerId());
                $rfqsCron->delete();
            }
        }
    }

    public function sendExpiryReminder($expiryDate, $customerId, $expiryMsg)
    {
        $customerModel = $this->getcustomerInformation($customerId);
        $this->logger->info('Expiry Reminder : ' . $expiryMsg . " " . $expiryDate . " " . $customerModel->getEmail());
        $name = $customerModel->getName();
        $translate = $this->translationStateInterface;
        /* @var $translate Mage_Core_Model_Translate */
        $translate->suspend();
        $templateVars = array(
            'name' => $name,
            'expirydate' => $expiryDate,
            'supplierUrl' => $this->urlBuilder->getUrl('supplierconnect/account/index')
        );
        $fromEmail = $this->scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $from = array('email' => $fromEmail, 'name' => 'admin');
        $storeId = $this->storeManager->getStore()->getId();
        $to = array($customerModel->getEmail());
        //$to = array('arjun.manoharan@epicor.com');
        $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId);
        try {
            $transport = $this->transportBuilder->setTemplateIdentifier('supplier_email_expiry_email_template')
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($to)
                ->getTransport();
            $transport->sendMessage();
            $this->translationStateInterface->resume(true);

        } catch (\Exception $e) {
            $this->translationStateInterface->resume(true);
        }
    }

    public function getcustomerInformation($customerId)
    {
        $customer = $this->customerFactory->create();
        $customerModel = $customer->load($customerId);
        $this->_customerInfos = $customerModel;
        return $customerModel;
    }
}