<?php

/**
 * Copyright © 2010-2019 Epicor Software. All rights reserved.
 */

namespace Epicor\Comm\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

class AccountSummary implements SectionSourceInterface {

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    private $_erp_customer;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->customerSession = $customerSession;
        $this->_scopeConfig = $scopeConfig;
        $this->commHelper = $commHelper;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->_storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData() {
        if ($this->isErpCustomer()) {
            $accountSummaryInfo = array();
            if ($this->showField('name')) {
                $accountSummaryInfo['erpName'] = $this->getErpName();
            }
            if ($this->showField('code')) {
                $accountSummaryInfo['erpShortCode'] = $this->getErpShortCode();
            }
            if ($this->showField('credit_limit')) {
                $accountSummaryInfo['erpCreditLimit'] = $this->getCreditLimit();
            }
            if ($this->showField('balance')) {
                $accountSummaryInfo['erpBalance'] = $this->getBalance();
            }
            return $accountSummaryInfo;
        } else {
            $accountSummaryInfo = array();
            return $accountSummaryInfo;
        }
    }

    /**
     * Is current Customer an ERP Customer
     *
     * @return bool
     */
    public function isErpCustomer() {
        $customer = $this->customerSession->getCustomer();
        return ($customer->getEccErpaccountId() != 0 || $customer->isSalesRep());
    }

    /**
     * Returns whether to show a field on the page or not
     *
     * @param string $field - field name to check
     *
     * @return boolean
     */
    public function showField($field) {
        // customer check
        // erp account check
        // global check
        if ($this->_scopeConfig->isSetFlag('customerconnect_enabled_messages/customer_account_summary/show_' . $field, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return true;
        }
        return false;
    }

    /**
     * Gets the ERP Account for the current customer
     *
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function getErpCustomer() {
        if (!$this->_erp_customer) {
            $commHelper = $this->commHelper;
            $this->_erp_customer = $commHelper->getErpAccountInfo();
        }
        return $this->_erp_customer;
    }

    /**
     * Gets the ERP Name
     *
     * @return string
     */
    public function getErpName() {
        return $this->getErpCustomer()->getName();
    }

    /**
     * Gets the ERP Short Code
     *
     * @return string
     */
    public function getErpShortCode() {
        return $this->getErpCustomer()->getShortCode();
    }

    /**
     * Returns the erp account credit limit in the store base currency code
     *
     * @return string
     */
    public function getCreditLimit() {
        $store_currency = $this->_storeManager->getStore()->getBaseCurrencyCode();
        $credit_limit = $this->getErpCustomer()->getCreditLimit($store_currency);
        if (is_null($credit_limit)) {
            $displayed_credit = __("No Limit");
        } else {
            $displayed_credit = $this->getLocalisedAmount($credit_limit);
        }
        return $displayed_credit;
    }

    /**
     * Localises an amount to the current store currency
     *
     * @param float $amount - amount to convert
     *
     * @return string - amount localised to store currency
     */
    public function getLocalisedAmount($amount) {
        $store = $this->_storeManager->getStore();
        if (empty($amount) && $amount !== 0) {
            $output = __('N/A');
        } else {
            $helper = $this->customerconnectHelper;
            $output = $helper->getCurrencyConvertedAmount(
                    $amount, $store->getBaseCurrencyCode(), $store->getCurrentCurrencyCode()
            );
        }
        return $output;
    }

    /**
     * Returns the erp account balance in the store base currency code
     *
     * @return string
     */
    public function getBalance() {
        $store = $this->_storeManager->getStore();
        return $this->getLocalisedAmount($this->getErpCustomer()->getBalance($store->getBaseCurrencyCode()));
    }

}