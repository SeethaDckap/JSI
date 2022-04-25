<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Helper;


class Reminder extends \Epicor\Comm\Helper\Messaging
{

    /**
     * @var \Epicor\Common\Helper\Locale\Format\Date
     */


    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    protected $translationStateInterface;

    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    protected $_localeDate;


    protected $_customerInfos;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    protected $logger;

    protected $dealerReminderFactory;

    public function __construct(
        \Epicor\Comm\Helper\Messaging\Context $context,
        \Epicor\Dealerconnect\Logger\Logger $logger,
        \Epicor\Dealerconnect\Model\ResourceModel\DealerReminder\CollectionFactory $dealerReminderFactory
    )
    {
        $this->transportBuilder = $context->getEmailTemplateFactory();
        $this->translationStateInterface = $context->getTranslateInterface();
        $this->storeManager = $context->getStoreManager();
        $this->customerFactory = $context->getCustomerFactory();
        $this->_localeDate = $context->getTimezone();
        $this->urlBuilder = $context->getUrlBuilder();
        $this->logger = $logger;
        $this->dealerReminderFactory = $dealerReminderFactory;
        parent::__construct($context);
    }


    /**
     * Send Reminder Email
     * @param string $messageReference
     * @param string $referenceName
     * @param string/object $customerModel
     * @return mixed
     */

    public function sendclaimsReminder($messageReference, $referenceName, $customerModel)
    {
        if (is_string($customerModel)) {
            $customerModel = $this->getcustomerInformationEmail($customerModel);
        }
        $name = $customerModel->getName();
        $translate = $this->translationStateInterface;
        /* @var $translate Mage_Core_Model_Translate */
        $translate->suspend();
        $templateVars = array(
            'name' => $name,
            'epicreminderreference' => $referenceName,
            'messageReference' => $messageReference,
            'dealerUrl' => $this->urlBuilder->getUrl('dealerconnect/dashboard/index')
        );
        $fromEmail = $this->scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $from = array('email' => $fromEmail, 'name' => 'admin');
        $storeId = $this->storeManager->getStore()->getId();
        $to = array($customerModel->getEmail());
        //$to = array('pradeep.kumar@epicor.com');
        $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId);
        try {
            $transport = $this->transportBuilder->setTemplateIdentifier('dealer_email_clamis_reminder_email_template')
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
        if (is_string($customerId)) {
            $customerModel = $customer->loadByEmail($customerId);
        }
        if (is_numeric($customerId)) {
            $customerModel = $customer->load($customerId);
        }
        $this->_customerInfos = $customerModel;
        return $customerModel;
    }

    /**
     * Update Reminder date and send email
     * @param array $data
     * @param string $field
     * @param string $frequency
     * @param array $emailData
     * @return boolean
     */

    public function saveCondition($data, $field, $frequency, $emailData)
    {
        $curentDate = $this->_localeDate->date()->format("Ymd");
        $curentDbDate = $this->_localeDate->date()->format("Y-m-d H:i:s");
        $currentTimestamp = strtotime($curentDate);
        $getColumnsSentAt = $this->_localeDate->date($data[$field])->format("Ymd");
        $exactDate = $curentDate == date('Ymd', strtotime($getColumnsSentAt));
        $interval = strtotime($getColumnsSentAt) - $currentTimestamp;
        $days = floor($interval / 86400); // 1 day
        $intervalColumnDate = array('-2', '-1');
        $previous = false;
        $extracheck = true;
        if ($frequency == "daily") {
            if ((strtotime($curentDate) > strtotime($getColumnsSentAt)) && (in_array($days, $intervalColumnDate))) {
                $previous = true;
            }
        }
        if ($frequency == "weekly") {
            if ((strtotime($curentDate) > strtotime($getColumnsSentAt))) {
                $previous = true;
            }
            $UpcomingMonday = date('w', strtotime($curentDate));
            if ($UpcomingMonday != "1") {
                $extracheck = false;
            }
        }
        if ($frequency == "monthly") {
            if ((strtotime($curentDate) > strtotime($getColumnsSentAt))) {
                $previous = true;
            }
            $UpcomingMonth = date('Ym01', strtotime('this month'));
            if ($UpcomingMonth != $curentDate) {
                $extracheck = false;
            }
        }

        if (((!$exactDate && $previous) && $extracheck || ($data[$field] == "0000-00-00 00:00:00" && $extracheck))) {
            $collections = $this->updateDealerConnect($data['id']);
            $collections->setData($field, $curentDbDate);
            $this->sendclaimsReminder($emailData['messageReference'], $emailData['referenceName'], $emailData['email']);
            $collections->save();
            return true;
        } else {
            return false;
        }

    }

