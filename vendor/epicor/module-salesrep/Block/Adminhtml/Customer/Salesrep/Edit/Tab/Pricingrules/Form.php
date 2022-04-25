<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Adminhtml\Customer\Salesrep\Edit\Tab\Pricingrules;


class Form extends \Magento\Backend\Block\Widget\Form
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $backendWidgetFormRendererFieldset;

    /**
     * @var \Magento\CatalogRule\Model\RuleFactory
     */
    protected $catalogRuleRuleFactory;

    /**
     * @var \Magento\Rule\Block\Conditions
     */
    protected $ruleConditions;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $backendWidgetFormRendererFieldset,
        \Magento\CatalogRule\Model\RuleFactory $catalogRuleRuleFactory,
        \Magento\Rule\Block\Conditions $ruleConditions,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->backendWidgetFormRendererFieldset = $backendWidgetFormRendererFieldset;
        $this->catalogRuleRuleFactory = $catalogRuleRuleFactory;
        $this->ruleConditions = $ruleConditions;
        parent::__construct(
            $context,
            $data
        );
    }


    protected function _prepareForm()
    {
        $salesRepAccount = $this->registry->registry('salesrep_account');
        /* @var $salesRepAccount \Epicor\SalesRep\Model\Account */

        if (!$salesRepAccount || !$salesRepAccount->getId()) {
            return;
        }
        $form = $this->formFactory->create();
        $fieldset = $form->addFieldset('pricing_rule_form', array('legend' => __('Pricing Rule')));

        $fieldset->setHeaderBar(
            '<button title="' . __('Close') . '" type="button" class="scalable" onclick="window.pricingRules.close();"><span><span><span>' . __('Close') . '</span></span></span></button>'
        );

        $fieldset->addField('pricing_rule_post_url', 'hidden', array(
            'name' => 'pricingRulePostUrl',
            'value' => $this->getUrl('adminhtml/epicorsalesrep_customer_salesrep/pricingrulespost', array('salesrep_account_id' => $salesRepAccount->getId()))
        ));

        $fieldset->addField('id', 'hidden', array(
            'name' => 'id',
        ));

        $fieldset->addField('delete_message', 'hidden', array(
            'name' => 'deleteMessage',
            'value' => __('Are you sure you want to delete this Pricing Rule?')
        ));

        $fieldset->addField('name', 'text', array(
            'label' => __('Price Rule Name'),
            'required' => true,
            'value' => 'default',
            'name' => 'name',
        ));

        $fieldset->addField('from_date', 'date', array(
            'label' => __('From Date'),
            'required' => false,
            'name' => 'from_date',
            'comment' => 'Change Date Using Date Picker',
            'class' => 'validate-date',
            'format' => 'yyyy-MM-dd',
        ));

        $fieldset->addField('to_date', 'date', array(
            'label' => __('To Date'),
            'required' => false,
            'name' => 'to_date',
            'comment' => 'Change Date Using Date Picker',
            'class' => 'validate-date',
            'format' => 'yyyy-MM-dd',
           ));

        $fieldset->addField('priority', 'text', array(
            'label' => __('Priority'),
            //'required' => true,
            'name' => 'priority',
            'class' => 'validate-number',
            'after_element_html' => __('The Higher the Number, the Higher the Priority')
        ));

        $fieldset->addField('is_active', 'select', array(
            'label' => __('Status'),
            //'required' => true,
            'name' => 'is_active',
            'values' => array(
                '1' => __('Active'),
                '0' => __('Inactive')
            ),
        ));

        $renderer = $this->backendWidgetFormRendererFieldset
            ->setTemplate('Epicor_Lists::epicor/lists/promo/fieldset.phtml')
            ->setNewChildUrl($this->getUrl('*/promo_catalog/newConditionHtml/form/rule_conditions_fieldset'));

        $fieldset = $form->addFieldset('rule_conditions_fieldset', array(
                'legend' => __('Conditions (leave blank for all products)'))
            )->setRenderer($renderer);

        $fieldset->addField('rule_conditions', 'text', array(
            'name' => 'conditions',
            'label' => __('Conditions'),
            'title' => __('Conditions'),
            //'required' => true,
        ))->setRule($this->catalogRuleRuleFactory->create())->setRenderer($this->ruleConditions);

        $fieldset = $form->addFieldset('calculation', array(
            'legend' => __('Pricing Calculation')));

        $fieldset->addType('heading', 'Epicor\Common\Lib\Varien\Data\Form\Element\Heading');

        $fieldset->addField('pricing', 'heading', array(
            'label' => __('Sales Reps can Apply Prices Using the Following Information'),
        ));

        $fieldset->addField('action_operator', 'select', array(
            'label' => __('Apply'),
            //'required' => true,
            'name' => 'action_operator',
            'values' => array(
                'cost' => __('Up to a Percentage above the Cost Price'),
                'list' => __('Down to a Percentage below the Customer Specific Price'),
                'base' => __('Down to a Percentage below the Base Price')
            ),
        ));

        $fieldset->addField('action_amount', 'text', array(
            'label' => __('Margin Amount'),
            //'required' => true,
            'name' => 'action_amount',
            'class' => 'validate-number',
        ));

        $fieldset->addField('updatePricingRuleSubmit', 'submit', array(
            'value' => __('Update'),
            'onclick' => "event.preventDefault();window.pricingRules.rowUpdate()",
            'name' => 'updatePricingRuleSubmit',
            'class' => 'form-button',
        ));

        $fieldset->addField('addPricingRuleSubmit', 'submit', array(
            'value' => __('Add'),
            'onclick' => "event.preventDefault();window.pricingRules.rowUpdate()",
            'name' => 'addPricingRuleSubmit',
            'class' => 'form-button',
        ));

        $this->setForm($form);
    }

}
