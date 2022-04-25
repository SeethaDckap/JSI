<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Adminhtml\Customer\Salesrep;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Edit
 *
 * @author David.Wylie
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    private $_salesrep;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );

        $this->_objectId = 'id';
        $this->_controller = 'adminhtml\Customer_Salesrep';
        $this->_blockGroup = 'Epicor_SalesRep';
        $this->_mode = 'edit';

        $this->addButton('save_and_continue', array(
            'label' => __('Save And Continue Edit'),
            //'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
            ), -100);

        $this->updateButton('save', 'label', __('Save'));
        //M1 > M2 Translation Begin (Rule 17)
        /*$this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";*/
        $this->_formScripts[] = "
            
           require([
            'Epicor_SalesRep/js/epicor/salesrep/pricing_rules',
            'jquery',
            'mage/validation',
            'prototype'
            ], function(pricingRules,jQuery){
            
                function validatePriceRule(price_rule_object){
                         if (typeof (price_rule_object) != 'undefined') {   
                                     var dataForm = jQuery('#edit_form');
                                    if(dataForm.validation() && dataForm.validation('isValid') && price_rule_object.formWrapper != null && price_rule_object.formWrapper.visible()){
                                         price_rule_object.rowUpdate();
                                     }
                           }
                };

               document.observe('dom:loaded', function () {
                    var saveUrl = jQuery('#edit_form').attr('action');
                    window.pricingRules = new pricingRules('pricing_rule_form','pricing_rules_table','pricing_rules');
                    jQuery('#save_and_continue').on('click',function(){
                          jQuery('#edit_form').attr('action',saveUrl + 'back/edit/');  
                          validatePriceRule(window.pricingRules);
                          jQuery('#edit_form').eq(0).submit();   
                    });
                     jQuery('#save').on('click',function(){
                           validatePriceRule(window.pricingRules);
                    });
                });
           });";
        //M1 > M2 Translation End
    }

    /**
     * 
     * @return \Epicor\SalesRep\Model\Account
     */
    public function getSalesRepAccount()
    {
        if (!$this->_salesrep) {
            $this->_salesrep = $this->registry->registry('salesrep_account');
        }

        return $this->_salesrep;
    }

    public function getHeaderText()
    {
        $salesRepAccount = $this->getSalesRepAccount();
        $name = $salesRepAccount->getName();
        $code = $salesRepAccount->getSalesRepId();

        if ($salesRepAccount->isObjectNew()) {
            $header = __('New Sales Rep Account');
        } else {
            $header = __('Sales Rep Account: ' . $name . ' (' . $code . ')');
        }

        return $header;
    }

}
