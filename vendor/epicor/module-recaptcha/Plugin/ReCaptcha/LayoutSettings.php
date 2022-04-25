<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Recaptcha\Plugin\ReCaptcha;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use MSP\ReCaptcha\Model\Config;

class LayoutSettings
{

    const XML_PATH_ENABLED_B2B_CREATE = 'msp_securitysuite_recaptcha/frontend/enabled_b2b_create';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Config
     */
    private $config;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Config $config
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
    }

    /**
     * Return captcha config for frontend
     * @return array
     */
    public function afterGetCaptchaSettings(
        \MSP\ReCaptcha\Model\LayoutSettings $subject,
        $result
    )
    {
        if (isset($result['enabled'])) {
            $result['enabled']['b2b_create'] = $this->isEnabledB2bCreate();
        }
        return $result;
    }

    /**
     * Return true if enabled on B2B create user
     * @return bool
     */
    public function isEnabledB2bCreate()
    {
        if (!$this->config->isEnabledFrontend()) {
            return false;
        }

        return (bool) $this->scopeConfig->getValue(
            static::XML_PATH_ENABLED_B2B_CREATE,
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}