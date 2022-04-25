<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elements\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Service class for accessing configurations.
 *
 * @package Epicor\Elements\Service
 */
class ElementsConfiguration
{
    /**
     * Constants for configuration paths.
     */
    const GATEWAY_URL        = 'payment/elements/gateway_url';
    const GATEWAY_URL_MOBILE = 'payment/elements/gateway_url_mobile';

    /**
     * Scope configuration.
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;


    /**
     * ElementsConfiguration constructor.
     *
     * @param ScopeConfigInterface $scopeConfig Scope configuration.
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;

    }//end __construct()


    /**
     * Get the relevant gateway URL for mobile / desktop.
     *
     * @param integer $isMobile Mobile or desktop.
     *
     * @return string
     */
    public function getGatewayUrl(int $isMobile = 0)
    {
        if ($isMobile === 0) {
            return $this->scopeConfig->getValue(self::GATEWAY_URL);
        }

        return $this->scopeConfig->getValue(self::GATEWAY_URL_MOBILE);

    }//end getGatewayUrl()


    /**
     * Get the transaction URL.
     *
     * @param array   $variables Variables to populate in the URL.
     * @param integer $isMobile  Is mobile or desktop.
     *
     * @return string
     */
    public function getTransactionUrl(array $variables, int $isMobile = 0)
    {
        $url = $this->getGatewayUrl($isMobile);
        foreach ($variables as $variable => $value) {
            $url = str_replace($variable, $value, $url);
        }

        return $url;

    }//end getTransactionUrl()


}
