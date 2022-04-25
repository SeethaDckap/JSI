<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Session\SessionStartChecker;

/**
 * Intended to preserve session cookie after submitting POST form to EWA.
 */
class ConfiguratorSessionChecker
{
    private const CONFIGURATOR_CONTROLLER_EWACSS = 'comm/configurator/ewacss';

    private const CONFIGURATOR_CONTROLLER_EWACOMPLETE = 'comm/configurator/ewacomplete';

    private const CONFIGURATOR_CONTROLLER_RFQEWACOMPLETE = 'comm/configurator/rfqewacomplete';

    /**
     * @var Http
     */
    private $request;

    /**
     * @param Http $request
     */
    public function __construct(
        Http $request
    ) {
        $this->request = $request;
    }

    /**
     * Prevents session starting while instantiating Configurator.
     *
     * @param SessionStartChecker $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCheck(SessionStartChecker $subject, bool $result): bool
    {
        $requestUri = $this->request->getRequestUri();
        if (strpos($requestUri, self::CONFIGURATOR_CONTROLLER_EWACSS) !== false
            || strpos($requestUri, self::CONFIGURATOR_CONTROLLER_EWACOMPLETE) !== false
            || strpos($requestUri, self::CONFIGURATOR_CONTROLLER_RFQEWACOMPLETE) !== false
        ) {
            return false;
        }
        return $result;
    }
}
