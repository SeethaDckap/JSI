<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Telemetry\Model\Context;

use Epicor\Telemetry\Model\AbstractContext;

/**
 * Class DeviceContext
 * @category    Epicor
 * @package     Epicor\Telemetry\Model
 * @author      Epicor Websales Team
 */
class DeviceContext extends AbstractContext
{
    /**
     * Data array that will store all the values.
     * @var array
     */
    protected $data;

    /**
     * DeviceContext constructor. Creates a new Device.
     */
    public function __construct()
    {
        $this->data = [];
    }

    /**
     * Gets the id field. Unique client device id. Computer name in most cases.
     * @return mixed|void
     */
    public function getId()
    {
        if (array_key_exists('ai.device.id', $this->data)) {
            return $this->data['ai.device.id'];
        }
        return;
    }

    /**
     * Sets the id field. Unique client device id. Computer name in most cases.
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->data['ai.device.id'] = $id;
    }

    /**
     * Gets the locale field. Device locale using <language>-<REGION> pattern, following RFC 5646. Example 'en-US'.
     * @return mixed|void
     */
    public function getLocale()
    {
        if (array_key_exists('ai.device.locale', $this->data)) {
            return $this->data['ai.device.locale'];
        }
        return;
    }

    /**
     * Sets the locale field. Device locale using <language>-<REGION> pattern, following RFC 5646. Example 'en-US'.
     * @param mixed $locale
     */
    public function setLocale($locale)
    {
        $this->data['ai.device.locale'] = $locale;
    }

    /**
     * Gets the model field.
     * Model of the device the end user of the application is using.
     * Used for client scenarios. If this field is empty then it is derived from the user agent.
     * @return mixed|void
     */
    public function getModel()
    {
        if (array_key_exists('ai.device.model', $this->data)) {
            return $this->data['ai.device.model'];
        }
        return;
    }

    /**
     * Sets the model field.
     * Model of the device the end user of the application is using.
     * Used for client scenarios. If this field is empty then it is derived from the user agent.
     * @param mixed $model
     */
    public function setModel($model)
    {
        $this->data['ai.device.model'] = $model;
    }

    /**
     * Gets the oemName field. Client device OEM name taken from the browser.
     * @return mixed|void
     */
    public function getOemName()
    {
        if (array_key_exists('ai.device.oemName', $this->data)) {
            return $this->data['ai.device.oemName'];
        }
        return;
    }

    /**
     * Sets the oemName field. Client device OEM name taken from the browser.
     * @param mixed $oemName
     */
    public function setOemName($oemName)
    {
        $this->data['ai.device.oemName'] = $oemName;
    }

    /**
     * Gets the osVersion field.
     * Operating system name and version of the device the end user of the application is using.
     * If this field is empty then it is derived from the user agent. Example 'Windows 10 Pro 10.0.10586.0'
     * @return mixed|void
     */
    public function getOsVersion()
    {
        if (array_key_exists('ai.device.osVersion', $this->data)) {
            return $this->data['ai.device.osVersion'];
        }
        return;
    }

    /**
     * Sets the osVersion field.
     * Operating system name and version of the device the end user of the application is using.
     * If this field is empty then it is derived from the user agent. Example 'Windows 10 Pro 10.0.10586.0'
     * @param mixed $osVersion
     */
    public function setOsVersion($osVersion)
    {
        $this->data['ai.device.osVersion'] = $osVersion;
    }

    /**
     * Gets the type field.
     * The type of the device the end user of the application is using.
     * Used primarily to distinguish JavaScript telemetry from server side telemetry.
     * Examples: 'PC', 'Phone', 'Browser'. 'PC' is the default value.
     * @return mixed|void
     */
    public function getType()
    {
        if (array_key_exists('ai.device.type', $this->data)) {
            return $this->data['ai.device.type'];
        }
        return;
    }

    /**
     * Sets the type field.
     * The type of the device the end user of the application is using.
     * Used primarily to distinguish JavaScript telemetry from server side telemetry.
     * Examples: 'PC', 'Phone', 'Browser'. 'PC' is the default value.
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->data['ai.device.type'] = $type;
    }
}
