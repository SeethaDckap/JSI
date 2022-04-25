<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Telemetry\Model\Data;

use Epicor\Telemetry\Model\BaseData;

/**
 * Class EventData
 * @category    Epicor
 * @package     Epicor\Telemetry\Model
 * @author      Epicor Websales Team
 */
class EventData extends BaseData
{
    /**
     * EventData constructor. Creates a new EventData.
     */
    public function __construct()
    {
        $this->envelopeTypeName = 'Microsoft.ApplicationInsights.Event';
        $this->dataTypeName = 'EventData';
        $this->data['ver'] = 2;
        $this->data['name'] = null;
    }

    /**
     * Gets the ver field. Schema version
     * @return mixed|void
     */
    public function getVer()
    {
        if (array_key_exists('ver', $this->data)) {
            return $this->data['ver'];
        }
        return;
    }

    /**
     * Sets the ver field. Schema version
     * @param mixed $ver
     */
    public function setVer($ver)
    {
        $this->data['ver'] = $ver;
    }

    /**
     * Gets the name field. Event name. Keep it low cardinality to allow proper grouping and useful metrics.
     * @return mixed|void
     */
    public function getName()
    {
        if (array_key_exists('name', $this->data)) {
            return $this->data['name'];
        }
        return;
    }

    /**
     * Sets the name field. Event name. Keep it low cardinality to allow proper grouping and useful metrics.
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->data['name'] = $name;
    }

    /**
     * Gets the properties field. Collection of custom properties.
     * @return mixed|void
     */
    public function getProperties()
    {
        if (array_key_exists('properties', $this->data)) {
            return $this->data['properties'];
        }
        return;
    }

    /**
     * Sets the properties field. Collection of custom properties.
     * @param mixed $properties
     */
    public function setProperties($properties)
    {
        $this->data['properties'] = $properties;
    }

    /**
     * Gets the measurements field. Collection of custom measurements.
     * @return mixed|void
     */
    public function getMeasurements()
    {
        if (array_key_exists('measurements', $this->data)) {
            return $this->data['measurements'];
        }
        return;
    }

    /**
     * Sets the measurements field. Collection of custom measurements.
     * @param mixed $measurements
     */
    public function setMeasurements($measurements)
    {
        $this->data['measurements'] = $measurements;
    }

}