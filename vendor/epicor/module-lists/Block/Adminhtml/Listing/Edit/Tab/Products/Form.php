<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Products;


class Form extends \Magento\Backend\Block\Widget\Form
{

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\RuleFactory
     */
    protected $listsListModelRuleFactory;

    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $backendWidgetFormRendererFieldset;

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
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Epicor\Lists\Model\ListModel\RuleFactory $listsListModelRuleFactory,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $backendWidgetFormRendererFieldset,
        \Magento\Rule\Block\Conditions $ruleConditions,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->listsListModelFactory = $listsListModelFactory;
        $this->listsListModelRuleFactory = $listsListModelRuleFactory;
        $this->backendWidgetFormRendererFieldset = $backendWidgetFormRendererFieldset;
        $this->ruleConditions = $ruleConditions;
        parent::__construct(
            $context,
            $data
        );
    }


    protected function _prepareForm()
    {
        $id = $this->getRequest()->getParam('id');
        $list = $this->listsListModelFactory->create()->load($id);
        /* @var $list Epicor_Lists_Model_ListModel */
        $form = $this->formFactory->create();
        if ($list->getType() != 'Pr' && $list->getTypeInstance()->isSectionEditable('products')) {
            $conditions = $list->getConditions();
            $rule = $this->listsListModelRuleFactory->create();
            if ($conditions) {
                $rule->setConditionsSerialized($conditions);                                     // the conditions is looking for this field. no matter what you have saved your field as, populate this value here
                $rule->getConditions()->setJsFormObject('rule_conditions_fieldset');
            }
            $form->setHtmlIdPrefix('rule_');

            $fieldset = $form->addFieldset('conditions_option', array(
                'legend' => __('Conditions'))
            );
            $fieldset->addField('is_enabled', 'checkbox', array(
                'label' => __('Link products to list conditionally?'),
                'onclick' => 'this.value = this.checked ? 1 : 0;',
                'name' => 'rule_is_enabled',
                'checked' => $conditions ? true : false,
            ));
            $isEnabledJs = $fieldset->addField('is_enabled_js', 'hidden', array('name' => 'is_enabled_js'), false);
            $isEnabledJs->setAfterElementHtml('
                    <script> 
                        require([
                        "prototype"
                        ], function () {
                            if($("rule_is_enabled").checked){
                                $("rule_is_enabled").value = 1;
                                $$(".rule-tree").each(function(a){
                                    a.show();
                                });
                            }else{
                                $("rule_is_enabled").value = 0;
                                $$(".rule-tree").each(function(a){
                                    a.hide();
                                });                    
                            }
                            $("rule_is_enabled").observe("change", function(){  					  
                                if(this.value == "1"){
                                  $$(".rule-tree").first().show();
                                }else{
                                  $$(".rule-tree").first().hide();
                                }  
                           });
                       });
                    </script>
                    ');

            $checked = in_array('E', $list->getSettings()) ? true : false;
            $fieldset->addField('exclude_selected_products', 'checkbox', array(
                'label' => __('Exclude selected Products?'),
                'onclick' => 'this.value = this.checked ? 1 : 0;',
                'name' => 'exclude_selected_products',
                'checked' => $checked
            ));

            // -------------- add conditions code below ----------------
            $renderer = $this->backendWidgetFormRendererFieldset
                ->setTemplate('Epicor_Lists::epicor/lists/promo/fieldset.phtml')             // this refers to newly created fieldset
                ->setNewChildUrl($this->getUrl('catalog_rule/promo_catalog/newConditionHtml/form/rule_conditions_fieldset/form_namespace/catalog_rule_form'));

            $fieldset = $form->addFieldset('conditions_fieldset', array(
                    'legend' => __('Conditions (leave blank for all products)'),
                ))->setRenderer($renderer);


            $fieldset->addField('conditions', 'text', array(
                'name' => 'conditions',
                'label' => __('Conditions'),
                'title' => __('Conditions'),
            ))->setRule($rule)->setRenderer($this->ruleConditions);

            // -------------- conditions code above ----------------

            $data = $rule->getData();
            $data['exclude_selected_products'] = 1;
            $form->setValues($data);
            // $form->setValues($rule->getData());
        }

        $assignProductField = $form->addField('assign-products', 'hidden',['name' => 'assign-products']);
        $assignProductField->setAfterElementHtml($this->getProductAssignJs());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getProductAssignJs()
    {
        return $this->getLayout()
            ->createBlock('\Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Products\ListAssignProducts')
            ->setTemplate('Epicor_Lists::epicor/lists/product/list-assign-products.phtml')
            ->_toHtml();
    }

}
