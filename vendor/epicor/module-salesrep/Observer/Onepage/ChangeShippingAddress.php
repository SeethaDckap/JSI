<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Observer\Onepage;

class ChangeShippingAddress extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\Registry
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customerSession = $this->customerSession;
        /* @var $customerSession Mage_Customer_Model_Session */

        $customer = $customerSession->getCustomer();
        /* @var $customer Epicor_Comm_Model_Customer */

        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */

        if (!$customer->isSalesRep() || !$helper->isMasquerading()) {
            return;
        }

        $address = $observer->getEvent()->getAddress();
        /* @var $address Mage_Sales_Model_Quote_Address */

        $quote = $observer->getEvent()->getQuote();
        /* @var $quote Epicor_Comm_Model_Quote */

        if ($quote->getEccSalesrepChosenCustomerId()) {
            $customerId = $quote->getEccSalesrepChosenCustomerId();

            $salesRepCustomer = $this->customerCustomerFactory->create()->load($customerId);
            /* @var $salesRepCustomer Epicor_Comm_Model_Customer */

            $address->setCustomer($salesRepCustomer);

            $address->setPrefix($salesRepCustomer->getPrefix());
            $address->setFirstname($salesRepCustomer->getFirstname());
            $address->setMiddlename($salesRepCustomer->getMiddlename());
            $address->setLastname($salesRepCustomer->getLastname());
            $address->setSuffix($salesRepCustomer->getSuffix());
        } else {
            $customerInfo = unserialize($quote->getEccSalesrepChosenCustomerInfo());

            if (isset($customerInfo['name'])) {

                $salesRepCustomer = $this->customerCustomerFactory->create();
                /* @var $salesRepCustomer Epicor_Comm_Model_Customer */

                $nameParts = explode(' ', $customerInfo['name'], 3);

                $salesRepCustomer->setFirstname($nameParts[0]);

                if (count($nameParts) == 3) {
                    $salesRepCustomer->setMiddlename($nameParts[1]);
                    $salesRepCustomer->setLastname($nameParts[2]);
                } else {
                    $salesRepCustomer->setLastname($nameParts[1]);
                }
                $salesRepCustomer->setEmail($customerInfo['email']);

                $address->setCustomer($salesRepCustomer);
                $address->setPrefix($salesRepCustomer->getPrefix());
                $address->setFirstname($salesRepCustomer->getFirstname());
                $address->setMiddlename($salesRepCustomer->getMiddlename());
                $address->setLastname($salesRepCustomer->getLastname());
                $address->setSuffix($salesRepCustomer->getSuffix());
            } else {
                $address->setPrefix('');
                $address->setFirstname('');
                $address->setMiddlename('');
                $address->setLastname('');
                $address->setSuffix('');
            }
        }
    }

}