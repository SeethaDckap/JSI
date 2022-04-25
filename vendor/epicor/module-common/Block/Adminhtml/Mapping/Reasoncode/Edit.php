<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Reasoncode;


class Edit extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Edit
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Widget\Context  $context,
        \Magento\Framework\Registry $registry,
        array $data)
    {
        $this->registry = $registry;
        parent::__construct($context, $data);

        $this->_objectId = 'id';
        $this->_controller = 'adminhtml\Mapping_Reasoncode';
        $this->_blockGroup = 'Epicor_Common';
        $this->_mode = 'edit';

        $this->addButton('save_and_continue', array(
            'label' => __('Save And Continue Edit'),
            //'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
            ), -100);
        $this->updateButton('save', 'label', __('Save'));
        //M1 > M2 Translation Begin (Rule 17)
        /*$this->_formScripts[] = "
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
                    $('#edit_form').attr('action',saveUrl + 'back/edit/')
                    $('#edit_form').eq(0).submit();
                });
           });
        ";
        //M1 > M2 Translation End
    }

    public function getHeaderText()
    {
        if ($this->registry->registry('reasoncode_mapping_data') && $this->registry->registry('reasoncode_mapping_data')->getCode()) {
            $title = $this->registry->registry('reasoncode_mapping_data')->getCode();
            //M1 > M2 Translation Begin (Rule 55)
            //return __('Edit Mapping "%s"', $this->htmlEscape($title));
            return __('Edit Mapping "%1"', $this->htmlEscape($title));
            //M1 > M2 Translation End
        } else {
            return __('New Mapping');
        }
    }

}
