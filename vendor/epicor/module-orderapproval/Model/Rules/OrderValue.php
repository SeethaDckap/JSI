<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Rules;

use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;
use Magento\Framework\App\RequestInterface as Request;
use Epicor\OrderApproval\Model\GroupSave\Customers as GroupCustomers;
use Epicor\OrderApproval\Model\Rules\FrontEnd\Condition as RuleCondition;

class OrderValue extends \Magento\Rule\Model\Condition\AbstractCondition
{
    const APPROVAL_LIMIT_OPERATOR_TYPE = 'approval-limit';

    const APPROVAL_LIMIT_OPERATORS = ['=='];

    /**
     * Base name for hidden elements.
     *
     * @var string
     */
    protected $elementName = 'approval_limit';

    /**
     * @var GroupCustomers
     */
    private $groupCustomers;

    /**
     * OrderValue constructor.
     * @param Context $context
     * @param GroupCustomers $groupCustomers
     * @param array $data
     */
    public function __construct(
        Context $context,
        GroupCustomers $groupCustomers,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->groupCustomers = $groupCustomers;
    }

    /**
     * @return $this|OrderValue
     */
    public function loadOperatorOptions()
    {
        parent::loadOperatorOptions();
        $inputTypes = $this->getOperatorByInputType();
        $inputTypes[self::APPROVAL_LIMIT_OPERATOR_TYPE] = self::APPROVAL_LIMIT_OPERATORS;
        $this->setOperatorByInputType($inputTypes);
        return $this;
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'approval_limit' => __('Total')
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface|\Magento\Rule\Block\Editable
     */
    public function getValueElementRenderer()
    {
        if (strpos($this->getValueElementType(), '/') !== false) {
            return $this->_layout->getBlockSingleton($this->getValueElementType());
        }
        return $this->_layout->getBlockSingleton(\Epicor\OrderApproval\Block\Group\Rules\Renderer\Editable::class);
    }

    /**
     * Get remove link html.
     *
     * @return string
     */
    public function getRemoveLinkHtml()
    {
        if (!$this->isNotEditable()) {
            return '';
        }
        $src = $this->_assetRepo->getUrl('Epicor_OrderApproval::images/rule_component_remove.gif');
        return ' <span class="rule-param">
                    <a href="javascript:void(0)" class="rule-param-remove approval-limit" title="' . __('Remove') . '">
                       <img src="' . $src . '"/>
                    </a>
                  </span>';
    }

    /**
     * @return false
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function isNotEditable()
    {
        $ruleCondition = $this->getData('rule');
        $groupId = $this->getGroupIdFromRule($ruleCondition);
        if ($groupId && !$this->groupCustomers->isEditableByCustomer('id', $groupId)) {
            return false;
        }
        return true;
    }

    /**
     * @param $ruleCondition
     * @return mixed|null
     */
    private function getGroupIdFromRule($ruleCondition)
    {
        if ($ruleCondition instanceof RuleCondition) {
            return $ruleCondition->getData('group_id');
        }
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
        return $this->getElementType();
    }

    /**
     * @return string
     */
    private function getElementType()
    {
        switch ($this->getAttribute()) {
            case 'approval_limit':
                return self::APPROVAL_LIMIT_OPERATOR_TYPE;

            default:
                return 'text';
        }
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
     * @return OrderValue|\Magento\Framework\Data\Form\Element\AbstractElement
     * @throws \Exception
     */
    public function getValueElement()
    {
        $elementParams = [
            'name' => $this->elementName . '[' . $this->getPrefix() . '][' . $this->getId() . '][value]',
            'value' => $this->getValue(),
            'value_name' => $this->getValueName(),
            'after_element_html' => $this->getValueAfterElementHtml(),
            'explicit_apply' => $this->getExplicitApply(),
            'data-form-part' => $this->getFormName(),
            'class' => 'validate-digits'
        ];
        $elementParams = $this->getDisabled($elementParams);
        return $this->getForm()->addField(
            $this->getPrefix() . '__' . $this->getId() . '__value',
            $this->getValueElementType(),
            $elementParams
        )->setRenderer(
            $this->getValueElementRenderer()
        );
    }

    /**
     * @param $elementParams
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getDisabled($elementParams)
    {
        if (!$this->groupCustomers->isEditableByCustomer('id')) {
            $elementParams['disabled'] = 'disabled';
        }
        return $elementParams;
    }
}
