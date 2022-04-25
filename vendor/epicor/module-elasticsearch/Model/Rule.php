<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model;

/**
 * Catalog search engine rule.
 *
 */
class Rule extends \Magento\Rule\Model\AbstractModel
{
    /**
     * @var \Epicor\Elasticsearch\Model\Rule\Condition\CombineFactory
     */
    protected $conditionsFactory;

    /**
     * @var string
     */
    protected $elementName;

    /**
     * @var \Epicor\Elasticsearch\Model\Data\ConditionFactory
     */
    private $conditionDataFactory;

    /**
     * Constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Epicor\Elasticsearch\Model\Rule\Condition\CombineFactory $conditionsFactory
     * @param \Epicor\Elasticsearch\Model\Data\ConditionFactory $conditionDataFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Epicor\Elasticsearch\Model\Rule\Condition\CombineFactory $conditionsFactory,
        \Epicor\Elasticsearch\Model\Data\ConditionFactory $conditionDataFactory,
        array $data = []
    ) {
        $this->conditionsFactory    = $conditionsFactory;
        $this->conditionDataFactory = $conditionDataFactory;
        parent::__construct($context, $registry, $formFactory, $localeDate, null, null, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function getConditionsInstance()
    {
        $condition = $this->conditionsFactory->create();
        $condition->setRule($this);
        $condition->setElementName($this->elementName);
        return $condition;
    }

    /**
     * {@inheritDoc}
     */
    public function getActionsInstance()
    {
        throw new \LogicException('Unsupported method.');
    }

    /**
     * Set the target element name (name of the input into the form).
     *
     * @param string $elementName Target element name
     *
     * @return $this
     */
    public function setElementName($elementName)
    {
        $this->elementName = $elementName;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getConditions()
    {
        $conditions = parent::getConditions();
        $conditions->setRule($this);
        $conditions->setElementName($this->elementName);
        return $conditions;
    }

    /**
     * Build a search query for the current rule.
     *
     * @return array
     */
    public function getSearchQuery()
    {
        $query      = null;
        $conditions = $this->getConditions();
        if ($conditions) {
            $query = $conditions->getSearchQuery();
        }
        return $query;
    }

    /**
     * Convert recursive array into condition data model
     *
     * @param array $input Conditions arrays.
     *
     * @return \Epicor\Elasticsearch\Model\Data\Condition
     */
    protected function arrayToConditionDataModel(array $input)
    {
        /** @var \Epicor\Elasticsearch\Model\Data\Condition $conditionDataModel */
        $conditionDataModel = $this->conditionDataFactory->create();
        foreach ($input as $key => $value) {
            switch ($key) {
                case 'type':
                    $conditionDataModel->setConditionType($value);
                    break;
                case 'attribute':
                    $conditionDataModel->setAttributeName($value);
                    break;
                case 'operator':
                    $conditionDataModel->setOperator($value);
                    break;
                case 'value':
                    $conditionDataModel->setValue($value);
                    break;
                case 'aggregator':
                    $conditionDataModel->setAggregatorType($value);
                    break;
                case 'conditions':
                    $conditions = [];
                    foreach ($value as $condition) {
                        $conditions[] = $this->arrayToConditionDataModel($condition);
                    }
                    $conditionDataModel->setConditions($conditions);
                    break;
                default:
            }
        }

        return $conditionDataModel;
    }

    /**
     * Convert recursive array into condition data model
     *
     * @param \Epicor\Elasticsearch\Model\Data\Condition $condition
     * @param string $key
     *
     * @return array
     */
    protected function dataModelToArray(\Epicor\Elasticsearch\Model\Data\Condition $condition, $key = 'conditions')
    {
        $output              = [];
        $output['type']      = $condition->getConditionType();
        $output['value']     = $condition->getValue();
        $output['attribute'] = $condition->getAttributeName();
        $output['operator']  = $condition->getOperator();

        if ($condition->getAggregatorType()) {
            $output['aggregator'] = $condition->getAggregatorType();
        }
        if ($condition->getConditions()) {
            $conditions = [];
            foreach ($condition->getConditions() as $subCondition) {
                $conditions[] = $this->dataModelToArray($subCondition, $key);
            }
            $output[$key] = $conditions;
        }
        return $output;
    }
}
