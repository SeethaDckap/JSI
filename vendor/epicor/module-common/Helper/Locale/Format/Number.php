<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Helper\Locale\Format;


class Number extends \Epicor\Common\Helper\Locale\Format
{

    public function toNumber($value, $precision, $localeCode = false, $numberFormat = false)
    {
        if (!$localeCode) {
            $localeCode = $this->getLocale()->getLocaleCode();
        }
        $options = array(
            'locale' => $localeCode,
            'precision' => $precision
        );
        if ($numberFormat) {
            $options['number_format'] = $numberFormat;
        }
        $number = \Zend_Locale_Format::toNumber($value, $options);
        return $number;
    }

    public function getSymbols($localeCode = false)
    {
        if (!$localeCode) {
            $localeCode = $this->getLocale()->getLocaleCode();
        }

        if (!isset($this->_cache['numbers']['symbols'][$localeCode])) {
            $this->_cache['numbers']['symbols'][$localeCode] = \Zend_Locale_Data::getList($localeCode, 'symbols');
        }

        return $this->_cache['numbers']['symbols'][$localeCode];
    }

    public function getThousandsSeparator($localeCode = false)
    {
        $symbols = $this->getSymbols($localeCode);
        return $symbols['group'];
    }

    public function getDecimalSeparator($localeCode = false)
    {
        $symbols = $this->getSymbols($localeCode);
        return $symbols['decimal'];
    }

    public function getPercentSymbol($localeCode = false)
    {
        $symbols = $this->getSymbols($localeCode);
        return $symbols['percentSign'];
    }

}
