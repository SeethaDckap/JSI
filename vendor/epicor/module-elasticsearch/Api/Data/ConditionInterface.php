<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Api\Data;

/**
 * Interface ConditionInterface
 *
 * @package Epicor\Elasticsearch\Api\Data
 * @api
 */
interface ConditionInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const AGGREGATOR_TYPE_ALL = 'all';
    const AGGREGATOR_TYPE_ANY = 'any';

    /**
     * Get condition type
     *
     * @return string
     */
    public function getConditionType();

    /**
     * Set condition type
     *
     * @param string $conditionType Condition Type
     *
     * @return $this
     */
    public function setConditionType($conditionType);

    /**
     * Return list of conditions
     *
     * @return \Epicor\Elasticsearch\Api\Data\ConditionInterface[]|null
     */
    public function getConditions();

    /**
     * Set conditions
     *
     * @param \Epicor\Elasticsearch\Api\Data\ConditionInterface[]|null $conditions
     *
     * @return $this
     */
    public function setConditions(array $conditions = null);

    /**
     * Return the aggregator type
     *
     * @return string|null
     */
    public function getAggregatorType();

    /**
     * Set the aggregator type
     *
     * @param string $aggregatorType
     *
     * @return $this
     */
    public function setAggregatorType($aggregatorType);

    /**
     * Return the operator of the condition
     *
     * @return string
     */
    public function getOperator();

    /**
     * Set the operator of the condition
     *
     * @param string $operator
     *
     * @return $this
     */
    public function setOperator($operator);

    /**
     * Return the attribute name of the condition
     *
     * @return string|null
     */
    public function getAttributeName();

    /**
     * Set the attribute name of the condition
     *
     * @param string $attributeName Attribute Name
     *
     * @return $this
     */
    public function setAttributeName($attributeName);

    /**
     * Return the value of the condition
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Set the value of the condition
     *
     * @param mixed $value The value
     *
     * @return $this
     */
    public function setValue($value);
}
