<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Helper;


class Locale extends \Epicor\Common\Helper\Data
{

    private $_locale;
    protected $_cache = array();

    /**
     * Retrieve application locale object
     *
     * @return Mage_Core_Model_Locale
     */
    public function getLocale()
    {
        if (!$this->_locale) {
            //M1 > M2 Translation Begin (Rule p2-5.1)
            //$this->_locale = Mage::getSingleton('core/locale');
            $this->_locale = $this->timezone;
            //M1 > M2 Translation End
        }
        return $this->_locale;
    }

}
