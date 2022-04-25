<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Telemetry\Model\Context;

use Epicor\Telemetry\Model\AbstractContext;

/**
 * Class ApplicationContext
 * @category    Epicor
 * @package     Epicor\Telemetry\Model
 * @author      Epicor Websales Team
 */
class ApplicationContext extends AbstractContext
{
    /**
     * Data array that will store all the values.
     * @var array
     */
    protected $data;

    /**
     * ApplicationContext constructor. Creates a new Application.
     */
    public function __construct()
    {
        $this->data = [];
    }

    /**
     * Gets the ver field.
     * Application version.
     * Information in the application context fields is always about the application that is sending the telemetry.
     * @return mixed|void
     */
    public function getVer()
    {
        if (array_key_exists('ai.application.ver', $this->data)) {
            return $this->data['ai.application.ver'];
        }
        return;
    }

    /**
     * Sets the ver field. Application version.
     * Information in the application context fields is always about the application that is sending the telemetry.
     * @param mixed $ver
     */
    public function setVer($ver)
    {
        $this->data['ai.application.ver'] = $ver;
    }
}
