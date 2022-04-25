<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Telemetry\Model;

/**
 * Class Envelope
 * @category    Epicor
 * @package     Epicor\Telemetry\Model
 * @author      Epicor Websales Team
 */
class Envelope extends AbstractContext
{
    /**
     * Data array that will store all the values.
     * @var array
     */
    protected $data;

    /**
     * Envelope constructor. Creates a new Envelope.
     */
    public function __construct()
    {
        $this->data['ver'] = 1;
        $this->data['name'] = null;
        $this->data['time'] = null;
        $this->data['sampleRate'] = 100.0;
    }

    /**
     * Gets the ver field. Envelope version.
     * For internal use only.
     * By assigning this the default, it will not be serialized within the payload unless changed to a value other than #1.
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
     * Sets the ver field. Envelope version.
     * For internal use only.
     * By assigning this the default, it will not be serialized within the payload unless changed to a value other than #1.
     * @param int $ver
     */
    public function setVer($ver)
    {
        $this->data['ver'] = $ver;
    }

    /**
     * Gets the name field. Type name of telemetry data item.
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
     * Sets the name field. Type name of telemetry data item.
     * @param string $name
     */
    public function setName($name)
    {
        $this->data['name'] = $name;
    }

    /**
     * Gets the time field. Event date time when telemetry item was created.
     * This is the wall clock time on the client when the event was generated.
     * There is no guarantee that the client's time is accurate.
     * This field must be formatted in UTC ISO 8601 format, with a trailing 'Z' character, as described publicly on https://en.wikipedia.org/wiki/ISO_8601#UTC.
     * Note: the number of decimal seconds digits provided are variable (and unspecified).
     * Consumers should handle this, i.e. managed code consumers should not use format 'O' for parsing as it specifies a fixed length. Example: 2009-06-15T13:45:30.0000000Z.
     * @return mixed|void
     */
    public function getTime()
    {
        if (array_key_exists('time', $this->data)) {
            return $this->data['time'];
        }
        return;
    }

    /**
     * Sets the time field.
     * Event date time when telemetry item was created.
     * This is the wall clock time on the client when the event was generated.
     * There is no guarantee that the client's time is accurate.
     * This field must be formatted in UTC ISO 8601 format, with a trailing 'Z' character, as described publicly on https://en.wikipedia.org/wiki/ISO_8601#UTC.
     * Note: the number of decimal seconds digits provided are variable (and unspecified). Consumers should handle this, i.e. managed code consumers should not use format 'O' for parsing as it specifies a fixed length. Example: 2009-06-15T13:45:30.0000000Z.
     * @param mixed $time
     */
    public function setTime($time)
    {
        $this->data['time'] = $time;
    }

    /**
     * Gets the sampleRate field. Sampling rate used in application.
     * This telemetry item represents 1 / sampleRate actual telemetry items.
     * @return mixed|void
     */
    public function getSampleRate()
    {
        if (array_key_exists('sampleRate', $this->data)) {
            return $this->data['sampleRate'];
        }
        return;
    }

    /**
     * Sets the sampleRate field.
     * Sampling rate used in application.
     * This telemetry item represents 1 / sampleRate actual telemetry items.
     * @param mixed $sampleRate
     */
    public function setSampleRate($sampleRate)
    {
        $this->data['sampleRate'] = $sampleRate;
    }

    /**
     * Gets the seq field.
     * Sequence field used to track absolute order of uploaded events.
     * @return mixed|void
     */
    public function getSeq()
    {
        if (array_key_exists('seq', $this->data)) {
            return $this->data['seq'];
        }
        return;
    }

    /**
     * Sets the seq field.
     * Sequence field used to track absolute order of uploaded events.
     * @param mixed $seq
     */
    public function setSeq($seq)
    {
        $this->data['seq'] = $seq;
    }

    /**
     * Gets the iKey field.
     * The application's instrumentation key.
     * The key is typically represented as a GUID, but there are cases when it is not a guid.
     * No code should rely on iKey being a GUID. Instrumentation key is case insensitive.
     * @return mixed|void
     */
    public function getInstrumentationKey()
    {
        if (array_key_exists('iKey', $this->data)) {
            return $this->data['iKey'];
        }
        return;
    }

    /**
     * Sets the iKey field.
     * The application's instrumentation key.
     * The key is typically represented as a GUID, but there are cases when it is not a guid.
     * No code should rely on iKey being a GUID. Instrumentation key is case insensitive.
     * @param mixed $iKey
     */
    public function setInstrumentationKey($iKey)
    {
        $this->data['iKey'] = $iKey;
    }

    /**
     * Gets the tags field. Key/value collection of context properties.
     * See ContextTagKeys for information on available properties.
     * @return mixed|void
     */
    public function getTags()
    {
        if (array_key_exists('tags', $this->data)) {
            return $this->data['tags'];
        }
        return;
    }

    /**
     * Sets the tags field.
     * Key/value collection of context properties.
     * See ContextTagKeys for information on available properties.
     * @param mixed $tags
     */
     public function setTags($tags)
     {
        $this->data['tags'] = $tags;
     }


    /**
     * Gets the data field. Telemetry data item.
     * @return mixed|void
     */
    public function getData()
    {
        if (array_key_exists('data', $this->data)) {
            return $this->data['data'];
        }
        return;
    }

    /**
     * Sets the data field. Telemetry data item.
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data['data'] = $data;
    }
}
