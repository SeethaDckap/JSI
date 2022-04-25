<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Telemetry\Model\Data;

use Epicor\Telemetry\Model\BaseData;

/**
 * Class PageViewData
 * @category    Epicor
 * @package     Epicor\Telemetry\Model
 * @author      Epicor Websales Team
 */
class PageViewData extends BaseData
{
    /**
     * PageViewData constructor. Creates a new PageViewData.
     */
    public function __construct()
    {
        $this->envelopeTypeName = 'Microsoft.ApplicationInsights.PageView';
        $this->dataTypeName = 'PageViewData';
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
     * Sets the ver field. Schema version.
     * @param mixed $ver
     */
    public function setVer($ver)
    {
        $this->data['ver'] = $ver;
    }

    /**
     * Gets the url field. Request URL with all query string parameters.
     * @return mixed|void
     */
    public function getUrl()
    {
        if (array_key_exists('url', $this->data)) {
            return $this->data['url'];
        }
        return;
    }

    /**
     * Sets the url field. Request URL with all query string parameters.
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->data['url'] = $url;
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
     * Gets the duration field. Request duration in format: DD.HH:MM:SS.MMMMMM.
     * For a page view (PageViewData), this is the duration.
     * For a page view with performance information (PageViewPerfData), this is the page load time.
     * Must be less than 1000 days.
     * @return mixed|void
     */
    public function getDuration()
    {
        if (array_key_exists('duration', $this->data)) {
            return $this->data['duration'];
        }
        return;
    }

    /**
     * Sets the duration field. Request duration in format: DD.HH:MM:SS.MMMMMM.
     * For a page view (PageViewData), this is the duration.
     * For a page view with performance information (PageViewPerfData), this is the page load time.
     * Must be less than 1000 days.
     * @param mixed $duration
     */
    public function setDuration($duration)
    {
        $this->data['duration'] = $duration;
    }

    /**
     * Gets the id field. Identifier of a page view instance.
     * Used for correlation between page view and other telemetry items.
     * @return mixed|void
     */
    public function getId()
    {
        if (array_key_exists('id', $this->data)) {
            return $this->data['id'];
        }
        return;
    }

    /**
     * Sets the id field. Identifier of a page view instance.
     * Used for correlation between page view and other telemetry items.
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->data['id'] = $id;
    }

    /**
     * Gets the referrerUri field. Fully qualified page URI or URL of the referring page; if unknown, leave blank.
     * @return mixed|void
     */
    public function getReferrerUri()
    {
        if (array_key_exists('referrerUri', $this->data)) {
            return $this->data['referrerUri'];
        }
        return;
    }

    /**
     * Sets the referrerUri field. Fully qualified page URI or URL of the referring page; if unknown, leave blank.
     * @param mixed $referrerUri
     */
    public function setReferrerUri($referrerUri)
    {
        $this->data['referrerUri'] = $referrerUri;
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
