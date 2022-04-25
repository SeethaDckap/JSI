<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Access\Right;


class Create extends \Magento\Backend\Block\Widget\Form\Container
{

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $data
        );

        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_access_right';
        $this->_blockGroup = 'epicor_common';
        $this->_mode = 'new';

        $this->_addButton('save_and_continue', array(
            'label' => __('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
            ), -100);
        $this->_updateButton('save', 'label', __('Save'));

        //M1 > M2 Translation Begin (Rule 17)
        /*$this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('form_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'view_form');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'view_form');
                }
            }
 
            function saveAndContinueEdit(){
                editForm.submit($('view_form').action+'back/edit/');
            }
        ";*/
        $this->_formScripts[] = "
           require(['jquery'], function($){
                var saveUrl = $('#view_form').attr('action');
                $('#save_and_continue').on('click',function(){
                    $('#edit_form').attr('action',saveUrl + 'back/edit/')
                    $('#edit_form').eq(0).submit();
                });
           });
        ";
        //M1 > M2 Translation End
    }

    public function getHeaderText()
    {
        return __('New Mapping');
    }

}
