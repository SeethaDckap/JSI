<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\OrderApproval\Model\Rules;

/**
 * Group rule condition data model.
 */
class Condition extends \Magento\Rule\Model\Condition\AbstractCondition
{

    /**
     * Base name for hidden elements.
     *
     * @var string
     */
    protected $elementName = 'group';

    /**
     * @var \Epicor\Comm\Model\LocationFactory
     */
    protected $locationFactory;

    /**
     * Condition constructor.
     *
     * @param \Magento\Rule\Model\Condition\Context     $context
     * @param \Epicor\Comm\Model\LocationFactory        $locationFactory
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Epicor\Comm\Model\LocationFactory $locationFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->locationFactory = $locationFactory;
    }
    
    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'total' => __('Total (Incl. Shipping and Tax)')
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Get attribute element
     *
     * @return $this
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    /**
     * This value will define which operators will be available for this condition.
     *
     * Possible values are: string, numeric, date, select, multiselect, grid, boolean
     *
     * @return string
     */
    public function getInputType()
    {
        return 'string';
    }

    /**
     * Load operator options
     *
     * @return $this
     */
    public function loadOperatorOptions()
    {
        $this->setOperatorOption(
            [
                '==' => __('is')
            ]
        );
        return $this;
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }
    
    /**
     * Get value select options
     *
     * @return array|mixed
     */
    public function getValueSelectOptions()
    {
        return $this->getData('value_select_options');
    }

}
