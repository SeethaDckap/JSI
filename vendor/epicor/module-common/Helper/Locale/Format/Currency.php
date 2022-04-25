<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Helper\Locale\Format;


class Currency extends \Epicor\Common\Helper\Locale\Format\Number
{

    /**
     * @var \Magento\Config\Model\Config\Source\Locale\Currency
     */
    protected $currency;

    public function __construct(
        \Epicor\Common\Helper\Context $context,
        \Magento\Config\Model\Config\Source\Locale\Currency $currency
    ) {
        $this->currency = $currency;
        parent::__construct($context);
    }

    public function getCurrencySymbol($currencyCode = false)
    {
        if (!$currencyCode) {
            $currencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
        }

        if (!isset($this->_cache['currency']['symbol'][$currencyCode])) {
            $this->_cache['currency']['symbol'][$currencyCode] = $this->getLocale()->currency($currencyCode)->getSymbol();
        }

        return $this->_cache['currency']['symbol'][$currencyCode];
    }

    public function getPrecision()
    {
        if (!isset($this->_cache['currency']['precision'])) {
            $this->_cache['currency']['precision'] = $this->scopeConfig->getValue('checkout/options/unit_price_precision', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        return $this->_cache['currency']['precision'];
    }

    public function getCurrencySymbolPosition($localeCode = false)
    {
        if (!$localeCode) {
            $localeCode = $this->getLocale()->getLocaleCode();
        }

        if (!isset($this->_cache['currency']['symbol_position'][$localeCode])) {
            $format = $this->getPositiveFormat($localeCode);
            if (iconv_strpos($format, '¤') < 3) {
                $position = 'left';
            } else {
                $position = 'right';
            }
            $this->_cache['currency']['symbol_position'][$localeCode] = $position;
        }

        return $this->_cache['currency']['symbol_position'][$localeCode];
    }

    public function getFormat($localeCode)
    {
        if (!$localeCode) {
            $localeCode = $this->getLocale()->getLocaleCode();
        }

        if (!isset($this->_cache['currency']['format'][$localeCode])) {
            $this->_cache['currency']['format'][$localeCode] = \Zend_Locale_Data::getContent($localeCode, 'currencynumber');
        }

        return $this->_cache['currency']['format'][$localeCode];
    }

    public function getPositiveFormat($localeCode = false)
    {
        if (!$localeCode) {
            $localeCode = $this->getLocale()->getLocaleCode();
        }
        if (!isset($this->_cache['currency']['positive_format'][$localeCode])) {
            $format = $this->getFormat($localeCode);
            if (iconv_strpos($format, ';') !== false) {
                $format = iconv_substr($format, 0, iconv_strpos($format, ';'));
            }
            $this->_cache['currency']['positive_format'][$localeCode] = $format;
        }

        return $this->_cache['currency']['positive_format'][$localeCode];
    }

    public function getNegativeFormat($localeCode = false)
    {
        if (!$localeCode) {
            $localeCode = $this->getLocale()->getLocaleCode();
        }

        if (!isset($this->_cache['currency']['negative_format'][$localeCode])) {
            $format = $this->getFormat($localeCode);
            if (iconv_strpos($format, ';') !== false) {
                $tmpformat = iconv_substr($format, iconv_strpos($format, ';') + 1);
                if ($tmpformat[0] == '(') {
                    $format = iconv_substr($format, 0, iconv_strpos($format, ';'));
                } else {
                    $format = $tmpformat;
                }
                $format = iconv_substr($format, 0, iconv_strpos($format, ';'));
            }
            $this->_cache['currency']['negative_format'][$localeCode] = $format;
        }

        return $this->_cache['currency']['negative_format'][$localeCode];
    }

    public function hasSpace($localeCode = false)
    {

        if (!isset($this->_cache['currency']['has_space'][$localeCode])) {
            $format = $this->getPositiveFormat($localeCode);
            $this->_cache['currency']['has_space'][$localeCode] = iconv_strpos($format, ' ') ? true : false;
        }

        return $this->_cache['currency']['has_space'][$localeCode];
    }

    /**
     * Returns al Allowed Currencies
     *
     * @return array
     */
    public function getAllowedCurrencies()
    {
        $allowedCurrencies = array();

        $stores = $this->storeManager->getStores();
        foreach ($stores as $store) {
            $storeCurrency = $this->scopeConfig->getValue('currency/options/allow', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
            $storeCurrencies = explode(',', $storeCurrency);
            foreach ($storeCurrencies as $currency) {
                $allowedCurrencies[$currency] = $currency;
            }
        }

        //M1 > M2 Translation Begin (Rule p2-1)
        //$currencies = Mage::getModel('adminhtml/system_config_source_currency')->toOptionArray(false);
        $currencies = $this->currency->toOptionArray(false);
        //M1 > M2 Translation End
        foreach ($currencies as $currency) {
            if (in_array($currency['value'], $allowedCurrencies)) {
                $allowedCurrencies[$currency['value']] = $currency['label'];
            }
        }

        return $allowedCurrencies;
    }

}
