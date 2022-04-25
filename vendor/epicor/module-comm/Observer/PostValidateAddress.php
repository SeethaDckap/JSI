<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class PostValidateAddress extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $errors = array();
        $address = $observer->getEvent()->getAddress();
        if (!\Zend_Validate::is($address->getFirstname(), 'NotEmpty')) {
            $errors[] = __('Please enter the first name.');
        }

        if (!\Zend_Validate::is($address->getLastname(), 'NotEmpty')) {
            $errors[] = __('Please enter the last name.');
        }

        if (!\Zend_Validate::is($address->getStreet(1), 'NotEmpty')) {
            $errors[] = __('Please enter the street.');
        }

        if (!\Zend_Validate::is($address->getCity(), 'NotEmpty')) {
            $errors[] = __('Please enter the city.');
        }

        // This is the reason this copy has been created. In 1.7 and below the dispatch event customer_address_validation_after doesn't exist
        // nor does the _resetErrors method 
        if ($this->scopeConfig->getValue('checkout/options/telephone_required', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            if (!\Zend_Validate::is($address->getTelephone(), 'NotEmpty')) {
                $errors[] = __('Please enter the telephone number.');
            }
        }
        if ($this->scopeConfig->isSetFlag('customer/address/display_mobile_phone', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            if (!\Zend_Validate::is($address->getEccMobileNumber(), 'NotEmpty')) {
                $errors[] = __('Please enter the mobile number.');
            }
        }

        $_havingOptionalZip = $this->directoryHelper->getCountriesWithOptionalZip();
        if (!in_array($address->getCountryId(), $_havingOptionalZip) && !\Zend_Validate::is($address->getPostcode(), 'NotEmpty')
        ) {
            $errors[] = __('Please enter the zip/postal code.');
        }

        if (!\Zend_Validate::is($address->getCountryId(), 'NotEmpty')) {
            $errors[] = __('Please enter the country.');
        }

        if ($address->getCountryModel()->getRegionCollection()->getSize() && !\Zend_Validate::is($address->getRegionId(), 'NotEmpty') && $this->directoryHelper->isRegionRequired($address->getCountryId())
        ) {
            $errors[] = __('Please enter the state/province.');
        }

        // check length limits
        $use_length_limits = $this->scopeConfig->isSetFlag('customer/address/limits_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($use_length_limits) {
            $name_length_limit = $this->scopeConfig->getValue('customer/address/limit_name_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ? $this->scopeConfig->getValue('customer/address/limit_name_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) : 10234;
            $address_length_limit = $this->scopeConfig->getValue('customer/address/limit_address_line_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ? $this->scopeConfig->getValue('customer/address/limit_address_line_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) : 10234;
            $telephone_length_limit = $this->scopeConfig->getValue('customer/address/limit_telephone_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ? $this->scopeConfig->getValue('customer/address/limit_telephone_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) : 10234;
            $instructions_length_limit = $this->scopeConfig->getValue('customer/address/limit_instructions_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ? $this->scopeConfig->getValue('customer/address/limit_instructions_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) : 10234;
            if ($address->getStreet()) {                                   // needed because the street fields have been merged into one field by this point
                if (is_array($address->getStreet())) {
                    foreach ($address->getStreet() as $key => $street) {
                        $address->setData('street_' . $key, $street);
                    }
                }
            }
            $nameArray = array(
                // used in pages: my address edit , billing, shipping
                array('code' => 'firstname', 'limit_name' => 'name_length_limit', 'message_prefix' => 'First Name')
                , array('code' => 'lastname', 'limit_name' => 'name_length_limit', 'message_prefix' => 'Last Name')
                , array('code' => 'name', 'limit_name' => 'name_length_limit', 'message_prefix' => 'Name')
                , array('code' => 'street_0', 'limit_name' => 'address_length_limit', 'message_prefix' => 'Street address line 1')
                , array('code' => 'street_1', 'limit_name' => 'address_length_limit', 'message_prefix' => 'Street address line 2')
                , array('code' => 'street_2', 'limit_name' => 'address_length_limit', 'message_prefix' => 'Street address line 3')
                , array('code' => 'telephone', 'limit_name' => 'telephone_length_limit', 'message_prefix' => 'Telephone')
                , array('code' => 'ecc_mobile_number', 'limit_name' => 'telephone_length_limit', 'message_prefix' => __('Mobile'))
                , array('code' => 'fax', 'limit_name' => 'telephone_length_limit', 'message_prefix' => 'Fax')
                , array('code' => 'ecc_instructions', 'limit_name' => 'instructions_length_limit', 'message_prefix' => 'Instructions')
            );
            foreach ($nameArray as $name) {

                if (in_array($name['code'], array('firstname', 'lastname')) && $address->getData('firstname') == 'validate' || ($name['code'] == 'telephone' && $address->getData($name['code']) == 'validateme')) {   // if not supplied in CUS, 'validate me' is added to firstname and lastname and validateme to telephone
                    continue;
                }

                $length_limit = ${$name['limit_name']};
                if ($address->getData($name['code']) && $length_limit != 10234) {
                    if (strlen($address->getData($name['code'])) > $length_limit) {
                        $errors[] = __("{$name['message_prefix']} cannot exceed {$length_limit} chars");
                    }
                }
            }

            if ($address->getShouldIgnoreValidation()) {
                return true;
            }
            foreach ($errors as $error) {
                $address->addError($error);
            }
        }
    }

}