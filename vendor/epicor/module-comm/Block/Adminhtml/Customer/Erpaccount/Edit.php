<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount;


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
    protected $_erp_customer;
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

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
        $this->_controller = 'adminhtml_customer_erpaccount';
        $this->_blockGroup = 'epicor_comm';
        $this->_mode = 'edit';

        $this->addButton('save_and_continue', array(
            'label' => __('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
            ), -100);
        $this->updateButton('save', 'label', __('Save'));
        $params = array(
            'id' => $this->getRequest()->getParam('id')
        );
        $checkUrl = $this->getUrl('*/*/emptyListCheck', $params);
        $proceedMsg = __('Do you wish to Proceed?');

        //M1 > M2 Translation Begin (Rule 17)
       /* $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('form_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'edit_form');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'edit_form');
                }
            }
 
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";*/
        $this->_formScripts[] = "
           require(['jquery'], function($){
                var saveUrl = $('#edit_form').attr('action');
                $('#save_and_continue').on('click',function(){
                    saveAndContinueEdit('save_and_c');
                    return false;
                });
                
                $('#save').on('click',function(){
                    saveAndContinueEdit('save');
                    return false;
                });
                
                function saveAndContinueEdit(type){
                    var formData = $('edit_form').serialize(true);
                     $.ajax({
                        showLoader: true,
                        url: '" . $checkUrl . "',
                        data: formData,
                        type: 'POST',
                        dataType: 'json'
                    }).done(function (data) {
                        var json = data;  
                        var displayMessage = json.message;
                        if (json.exclusionerror) {
                            json.message = json.message + \"\\n\" + '$proceedMsg';
                            if (!window.confirm(json.message)) {
                                return false;
                            }
                        }
                      
                        if (type != 'save') {
                            $('#edit_form').attr('action',saveUrl + 'back/edit/')
                        }
                        $('#edit_form').eq(0).submit();
                    });
                }
           });
        ";
        //M1 > M2 Translation End
    }

    /**
     * 
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function getErpCustomer()
    {
        if (!$this->_erp_customer) {
            $this->_erp_customer = $this->registry->registry('customer_erp_account');
        }
        return $this->_erp_customer;
    }
    
    public function _prepareLayout()
    {
        if (is_object($this->getLayout()->getBlock('page.title'))) {
            $this->getLayout()->getBlock('page.title')->setPageTitle($this->getHeaderText());
        }
        return parent::_prepareLayout();
    }

    public function getHeaderText()
    {
        if ($this->getErpCustomer()) {
            $name = $this->escapeHtml($this->getErpCustomer()->getName());
            $company = $this->escapeHtml($this->getErpCustomer()->getCompany());
            $number = $this->escapeHtml($this->getErpCustomer()->getAccountNumber());
            return __('Editing ERP Account "%1" (%2 - %3)', $name, $company, $number);
        } else {
            return __('New ERP Account');
        }
    }
}
