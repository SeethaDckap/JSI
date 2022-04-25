<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Account\Manage\Pricingrules;


/**
 * Sales Rep Account Pricing Rules List
 * 
 * @category   Epicor
 * @package    Epicor_SalesRep
 * @author     Epicor Websales Team
 */
class Form extends \Magento\Backend\Block\Widget\Form
{

    /**
     * @var \Epicor\SalesRep\Helper\Account\Manage
     */
    protected $salesRepAccountManageHelper;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var Epicor\SalesRep\Block\Widget\Form\Renderer\FromDate
     */
    protected $renderer_fromdate;
    /**
     * @var Epicor\SalesRep\Block\Widget\Form\Renderer\ToDate
     */
    protected $renderer_todate;
    
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
        \Epicor\SalesRep\Helper\Account\Manage $salesRepAccountManageHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $backendWidgetFormRendererFieldset,
        \Magento\CatalogRule\Model\RuleFactory $catalogRuleRuleFactory,
        \Magento\Rule\Block\Conditions $ruleConditions,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Epicor\SalesRep\Block\Widget\Form\Renderer\FromDate  $renderer_fromdate,
        \Epicor\SalesRep\Block\Widget\Form\Renderer\ToDate  $renderer_todate,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->salesRepAccountManageHelper = $salesRepAccountManageHelper;
        $this->commHelper = $commHelper;
        $this->backendWidgetFormRendererFieldset = $backendWidgetFormRendererFieldset;
        $this->catalogRuleRuleFactory = $catalogRuleRuleFactory;
        $this->ruleConditions = $ruleConditions;
        $this->renderer_fromdate = $renderer_fromdate;
        $this->renderer_todate = $renderer_todate;
        parent::__construct(
            $context,
            $data
        );
        $this->setTemplate('Epicor_SalesRep::epicor/salesrep/widget/grid/form.phtml');
    }


    protected function _prepareForm()
    {
        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper \Epicor\SalesRep\Helper\Account\Manage */
        $editable = $helper->canEdit();

        $form = $this->formFactory->create();
        $fieldset = $form->addFieldset('pricing_rule_form', array('legend' => __('Pricing Rule')));

        $fieldset->addField('pricing_rule_post_url', 'hidden', array(
            'name' => 'pricingRulePostUrl',
            'value' => $this->getUrl('*/*/pricingrulespost')
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
            //'required' => true,
            'name' => 'name',
            'disabled' => !$editable
        ));
        
      if($editable){
          
        $fieldset->addField('from_date', 'date', array(
                 'label' => __('From Date'),
                 'required' => false,
                  'format' => 'yyyy-MM-dd',
               )
              )->setRenderer($this->renderer_fromdate);
        
        $fieldset->addField('to_date', 'date', array(
                 'label' => __('To Date'),
                 'required' => false,
               )
              )->setRenderer($this->renderer_todate);
      }else{
            $fieldset->addField('from_date', 'date', array(
                'label' => __('From Date'),
                'required' => false,
                'name' => 'from_date',
                //'comment' => 'Change Date Using Date Picker',
                'format' => 'yyyy-MM-dd',
                'disabled' => !$editable
            ));

            $fieldset->addField('to_date', 'date', array(
                'label' => __('To Date'),
                'required' => false,
                'name' => 'to_date',
                //'comment' => 'Change Date Using Date Picker',
                'format' => 'yyyy-MM-dd',
                'disabled' => !$editable
            ));
      }   
        $fieldset->addField('priority', 'text', array(
            'label' => __('Priority'),
            //'required' => true,
            'name' => 'priority',
            'class' => 'validate-number',
            'after_element_html' => __('The Higher the Number, the Higher the Priority'),
            'disabled' => !$editable
        ));

        $fieldset->addField('is_active', 'select', array(
            'label' => __('Status'),
            //'required' => true,
            'name' => 'is_active',
            'values' => array(
                '1' => __('Active'),
                '0' => __('Inactive')
            ),
            'disabled' => !$editable
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
            'disabled' => !$editable
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
            'disabled' => !$editable
        ));

        $fieldset->addField('action_amount', 'text', array(
            'label' => __('Margin Amount'),
            //'required' => true,
            'name' => 'action_amount',
            'class' => 'validate-number',
            'disabled' => !$editable
        ));

        if ($editable) {
            $fieldset->addField('updatePricingRuleSubmit', 'submit', array(
                'value' => __('Update'),
                'onclick' => "return pricingRules.rowUpdate();",
                'name' => 'updatePricingRuleSubmit',
                'class' => 'form-button',
            ));

            $fieldset->addField('addPricingRuleSubmit', 'submit', array(
                'value' => __('Add'),
                'onclick' => "return pricingRules.rowUpdate();",
                'name' => 'addPricingRuleSubmit',
                'class' => 'form-button',
            ));
        }

        $this->setForm($form);
    }

}
