<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Access\Right;


class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

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
        $this->_controller = 'adminhtml_access_right';
        $this->_blockGroup = 'epicor_common';
        $this->_mode = 'edit';

        $this->_addButton('save_and_continue', array(
            'label' => __('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
            ), -100);
        $this->_updateButton('save', 'label', __('Save'));
        $ajaxUrl = $this->getUrl('adminhtml/epicorcommon_access_right/updateelements');
        $javascript = " new Ajax.Request('$ajaxUrl', {
            method:     'get',
            onSuccess: function(transport){
                    alert('Scanning Complete');
            }
        });";

        $this->_addButton('scancontrollers', array(
            'label' => 'Update Element List',
            'onclick' => $javascript,
            'class' => 'add',
        ));

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
                editForm.submit($('edit_form').action+'back/edit/');
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
        if ($this->registry->registry('access_right_data') && $this->registry->registry('access_right_data')->getEntityId()) {
            $title = $this->registry->registry('access_right_data')->getEntityName();
            //   $title= Mage::app()->getLocale()->getCountryTranslation($title);
            //M1 > M2 Translation Begin (Rule 55)
            //return __('Edit Access Right "%s"', $this->htmlEscape($title));
            return __('Edit Access Right "%1"', $this->htmlEscape($title));
            //M1 > M2 Translation End
        } else {
            return __('New Access Right');
        }
    }

}
