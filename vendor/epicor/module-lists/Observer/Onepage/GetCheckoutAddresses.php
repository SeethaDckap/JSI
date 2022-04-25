<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Observer\Onepage;

class GetCheckoutAddresses extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->listsFrontendContractHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Contract */

        $type = $observer->getEvent()->getType();

        $quote = $observer->getEvent()->getQuote();
        /* @var $quote Epicor_Comm_Model_Quote */

        if (($helper->contractsDisabled()) || (empty($quote))) {
            return;
        }


        $contracts = $helper->getQuoteContracts($quote);

        $addressData = $observer->getEvent()->getAddresses();
        /* @var $addresses Varien_Object */

        if ($contracts && $type == 'delivery') {
            $customerSession = $this->customerSession;
            /* @var $customerSession Mage_Customer_Model_Session */
            $customer = $customerSession->getCustomer();
            /* @var $customer Epicor_Comm_Model_Customer */
            $customerAddresses = $customer->getAddressesByType('delivery');
            $contractAddresses = $helper->getValidShippingAddressCodesForContracts($contracts);

            $filteredAddresses = array();
            foreach ($customerAddresses as $address) {
                if (in_array($address->getEccErpAddressCode(), $contractAddresses)) {
                    $filteredAddresses[] = $address;
                }
            }

            $addressData->setLoadAddresses(false);
            $addressData->setAddresses($filteredAddresses);
        }
    }

}