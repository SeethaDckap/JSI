<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Telemetry\Model;

/**
 * Data contract class for type Data. Data struct to contain both B and C sections.
 * @category    Epicor
 * @package     Epicor\Telemetry\Model
 * @author      Epicor Websales Team
 */
class Data extends AbstractContext
{
    /**
     * Data array that will store all the values.
     * @var mixed
     */
    protected $data;

    /**
     * Data constructor. Creates a new Data.
     */
    public function __construct()
    {
        $this->data['baseData'] = null;
    }

    /**
     * Gets the baseType field. Name of item (B section) if any.
     * If telemetry data is derived straight from this, this should be null.
     * @return mixed|void
     */
    public function getBaseType()
    {
        if (array_key_exists('baseType', $this->data)) {
            return $this->data['baseType'];
        }
        return;
    }

    /**
     * Sets the baseType field. Name of item (B section) if any.
     * If telemetry data is derived straight from this, this should be null.
     * @param mixed $baseType
     */
    public function setBaseType($baseType)
    {
        $this->data['baseType'] = $baseType;
    }

    /**
     * Gets the baseData field. Container for data item (B section).
     * @return mixed|void
     */
    public function getBaseData()
    {
        if (array_key_exists('baseData', $this->data)) {
            return $this->data['baseData'];
        }
        return;
    }

    /**
     * Sets the baseData field. Container for data item (B section).
     * @param mixed $baseData
     */
    public function setBaseData($baseData)
    {
        $this->data['baseData'] = $baseData;
    }
}
