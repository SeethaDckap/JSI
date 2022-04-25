<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Telemetry\Model;


/**
 * Class BaseData
 * @category    Epicor
 * @package     Epicor\Telemetry\Model
 * @author      Epicor Websales Team
 */
abstract class BaseData extends AbstractContext
{
    /**
     * Override for the time of the event
     * @var mixed
     */
    protected $time;

    /**
     * Data array that will store all the values.
     * @var mixed
     */
    protected $data;

    /**
     * Needed to properly construct the JSON envelope.
     * @var mixed
     */
    protected $envelopeTypeName;

    /**
     * Needed to properly construct the JSON envelope.
     * @var mixed
     */
    protected $dataTypeName;

    /**
     * Gets the envelopeTypeName field.
     * @return mixed
     */
    public function getEnvelopeTypeName()
    {
        return $this->envelopeTypeName;
    }

    /**
     * Gets the dataTypeName field.
     * @return mixed
     */
    public function getDataTypeName()
    {
        return $this->dataTypeName;
    }

    /**
     * Gets the time of the event.
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Sets the time of the event.
     * @param mixed $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }
}