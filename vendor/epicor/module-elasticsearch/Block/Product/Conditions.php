<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Block\Product;

use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * Catalog search rule contribution form element renderer.
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Conditions extends Template implements RendererInterface
{
    /**
     * @var \Magento\Rule\Block\Conditions
     */
    protected $conditions;

    /**
     * @var \Epicor\Elasticsearch\Model\Rule
     */
    protected $rule;

    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $elementFactory;

    /**
     * @var AbstractElement
     */
    protected $element;

    /**
     * @var \Magento\Framework\Data\Form\Element\Text
     */
    protected $input;

    /**
     * @var string
     */
    protected $_template = 'product/conditions.phtml';

    /**
     * Block constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param \Epicor\Elasticsearch\Model\RuleFactory $ruleFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        \Magento\Rule\Block\Conditions $conditions,
        \Epicor\Elasticsearch\Model\RuleFactory $ruleFactory,
        array $data = []
    ) {
        $this->elementFactory = $elementFactory;
        $this->conditions     = $conditions;
        $this->rule           = $ruleFactory->create();
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function render(AbstractElement $element)
    {
        $this->element = $element;
        return $this->toHtml();
    }

    /**
     * Get URL used to create a new child condition into the rule.
     *
     * @return string
     */
    public function getNewChildUrl()
    {
        $urlParams = [
            'form'         => $this->getElement()->getContainer()->getHtmlId(),
            'element_name' => $this->getElement()->getName(),
        ];

        if (is_array($this->getData('url_params'))) {
            $urlParams = array_merge($urlParams, $this->getData('url_params'));
        }

        return $this->getUrl('ecc_elasticsearch/product_rule/conditions', $urlParams);
    }

    /**
     * Get currently edited element.
     *
     * @return AbstractElement
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * Retrieve element unique container id.
     *
     * @return string
     */
    public function getHtmlId()
    {
        return $this->getElement()->getContainer()->getHtmlId();
    }

    /**
     * Render HTML of the element using the rule engine.
     *
     * @return string
     */
    public function getInputHtml()
    {
        $this->rule->setElementName($this->element->getName());

        if ($this->element->getValue()) {
            $conditions = $this->element->getValue();
            if (!is_array($conditions)) {
                $conditions = $conditions->getConditions()->asArray();
            }
            $this->rule->getConditions()->loadArray($conditions);
            $this->element->setRule($this->rule);
        }

        $this->input = $this->elementFactory->create('text');
        $this->input->setRule($this->rule)->setRenderer($this->conditions);

        $this->setConditionFormName($this->rule->getConditions(), $this->getElement()->getContainer()->getHtmlId());

        if (is_array($this->getData('url_params'))) {
            $this->setConditionUrlParams($this->rule->getConditions(), $this->getData('url_params'));
        }
        return $this->input->toHtml();
    }

    /**
     * Set proper form name to rule conditions.
     *
     * @param \Magento\Rule\Model\Condition\AbstractCondition $conditions
     * @param string $formName
     *
     * @return void
     */
    private function setConditionFormName(\Magento\Rule\Model\Condition\AbstractCondition $conditions, $formName)
    {
        $conditions->setJsFormObject($formName);

        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName);
            }
        }
    }

    /**
     * Set proper url params to rule conditions.
     *
     * @param \Magento\Rule\Model\Condition\AbstractCondition $conditions
     * @param array $urlParams
     *
     * @return void
     */
    private function setConditionUrlParams(\Magento\Rule\Model\Condition\AbstractCondition $conditions, $urlParams)
    {
        $conditions->setUrlParams($urlParams);

        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionUrlParams($condition, $urlParams);
            }
        }
    }
}
