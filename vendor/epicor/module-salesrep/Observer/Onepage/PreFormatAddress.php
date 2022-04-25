<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Observer\Onepage;

class PreFormatAddress extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
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

        $controller = $this->request->getControllerName();

        $quote = $this->checkoutSession->getQuote();
        /* @var $quote Epicor_Comm_Model_Quote */

        $address = $observer->getEvent()->getAddress();
        /* @var $address Mage_Sales_Model_Quote_Address */

        $type = $observer->getEvent()->getType();

        if (
            $controller == 'onepage' &&
            ($customer->isCustomer() || $customer->isSalesRep())
        ) {
            $salesrep = ($customer->isSalesRep() && $helper->isMasquerading());
            $contact = $quote->getEccSalesrepChosenCustomerId() . $quote->getEccSalesrepChosenCustomerInfo();

            $nameMatch = $address->getName() == $customer->getName();

            $origFormatString = $type->getDefaultFormat();

            if (
                $address->getAddressType() == 'billing' ||
                $this->registry->registry('billing_address_checkout') ||
                ($salesrep && empty($contact) && $nameMatch)
            ) {

                $split = $type->getCode() == 'html' ? "\n" : ',';

                $newFormat = array();

                $formatArray = explode($split, $origFormatString);

                $searches = array(
                    '{{var prefix}}',
                    '{{var firstname}}',
                    '{{var middlename}}',
                    '{{var lastname}}',
                    '{{var suffix}}',
                );

                foreach ($formatArray as $row) {
                    $formatString = str_replace($searches, '', $row);
                    if (strpos($formatString, 'var') !== false) {
                        $newFormat[] = $formatString;
                    }
                }

                $newFormatString = implode($split, $newFormat);

                if (!$type->getOrigDefaultFormat()) {
                    $type->setOrigDefaultFormat($origFormatString);
                }
                $type->setDefaultFormat($newFormatString);
            } else {
                if ($type->getOrigDefaultFormat()) {
                    $type->setDefaultFormat($type->getOrigDefaultFormat());
                }
            }
        }

        return;
    }

}