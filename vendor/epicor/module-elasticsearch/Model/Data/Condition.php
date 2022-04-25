<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\Data;

use Magento\Framework\Api\AbstractSimpleObject;
use Epicor\Elasticsearch\Api\Data\ConditionInterface;

/**
 * CatalogRule Condition Data Model. Mostly used with WebAPI.
 */
class Condition extends AbstractSimpleObject implements ConditionInterface
{
    const KEY_CONDITION_TYPE = 'condition_type';
    const KEY_CONDITIONS = 'conditions';
    const KEY_AGGREGATOR_TYPE = 'aggregator_type';
    const KEY_OPERATOR = 'operator';
    const KEY_ATTRIBUTE_NAME = 'attribute_name';
    const KEY_VALUE = 'value';

    /**
     * {@inheritDoc}
     */
    public function getConditionType()
    {
        return $this->_get(self::KEY_CONDITION_TYPE);
    }

    /**
     * {@inheritDoc}
     */
    public function setConditionType($conditionType)
    {
        return $this->setData(self::KEY_CONDITION_TYPE, $conditionType);
    }

    /**
     * {@inheritDoc}
     */
    public function getConditions()
    {
        return $this->_get(self::KEY_CONDITIONS);
    }

    /**
     * {@inheritDoc}
     */
    public function setConditions(array $conditions = null)
    {
        return $this->setData(self::KEY_CONDITIONS, $conditions);
    }

    /**
     * {@inheritDoc}
     */
    public function getAggregatorType()
    {
        return $this->_get(self::KEY_AGGREGATOR_TYPE);
    }

    /**
     * {@inheritDoc}
     */
    public function setAggregatorType($aggregatorType)
    {
        return $this->setData(self::KEY_AGGREGATOR_TYPE, $aggregatorType);
    }

    /**
     * {@inheritDoc}
     */
    public function getOperator()
    {
        return $this->_get(self::KEY_OPERATOR);
    }

    /**
     * {@inheritDoc}
     */
    public function setOperator($operator)
    {
        return $this->setData(self::KEY_OPERATOR, $operator);
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributeName()
    {
        return $this->_get(self::KEY_ATTRIBUTE_NAME);
    }

    /**
     * {@inheritDoc}
     */
    public function setAttributeName($attributeName)
    {
        return $this->setData(self::KEY_ATTRIBUTE_NAME, $attributeName);
    }

    /**
     * {@inheritDoc}
     */
    public function getValue()
    {
        return $this->_get(self::KEY_VALUE);
    }

    /**
     * {@inheritDoc}
     */
    public function setValue($value)
    {
        return $this->setData(self::KEY_VALUE, $value);
    }
}
