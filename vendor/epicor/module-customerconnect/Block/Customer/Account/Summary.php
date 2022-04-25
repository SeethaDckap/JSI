<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Account;
use Magento\Framework\View\Element\Template;


/**
 * Customer ERP Account Summary Block
 *
 * @author gareth.james
 */
class Summary extends \Magento\Customer\Block\Account\Customer
{

    private $_erp_customer;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;


    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;


    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Registry $registry,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->commHelper = $commHelper;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $context->getScopeConfig();
        $this->_accessauthorization = $context->getAccessAuthorization();
        parent::__construct(
            $context,
            $httpContext,
            $data
        );
        // needed in customerconnect dashboard
        if ($this->registry->registry('customerconnect_dashboard_ok')) {
            $this->setDisplayDashboard(true);
        }
        if ($this->isErpCustomer()) {
            // this section enables the default currency amount to be displayed in the account summary in brackets
            $this->setBaseCurrencyCode($this->_storeManager->getStore()->getBaseCurrencyCode());
            $this->setCurrentCurrencyCode($this->_storeManager->getStore()->getCurrentCurrencyCode());
            if ($this->getBaseCurrencyCode() != $this->getCurrentCurrencyCode()) {
                $this->setConversionRequired(true);
                $baseCurrencyCode = $this->getBaseCurrencyCode();
                $currentCurrencyCode = $this->getCurrentCurrencyCode();

                if ($this->getCreditLimit() == __("No Limit"))
                    $this->setConvertedCreditLimit(__("No Limit"));
                else {
                    $creditLimitNoCurrency = $this->customerconnectHelper->removeCurrencyCodePrefix($this->getCreditLimit());
                    $this->setConvertedCreditLimit($this->customerconnectHelper->getCurrencyConvertedAmount($creditLimitNoCurrency,$this->getBaseCurrencyCode(), $this->getCurrentCurrencyCode()));
                }
                $balanceNoCurrency = $this->customerconnectHelper->removeCurrencyCodePrefix($this->getBalance());
                if ($balanceNoCurrency != (0.00 && 0)) {
                    $this->setConvertedBalance($this->customerconnectHelper->getCurrencyConvertedAmount($balanceNoCurrency, $this->getBaseCurrencyCode(),$this->getCurrentCurrencyCode()));
                }
            }
        }
    }


    public function toHtml()
    {
        if (!$this->_accessauthorization->isAllowed(
            'Epicor_Customerconnect::customerconnect_account_information_information_read'
        )) {
            return '';
        }
        return parent::toHtml();
    }
    /**
     * Gets the ERP Account for the current customer
     *
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function getErpCustomer()
    {
        if (!$this->_erp_customer) {
            $commHelper = $this->commHelper;
            $this->_erp_customer = $commHelper->getErpAccountInfo();
        }
        return $this->_erp_customer;
    }

    /**
     * Is current Customer an ERP Customer
     *
     * @return bool
     */
    public function isErpCustomer()
    {
        $customer = $this->customerSession->getCustomer();
        return (!is_null($customer->getEccErpaccountId()) || $customer->isSalesRep());
    }

    /**
     * Returns the erp account credit limit in the store base currency code
     *
     * @return string
     */
    public function getCreditLimit()
    {
        $store_currency = $this->_storeManager->getStore()->getBaseCurrencyCode();
        $credit_limit = $this->getErpCustomer()->getCreditLimit($store_currency);
        if (is_null($credit_limit)) {
            $displayed_credit = __("No Limit");
        } else {
            $displayed_credit = $this->getLocalisedAmount($credit_limit);
        }
        return $displayed_credit;
    }

    public function getMinOrderValue()
    {

        $store_currency = $this->_storeManager->getStore()->getBaseCurrencyCode();
        $min_order_value = $this->getErpCustomer()->getCurrencyData($store_currency)->getMinOrderAmount();
        if (is_null($min_order_value)) {
            $displayed_mov = __("No Limit");
        } else {
            $displayed_mov = $this->getLocalisedAmount($min_order_value);
        }
        return $displayed_mov;
    }

    /**
     * Returns the erp account balance in the store base currency code
     *
     * @return string
     */
    public function getBalance()
    {
        $store = $this->_storeManager->getStore();
        return $this->getLocalisedAmount($this->getErpCustomer()->getBalance($store->getBaseCurrencyCode()));
    }

    public function getUnallocatedCash()
    {
        $store = $this->_storeManager->getStore();
        $currencyCode = $store->getCurrentCurrencyCode();

        return $this->getLocalisedAmount($this->getErpCustomer()->getUnallocatedCash($currencyCode));
    }

    /**
     * Localises an amount to the current store currency
     *
     * @param float $amount - amount to convert
     *
     * @return string - amount localised to store currency
     */
    public function getLocalisedAmount($amount)
    {

        $store = $this->_storeManager->getStore();
        if (empty($amount) && $amount !== 0)
            $output = __('N/A');
        else {
            $helper = $this->customerconnectHelper;

            $output = $helper->getCurrencyConvertedAmount(
                $amount, $store->getBaseCurrencyCode(), $store->getCurrentCurrencyCode()
            );
        }
        return $output;
    }

    /**
     * Returns whether to show a field on the page or not
     *
     * @param string $field - field name to check
     *
     * @return boolean
     */
    public function showField($field)
    {
        // customer check
        // erp account check
        // global check
        if ($this->_scopeConfig->isSetFlag('customerconnect_enabled_messages/customer_account_summary/show_' . $field, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return true;
        }

        return false;
    }

    public function isHidePricesActive()
    {
        return (bool)$this->commHelper->getEccHidePrice() && in_array($this->commHelper->getEccHidePrice(), [1, 2, 3]);
    }

}
