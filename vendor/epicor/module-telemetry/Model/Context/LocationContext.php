<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Telemetry\Model\Context;

use Epicor\Telemetry\Model\AbstractContext;

/**
 * Class LocationContext
 * @category    Epicor
 * @package     Epicor\Telemetry\Model
 * @author      Epicor Websales Team
 */
class LocationContext extends AbstractContext
{
    /**
     * Data array that will store all the values.
     * @var array
     */
    protected $data;

    /**
     * LocationContext constructor. Creates a new Location.
     */
    public function __construct()
    {
        $this->data = [];
    }

    /**
     * Gets the ip field.
     * The IP address of the client device.
     * IPv4 and IPv6 are supported. Information in the location context fields is always about the end user.
     * When telemetry is sent from a service, the location context is about the user that initiated the operation in the service.
     * @return mixed|void
     */
    public function getIp()
    {
        if (array_key_exists('ai.location.ip', $this->data)) {
            return $this->data['ai.location.ip'];
        }
        return;
    }

    /**
     * Sets the ip field.
     * The IP address of the client device.
     * IPv4 and IPv6 are supported.
     * Information in the location context fields is always about the end user.
     * When telemetry is sent from a service, the location context is about the user that initiated the operation in the service.
     * @param mixed $ip
     */
    public function setIp($ip)
    {
        $this->data['ai.location.ip'] = $ip;
    }
}