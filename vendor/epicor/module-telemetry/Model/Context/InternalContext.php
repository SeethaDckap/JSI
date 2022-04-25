<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Telemetry\Model\Context;

use Epicor\Telemetry\Model\AbstractContext;

/**
 * Class InternalContext
 * @category    Epicor
 * @package     Epicor\Telemetry\Model
 * @author      Epicor Websales Team
 */
class InternalContext extends AbstractContext
{
    /**
     * Data array that will store all the values.
     * @var array
     */
    protected $data;

    /**
     * InternalContext constructor. Creates a new Internal.
     */
    public function __construct()
    {
        $this->data = [];
    }

    /**
     * Gets the sdkVersion field.
     * SDK version.
     * See https://github.com/Microsoft/ApplicationInsights-Home/blob/master/SDK-AUTHORING.md#sdk-version-specification for information.
     * @return mixed|null
     */
    public function getSdkVersion()
    {
        if (array_key_exists('ai.internal.sdkVersion', $this->data)) {
            return $this->data['ai.internal.sdkVersion'];
        }
        return;
    }

    /**
     * Sets the sdkVersion field.
     * SDK version.
     * See https://github.com/Microsoft/ApplicationInsights-Home/blob/master/SDK-AUTHORING.md#sdk-version-specification for information.
     * @param mixed $sdkVersion
     */
    public function setSdkVersion($sdkVersion)
    {
        $this->data['ai.internal.sdkVersion'] = $sdkVersion;
    }

    /**
     * Gets the agentVersion field.
     * Agent version.
     * Used to indicate the version of StatusMonitor installed on the computer if it is used for data collection.
     * @return mixed|null
     */
    public function getAgentVersion()
    {
        if (array_key_exists('ai.internal.agentVersion', $this->data)) {
            return $this->data['ai.internal.agentVersion'];
        }
        return;
    }

    /**
     * Sets the agentVersion field.
     * Agent version.
     * Used to indicate the version of StatusMonitor installed on the computer if it is used for data collection.
     * @param mixed $agentVersion
     */
    public function setAgentVersion($agentVersion)
    {
        $this->data['ai.internal.agentVersion'] = $agentVersion;
    }

    /**
     * Gets the nodeName field.
     * This is the node name used for billing purposes.
     * Use it to override the standard detection of nodes.
     * @return mixed|void
     */
    public function getNodeName()
    {
        if (array_key_exists('ai.internal.nodeName', $this->data)) {
            return $this->data['ai.internal.nodeName'];
        }
        return;
    }

    /**
     * Sets the nodeName field.
     * This is the node name used for billing purposes.
     * Use it to override the standard detection of nodes.
     * @param mixed $nodeName
     */
    public function setNodeName($nodeName)
    {
        $this->data['ai.internal.nodeName'] = $nodeName;
    }
}