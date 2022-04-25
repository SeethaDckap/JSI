<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Block\Adminhtml\Boost;

use Epicor\Elasticsearch\Model\RuleFactory;
use Epicor\Elasticsearch\Api\Data\BoostInterface;

/**
 * Create the virtual rule edit field in the category edit form.
 *
 */
class RuleCondition extends \Magento\Backend\Block\AbstractBlock
{
    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    private $formFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * Constructor.
     *
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Epicor\Elasticsearch\Model\RuleFactory $ruleFactory
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Epicor\Elasticsearch\Model\RuleFactory $ruleFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->ruleFactory = $ruleFactory;
        $this->registry    = $registry;

        parent::__construct($context, $data);
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritDoc}
     */
    protected function _toHtml()
    {
        return $this->getForm()->toHtml();
    }

    /**
     * Get Boost
     *
     * @return BoostInterface
     */
    private function getBoost()
    {
        return $this->registry->registry('current_boost');
    }

    /**
     * Create the form containing the rule field.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Framework\Data\Form
     */
    private function getForm()
    {
        $rule = $this->ruleFactory->create();
        if ($this->getBoost() && $this->getBoost()->getRuleCondition()) {
            $rule = $this->getBoost()->getRuleCondition();
        }
        $form = $this->formFactory->create();
        $form->setHtmlId('rule_condition');
        $ruleConditionField = $form->addField(
            'rule_condition',
            'text',
            ['name' => 'rule_condition', 'label' => __('Rule conditions'), 'container_id' => 'rule_condition']
        );
        $ruleConditionField->setValue($rule);
        $ruleConditionRenderer = $this->getLayout()->createBlock('Epicor\Elasticsearch\Block\Product\Conditions');
        $ruleConditionField->setRenderer($ruleConditionRenderer);
        return $form;
    }
}
