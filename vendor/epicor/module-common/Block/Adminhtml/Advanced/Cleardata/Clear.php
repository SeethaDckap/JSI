<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Advanced\Cleardata;


/**
 * Epicor_Common_Block_Adminhtml_Advanced_Cleardata
 * 
 * Form Container for Clear Data Form
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Clear extends \Magento\Backend\Block\Widget\Form\Container
{

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        $this->_controller = 'adminhtml\Advanced\Cleardata';
        $this->_blockGroup = 'epicor_common';
        $this->_headerText = __('Clear Data');
        $this->_mode = 'clear';

        parent::__construct(
            $context,
            $data
        );
        $this->removeButton('back');
        $this->removeButton('save');
        
        $this->addButton('cleardata', array(
            'label' => __('Clear selected data types'),
           // 'onclick' => 'clearFormSubmit()',
            'class' => 'save primary',
            ), -100);
        
        $this->_formScripts[] = "
             require(['jquery'], function($){
                var saveUrl = $('#clear_form').attr('action');
                $('#cleardata').on('click',function(){
                    if($$('#clear_form input[type=\'checkbox\']:checked').length > 0) {
                        if(confirm('Are you sure you wish to clear the selected data types? \\nThis action cannot be undone')) {
                             $('#clear_form').attr('action',saveUrl)
                             $('#clear_form').eq(0).submit();
                        }
                    } else {
                        alert('Please select one or more data types');
                    }
                
                   
                });
           });
           /*
            clearForm = new varienForm('clear_form', '');
            function clearFormSubmit(){
            
                if($$('#clear_form input[type=\'checkbox\']:checked').length > 0) {
                    if(confirm('Are you sure you wish to clear the selected data types? \\nThis action cannot be undone')) {
                        clearForm.submit();
                    }
                } else {
                    alert('Please select one or more data types');
                }
            } */
        "; 
    }

}
