<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Ui\Component\Form;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class CustomerFormValidation extends \Magento\Ui\Component\Form\Field {

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    private $storeManager;

    /**
     * DefaultValue constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
    ContextInterface $context, UiComponentFactory $uiComponentFactory, ScopeConfigInterface $scopeConfig, array $components = [], array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare() {
        parent::prepare();
        $addressLimits = $this->scopeConfig->getValue('customer/address/limits_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $streetLine = $this->scopeConfig->getValue('customer/address/street_lines', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ? $this->scopeConfig->getValue('customer/address/street_lines', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) : 255;
        if($streetLine == 1 && ($this->_data['name'] == "street_1" || $this->_data['name'] == "street_2" || $this->_data['name'] == "street_3")){
            $this->_data['config']['visible'] = "false";
        }
        if($streetLine == 2 && ($this->_data['name'] == "street_2" || $this->_data['name'] == "street_3")){
            $this->_data['config']['visible'] = "false";
        }
        if($streetLine == 3 && ($this->_data['name'] == "street_3")){
            $this->_data['config']['visible'] = "false";
        }
        if ($addressLimits) {
            $emailLimit = $this->scopeConfig->getValue('customer/address/limit_email_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?: 255;
            $nameLimit = $this->scopeConfig->getValue('customer/address/limit_name_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?: 255;
            $lastnameLimit = $this->scopeConfig->getValue('customer/address/limit_lastname_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?: 255;
            $companyLimit = $this->scopeConfig->getValue('customer/address/limit_company_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?: 255;
            $addressLimit = $this->scopeConfig->getValue('customer/address/limit_address_line_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?: 255;
            $telephoneLimit = $this->scopeConfig->getValue('customer/address/limit_telephone_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?: 255;
            $instructionsLimit = $this->scopeConfig->getValue('customer/address/limit_instructions_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?: 10234;
            $postalcodeLimit = $this->scopeConfig->getValue('customer/address/limit_postcode_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?: 255;
            if ($this->_data['name'] == "ecc_email") {
                $this->_data['config']['maxlength'] = $emailLimit;
                $this->_data['config']['notice'] = "Max " . $emailLimit . " chars";
            }
            if ($this->_data['name'] == "firstname") {
                $this->_data['config']['maxlength'] = $nameLimit;
                $this->_data['config']['notice'] = "Max " . $nameLimit . " chars";
            }
            if ($this->_data['name'] == "lastname") {
                $this->_data['config']['maxlength'] = $lastnameLimit;
                $this->_data['config']['notice'] = "Max " . $lastnameLimit . " chars";
            }
            if ($this->_data['name'] == "company") {
                $this->_data['config']['maxlength'] = $companyLimit;
                $this->_data['config']['notice'] = "Max " . $companyLimit . " chars";
            }
            if ($this->_data['name'] == "telephone" || $this->_data['name'] == "fax" || $this->_data['name'] == "ecc_mobile_number" || $this->_data['name'] == "ecc_telephone_number" || $this->_data['name'] == "ecc_fax_number") {
                $this->_data['config']['maxlength'] = $telephoneLimit;
                $this->_data['config']['notice'] = "Max " . $telephoneLimit . " chars";
            }
            if ($this->_data['name'] == "street_0" || $this->_data['name'] == "street_1" || $this->_data['name'] == "street_2" || $this->_data['name'] == "street_3") {
                $this->_data['config']['maxlength'] = $addressLimit;
                $this->_data['config']['notice'] = "Max " . $addressLimit . " chars";
            }
            if ($this->_data['name'] == "ecc_instructions") {
                $this->_data['config']['maxlength'] = $instructionsLimit;
                $this->_data['config']['notice'] = "Max " . $instructionsLimit . " chars";
            }
            if ($this->_data['name'] == "postcode") {
                $this->_data['config']['maxlength'] = $postalcodeLimit;
                $this->_data['config']['notice'] = "Max " . $postalcodeLimit . " chars";
            }
        } else {
            if ($this->_data['name'] == "ecc_instructions") {
                $this->_data['config']['maxlength'] = "10234";
            } else {
                $this->_data['config']['maxlength'] = "255";
            }
        }
    }

}
