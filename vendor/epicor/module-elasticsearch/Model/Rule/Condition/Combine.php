<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\Rule\Condition;

/**
 * Product attributes combination search engine rule.
 */
class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var string
     */
    protected $type = 'Epicor\Elasticsearch\Model\Rule\Condition\Combine';

    /**
     * @var \Epicor\Elasticsearch\Model\Rule\Condition\ProductFactory
     */
    protected $productConditionFactory;

    /**
     * Constructor.
     *
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Epicor\Elasticsearch\Model\Rule\Condition\ProductFactory $conditionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Epicor\Elasticsearch\Model\Rule\Condition\ProductFactory $conditionFactory,
        array $data = []
    ) {
        $this->productConditionFactory = $conditionFactory;
        parent::__construct($context, $data);
        $this->setType($this->type);
    }

    /**
     * {@inheritDoc}
     */
    public function getNewChildSelectOptions()
    {
        $productAttributes = $this->productConditionFactory->create()->loadAttributeOptions()->getAttributeOption();
        $attributes = [];
        $productConditionType = get_class($this->productConditionFactory->create());
        foreach ($productAttributes as $code => $label) {
            $attributes[] = [
                'value' => $productConditionType . '|' . $code,
                'label' => $label,
            ];
        }
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'label' => __('Product Attribute'),
                    'value' => $attributes,
                ],
            ]
        );
        return $conditions;
    }

    /**
     * {@inheritDoc}
     */
    public function loadArray($arr, $key = 'conditions')
    {
        $aggregator = $this->getAggregatorFromArray($arr);
        $value      = $this->getValueFromArray($arr);
        $this->setAggregator($aggregator)
            ->setValue($value);
        if (!empty($arr[$key]) && is_array($arr[$key])) {
            foreach ($arr[$key] as $conditionArr) {
                try {
                    $condition = $this->_conditionFactory->create($conditionArr['type']);
                    $condition->setElementName($this->elementName);
                    $condition->setRule($this->getRule());
                    $this->addCondition($condition);
                    $condition->loadArray($conditionArr, $key);
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                }
            }
        }
        return $this;
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
     * Read the aggregator from an array.
     *
     * @param array $arr Array.
     *
     * @return string|NULL
     */
    private function getAggregatorFromArray($arr)
    {
        return isset($arr['aggregator']) ? $arr['aggregator'] : (isset($arr['attribute']) ? $arr['attribute'] : null);
    }

    /**
     * Build a search query for the current rule.
     *
     * @return array
     */
    public function getSearchQuery()
    {
        $aggregator = $this->getAggregator();
        $value = (bool) $this->getValue();
        $aggBoolQuery = $aggregator === 'all' ? 'must' : 'should';
        $subQuery = [];
        foreach ($this->getConditions() as $condition) {
            if($condition->getSearchQuery() !== null)
            {
                $subQuery[] = $condition->getSearchQuery();
            }
        }
        if (($value === false) && !empty($subQuery))
        {
            $subQuery = [
                'bool' => [
                    'must_not' => [$subQuery],
                    'boost' => 1,
                    'adjust_pure_negative' => true,
                ]
            ];
        }
        if(($aggBoolQuery == "must") && !empty($subQuery))
        {
            return [
                'bool' => [
                    'must' => $subQuery,
                    'adjust_pure_negative' => true,
                    'boost' => 1
                ]
            ];
        }
        else if(($aggBoolQuery == "should") && !empty($subQuery))
        {
            return [
                'bool' => [
                    'should' => $subQuery,
                    'adjust_pure_negative' => true,
                    'minimum_should_match' => "1",
                    'boost' => 1
                ]
            ];
        }
    }

    /**
     * Read the value from an array.
     *
     * @param array $arr Array.
     *
     * @return mixed|NULL
     */
    private function getValueFromArray($arr)
    {
        return isset($arr['value']) ? $arr['value'] : (isset($arr['operator']) ? $arr['operator'] : null);
    }
}

