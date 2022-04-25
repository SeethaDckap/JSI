<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Model;


class Cron
{

    /**
     * @var \Epicor\Quotes\Model\ResourceModel\Quote\CollectionFactory
     */
    protected $quotesResourceQuoteCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Epicor\Quotes\Model\ResourceModel\Quote\CollectionFactory $quotesResourceQuoteCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->quotesResourceQuoteCollectionFactory = $quotesResourceQuoteCollectionFactory;
        $this->scopeConfig = $scopeConfig;
    }
    public function checkedExpired()
    {

        $collection = $this->quotesResourceQuoteCollectionFactory->create();
        /* @var $collection Mage_Core_Model_Resource_Collection_Abstract */

        $collection->addFieldToFilter('status_id', array('in', array(
                    \Epicor\Quotes\Model\Quote::STATUS_AWAITING_ACCEPTANCE,
                    \Epicor\Quotes\Model\Quote::STATUS_PENDING_RESPONSE,
                    \Epicor\Quotes\Model\Quote::STATUS_QUOTE_ACCEPTED
                )
            ))
            ->load();

        $sendReminders = $this->scopeConfig->getValue('epicor_quotes/email_alerts/send_reminders', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($sendReminders !== 'none') {

            foreach ($collection->getItems() as $quote) {
                /* @var $quote Epicor_Quotes_Model_Quote */
                if (!$quote->checkExpired()) {
                    $remindersDays = explode(',', $this->scopeConfig->getValue('epicor_quotes/email_alerts/days_to_send_reminders', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));

                    foreach ($remindersDays as $numeric_day) {

                        if ($quote->getExpires() == date('Y-m-d', strtotime('+' . $numeric_day . 'days'))) {
                            //send Email reminder
                            $quote->sendCustomerReminderEmail();
                            $quote->sendAdminReminderEmail();
                            break;
                        }
                    }
                }
            }
        }
    }

}
