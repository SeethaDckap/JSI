<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Telemetry\Model\Context;

use Epicor\Telemetry\Model\AbstractContext;

/**
 * Class OperationContext
 * @category    Epicor
 * @package     Epicor\Telemetry\Model
 * @author      Epicor Websales Team
 */
class OperationContext extends AbstractContext
{
    /**
     * Data array that will store all the values.
     * @var array
     */
    protected $data;

    /**
     * OperationContext constructor.
     */
    public function __construct()
    {
        $this->data = [];
    }

    /**
     * Operation.id is used for finding all the telemetry items for a specific operation instance.
     *
     * @return mixed|void
     */
    public function getId()
    {
        if (array_key_exists('ai.operation.id', $this->data)) {
            return $this->data['ai.operation.id'];
        }
        return;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->data['ai.operation.id'] = $id;
    }

    /**
     * Operation.name is used for finding all the telemetry items for a group of operations (i.e. 'GET Home/Index').
     *
     * @return mixed|void
     */
    public function getName()
    {
        if (array_key_exists('ai.operation.name', $this->data)) {
            return $this->data['ai.operation.name'];
        }
        return;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->data['ai.operation.name'] = $name;
    }

    /**
     * Gets the parentId field. The unique identifier of the telemetry item's immediate parent.
     *
     * @return mixed|void
     */
    public function getParentId()
    {
        if (array_key_exists('ai.operation.parentId', $this->data)) {
            return $this->data['ai.operation.parentId'];
        }
        return;
    }


    /**
     * @param mixed $parentId
     */
    public function setParentId($parentId)
    {
        $this->data['ai.operation.parentId'] = $parentId;
    }

    /**
     * Gets the syntheticSource field. Name of synthetic source. Some telemetry from the application may represent a synthetic traffic.
     * It may be web crawler indexing the web site, site availability tests or traces from diagnostic libraries like Application Insights SDK itself.
     * @return mixed|void
     */
    public function getSyntheticSource()
    {
        if (array_key_exists('ai.operation.syntheticSource', $this->data)) {
            return $this->data['ai.operation.syntheticSource'];
        }
        return;
    }

    /**
     * @param mixed $syntheticSource
     */
    public function setSyntheticSource($syntheticSource)
    {
        $this->data['ai.operation.syntheticSource'] = $syntheticSource;
    }

    /**
     * Gets the correlationVector field.
     * The correlation vector is a light weight vector clock which can be used to identify and order related events across clients and services.
     *
     * @return mixed|void
     */
    public function getCorrelationVector()
    {
        if (array_key_exists('ai.operation.correlationVector', $this->data)) {
            return $this->data['ai.operation.correlationVector'];
        }
        return;
    }

    /**
     * @param mixed $correlationVector
     */
    public function setCorrelationVector($correlationVector)
    {
        $this->data['ai.operation.correlationVector'] = $correlationVector;
    }
}