    public function updateDealerConnect($id)
    {
        $collection = $this->dealerReminderFactory->create();
        $collection->addFieldToFilter('id', $id);
        return $collection->getFirstItem();
    }


    /**
     * Check Reminder is expired or Not
     * @param array $rfqsCron
     * @return boolean
     */

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
                //  $collections = $this->updateDealerConnect($rfqsCron->getId());
                //  $collections->delete();
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }


    /**
     * Get the Reminder Expiry date and send Email
     * @param object $claimsCron
     * @return void
     */
    public function reminderExpiryDealer($claimsCron)
    {
        $reminderExpiryEnable = $claimsCron->getReminderExpiryDateEnable();
        $sendEmail = $claimsCron->getEmailReminderEnable();
        if ($reminderExpiryEnable && $sendEmail) {
            $curentDate = date("Y-m-d");
            $curentDbDate = $this->_localeDate->date()->format("Y-m-d H:i:s");
            $expiryDate = $claimsCron->getReminderExpiryDate();
            $reminderSent = date("Y-m-d", strtotime($expiryDate));
            $currentTimestamp = strtotime($curentDate);
            $intervalDate = strtotime($reminderSent) - $currentTimestamp;
            $days = floor($intervalDate / 86400);
            $reminderSent = ($claimsCron->getReminderEmailSentAt() == "0000-00-00 00:00:00") ? "0000-00-00 00:00:00" :
                date("Y-m-d", strtotime($claimsCron->getReminderEmailSentAt()));
            if (($days == "7") && ($curentDate != $reminderSent)) {
                $this->sendExpiryReminder($expiryDate, $claimsCron->getCustomerId(), "Before 7 Days");
                $claimsCron->setReminderEmailSentAt($curentDbDate);
                $claimsCron->save();
            }
            if (($days == "30") && ($curentDate != $reminderSent)) {
                $this->sendExpiryReminder($expiryDate, $claimsCron->getCustomerId(), "Before 30 Days");
                $claimsCron->setReminderEmailSentAt($curentDbDate);
                $claimsCron->save();
            }
            if ($days < 0) {
                //$this->logger->info('Reminder Expired & Deleted:' . $expiryDate . " " . $claimsCron->getCustomerId());
                $claimsCron->delete();
            }
        }
    }

    /**
     * Send Expiry Reminder Email
     * @param date $expiryDate
     * @param int $expiryDate
     * @param date $expiryMsg
     * @return void
     */
    public function sendExpiryReminder($expiryDate, $customerid, $expiryMsg)
    {
        $customerModel = $this->getcustomerInformationEmail($customerid);
        //$this->logger->info('Expiry Reminder : ' . $expiryMsg . " " . $expiryDate . " " . $customerModel->getEmail());
        $name = $customerModel->getName();
        $translate = $this->translationStateInterface;
        /* @var $translate Mage_Core_Model_Translate */
        $translate->suspend();
        $templateVars = array(
            'name' => $name,
            'expirydate' => $expiryDate,
            'dealerUrl' => $this->urlBuilder->getUrl('dealerconnect/dashboard/index')
        );
        $fromEmail = $this->scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $from = array('email' => $fromEmail, 'name' => 'admin');
        $storeId = $this->storeManager->getStore()->getId();
        $to = array($customerModel->getEmail());
        //$to = array('pradeep.kumar@epicor.com');

        $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId);
        try {
            $transport = $this->transportBuilder->setTemplateIdentifier('dealer_email_expiry_email_template')
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

    /**
     * Load Customer Model
     * @param int/string customerid/email
     * @return object
     */

    public function getcustomerInformation($customerId)
    {
        $customer = $this->customerFactory->create();
        $customerModel = $customer->load($customerId);
        $this->_customerInfos = $customerModel;
        return $customerModel;
    }
}