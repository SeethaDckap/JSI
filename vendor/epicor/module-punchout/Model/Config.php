<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Model
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Model;

use Magento\Framework\Math\Random;
use Epicor\Punchout\Model\Connections as Connection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Epicor\Punchout\Helper\Data;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Config
 *
 * @package Epicor\Punchout\Model
 */
class Config
{

    /**
     * #@+
     * Lengths of secret key
     */
    const LENGTH_CONNECTION_SECRET = 32;

    /**
     * ScopeConfigInterface
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Math random.
     *
     * @var mixed
     */
    private $mathRandom;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timezone;


    /**
     * Construction function.
     *
     * @param ScopeConfigInterface                                 $scopeConfig Scope Config.
     * @param Data                                                 $helper      Helper Data.
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Data $helper,
        TimezoneInterface $timezone
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->mathRandom  = $helper->getMathRandom();
        $this->timezone    = $timezone;

    }//end __construct()


    /**
     * Returns API Key used for authentication.
     *
     * A shared secret value between the Procurment system and ECC.
     * This value should never be exposed to the public.
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getApiKey(?int $storeId = null): string
    {
         return !empty($this->scopeConfig->getValue(
             'epicor_punchout/setup_request/api_key',
             ScopeInterface::SCOPE_STORE,
             $storeId
            )) ? $this->scopeConfig->getValue(
                'epicor_punchout/setup_request/api_key',
                ScopeInterface::SCOPE_STORE,
                $storeId
            ) : '';

    }//end getApiKey()


    /**
     * Check punch out feature is enable
     *
     * @return boolean
     */
    public function isPunchoutEnable()
    {
        return $this->scopeConfig->getValue('epicor_punchout/general/enabled', ScopeInterface::SCOPE_STORE);

    }//end isPunchoutEnable()


    /**
     * Generate secret key
     *
     * @return string
     */
    public function generateSecretKey()
    {
        return  $this->mathRandom->getRandomString(
            self::LENGTH_CONNECTION_SECRET,
            Random::CHARS_DIGITS.Random::CHARS_LOWERS
        );

    }//end generateSecretKey()

    /**
     * @param      $timestamp
     * @param int  $format
     * @param bool $showTime
     *
     * @return false|string
     */
    public function getLocalDate($timestamp, $format = \IntlDateFormatter::MEDIUM, $showTime = false)
    {
        if (is_numeric($timestamp)) {
            return date('c', $timestamp);
        }
        return $this->timezone->formatDate($timestamp, $format, $showTime);
    }
}//end class
