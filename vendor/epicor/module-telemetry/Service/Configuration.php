<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Telemetry\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Configuration
{
    const IS_ENABLED = 'Epicor_Comm/telemetry/telemetry_enabled';

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * Configuration constructor.
     *
     * @param ScopeConfigInterface $config
     */
    public function __construct(ScopeConfigInterface $config)
    {
        $this->config = $config;
    }

    public function isEnabled()
    {
        return $this->config->isSetFlag(self::IS_ENABLED);
    }
}
