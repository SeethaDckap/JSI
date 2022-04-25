<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Observer\Onepage;

class GetCheckoutAddresses extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
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

        $quote = $observer->getEvent()->getQuote();
        /* @var $quote Epicor_Comm_Model_Quote */

        $addresses = $observer->getEvent()->getAddresses();
        /* @var $addresses Varien_Object */

        $restrict = $observer->getEvent()->getRestrictByType();
        $type = $observer->getEvent()->getType();

        if ($quote->getEccSalesrepChosenCustomerId()) {
            $customerId = $quote->getEccSalesrepChosenCustomerId();

            $salesRepCustomer = $this->customerCustomerFactory->create()->load($customerId);
            /* @var $salesRepCustomer Epicor_Comm_Model_Customer */

            $collection = $salesRepCustomer->getAddressCollection()
                ->setCustomerFilter($salesRepCustomer)
                ->addAttributeToSelect('*')
                ->addAttributeToSelect('ecc_is_invoice', 'left')
                ->addAttributeToSelect('ecc_is_delivery', 'left')
                ->addAttributeToSelect('ecc_is_registered', 'left')
                ->addExpressionAttributeToSelect(
                'is_custom', 'IF((NOT(`at_ecc_is_invoice`.value <=> 1) AND NOT(`at_ecc_is_delivery`.value <=> 1) AND NOT(`at_ecc_is_registered`.value <=> 1)), 1 , 0)', array('ecc_is_invoice', 'ecc_is_delivery', 'ecc_is_registered')
            );

            if ($restrict) {
                $collection->getSelect()
                    ->where(
                        '(`at_is_ecc_' . $type . '`.value = 1) ' .
                        'OR (NOT(`at_ecc_is_invoice`.value <=> 1) AND NOT(`at_ecc_is_delivery`.value <=> 1) AND NOT(`at_ecc_is_registered`.value <=> 1))'
                );
            }

            $addressData = array();

            foreach ($collection->getItems() as $address) {
                /* @var $address Mage_Customer_Model_Address */
                //$address->setId('customeraddress_' . $address->getId());
                $addressErpGroupCode = $address->getEccErpGroupCode();
                $eccErpAddressCode = $address->getEccErpAddressCode();
                if($addressErpGroupCode && $eccErpAddressCode) {
                   $addressId =  $this->getErpAddressEntityId($eccErpAddressCode,$addressErpGroupCode);
                   if(!empty($addressId)) {
                       $address->setId("erpaddress_".$addressId);
                   }
                }
                
                $addressData[$address->getId()] = $address;
            }

            $addresses->setLoadAddresses(false);
            $addresses->setAddresses($addressData);
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

                $erpAddresses = ($restrict) ? $salesRepCustomer->getAddressesByType($type) : $customer->getCustomAddresses();

                $addresses->setLoadAddresses(false);
                $addresses->setAddresses($erpAddresses);
            }
        }
    }
    
    public  function getErpAddressEntityId($erpCode,$erpCustomerGroupCode)
    {
        $address_model = $this->commCustomerErpaccountAddressFactory->create();
        $erpCustomerGroupAddressColl = $address_model->getCollection();
        $erpCustomerGroupAddressColl->addFieldToFilter('erp_code', $erpCode);
        $erpCustomerGroupAddressColl->addFieldToFilter('erp_customer_group_code', $erpCustomerGroupCode);
        $erpCustomerGroupAddressColl->getSelect();
        $erpCustomerGroupAddress = $erpCustomerGroupAddressColl->getFirstItem();
        $getId = '';
        if(!empty($erpCustomerGroupAddress)) {
           $getId =  $erpCustomerGroupAddress->getId();
        }
        return $getId;
    }

}