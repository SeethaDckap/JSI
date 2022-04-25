<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Helper;


use Epicor\Comm\Helper\Context;

class Checkout extends \Epicor\SalesRep\Helper\Data
{

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;
    
    /**
     * @var \Epicor\Comm\Model\Customer\Erpaccount\AddressFactory
     */
    protected $commCustomerErpaccountAddressFactory;
    
    public function __construct(
        \Epicor\Comm\Helper\Context $context,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper
    ) {
        $this->customerconnectHelper = $customerconnectHelper;
        $this->commCustomerErpaccountAddressFactory = $context->getCommCustomerErpaccountAddressFactory();
        parent::__construct($context);
    }
    public function isChooseContactEnabled()
    {
        if ($this->isEnabled() && $this->scopeConfig->isSetFlag('epicor_salesrep/checkout/choose_contact_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {

            $customerSession = $this->customerSessionFactory->create();

            /* @var $customerSession Mage_Customer_Model_Session */

            $customer = $customerSession->getCustomer();
            /* @var $customer Epicor_Comm_Model_Customer */

            if ($customer->isSalesRep() && $this->isMasquerading()) {
                $contacts = $this->getSalesErpContacts();
                if (!empty($contacts)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function isChooseContactRequired()
    {
        return $this->scopeConfig->isSetFlag('epicor_salesrep/checkout/choose_contact_required', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getSalesErpContacts()
    {
        $contactData = $this->registry->registry('salesrep_erp_contacts');

        if (empty($contactData) && $contactData !== false) {
            $helper = $this->customerconnectHelper;
            /* @var $helper Epicor_Customerconnect_Helper_Data */

            $erpAccount = $helper->getErpAccountNumber();
            $erpAccountNumber = $helper->getErpAccountNumber();

            $data = array(
                'account_number' => $erpAccountNumber,
                //M1 > M2 Translation Begin (Rule p2-6.4)
                //'language_code' => $helper->getLanguageMapping(Mage::app()->getLocale()->getLocaleCode())
                'language_code' => $helper->getLanguageMapping(
                    $this->scopeConfig->getValue(
                        'general/locale/code',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $this->storeManager->getStore()->getStoreId()
                    )
                )
                //M1 > M2 Translation End

            );

            $sentMessage = $helper->sendErpMessage('customerconnect', 'cuad', $data);

            $contacts = array();

            if ($sentMessage['success']) {
                $message = $sentMessage['message'];
                /* @var $message Epicor_Customerconnect_Model_Message_Request_Cuad */
                $results = $message->getResults();
                $contacts = $message->_getGroupedData('contacts', 'contact', $results);
            }

            $contactData = array();
            if(empty($contacts)){
                $this->registry->unregister('salesrep_erp_contacts');
                $this->registry->register('salesrep_erp_contacts', false);
                return $contactData;
            }
            foreach ($contacts as $contact) {

                $info = array(
                    'contact_code' => $contact->getContactCode(),
                    'name' => $contact->getName(),
                    'function' => $contact->getFunction(),
                    'email' => $contact->getEmail() ?: $contact->getEmailAddress(),
                    'telephone_number' => $contact->getTelephoneNumber(),
                    'fax_number' => $contact->getFaxNumber(),
                    'ecc_login_id' => $contact->getLoginId()
                );

                $info['basedata'] = base64_encode(serialize($info));
                $contactData[] = $this->dataObjectFactory->create()->addData($info);
            }

            $this->registry->unregister('salesrep_erp_contacts');
            $this->registry->register('salesrep_erp_contacts', $contactData);
        }

        return $contactData;
    }
    
    /*
     *  This method need to be updated when Salesrep Select a
     *  Contact person for the ERP account so all the commented code
     * will be updated according to the Magento 2 In order load Contact
     *  person address data.
     */
    
    public function _getMasqAddressData($customerAddressId,$quote)
    {
        $customerSession = $this->customerSessionFactory->create();
        $customer = $customerSession->getCustomer();
        
        /* @var $customer Epicor_Comm_Model_Customer */
      
        if ($customer->isSalesRep()) {
            $customerId = $quote->getEccSalesrepChosenCustomerId();
            if ($customerId) {
                $salesRepCustomer = $this->customerCustomerFactory->create()->load($customerId);
                
            } else {
                $customerInfo = unserialize($quote->getEccSalesrepChosenCustomerInfo());
                
                if (isset($customerInfo['name']) && $customerInfo['name']) {
                    $salesRepCustomer = $this->customerCustomerFactory->create();
                    
                    $CustDataModel = $quote->getCustomer();
                    $CustDataModel->setEmail($customerInfo['email']);
                    $quote->setCustomer($CustDataModel); 
                    
                   // $quote->setCustomerEmail($customerInfo['email']);
                    $nameParts = explode(' ', $customerInfo['name'], 3);
                    $salesRepCustomer->setFirstname($nameParts[0]);

                    if (count($nameParts) == 3) {
                        $salesRepCustomer->setMiddlename($nameParts[1]);
                        $salesRepCustomer->setLastname($nameParts[2]);
                    } else {
                        $salesRepCustomer->setLastname($nameParts[1]);
                    }
                    $salesRepCustomer->setEmail($customerInfo['email']);
                } else {
                    $salesRepCustomer = $customer;
                } 
            }
        } 
        
        
        if (strpos($customerAddressId, 'erpaddress_') !== false) {
            $addressId = str_replace('erpaddress_', '', $customerAddressId);

            $address = $this->commCustomerErpaccountAddressFactory->create()->load($addressId);
            // @var $address Epicor_Comm_Model_Customer_Erpaccount_Address //

            if ($customer->isSalesRep()) {
                $addressData = $address->toCustomerAddress($salesRepCustomer)->getData();
            } else {
                $addressData = $address->toCustomerAddress($customer)->getData();
            }
        } 
            
        if ($customer->isSalesRep()) {
            $addressData['email'] = $salesRepCustomer->getEmail();
        }
        return $addressData;
        
    }
    
}
