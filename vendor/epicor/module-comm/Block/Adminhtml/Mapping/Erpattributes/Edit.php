<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Mapping\Erpattributes;


/*
 * Form to allow editing of epicor_comm/erp_mapping_attributes table
 */

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /*
     * Set up buttons to allow save and 'save and continue' buttons on grid for epicor_comm/erp_mapping_attributes table
     */

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
        $this->_controller = 'adminhtml_mapping_erpattributes';
        $this->_blockGroup = 'epicor_comm';
        $this->_mode = 'edit';

        $this->_addButton('save_and_continue', array(
            'label' => __('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
            ), -100);
        $this->_updateButton('save', 'label', __('Save'));

        $this->_formScripts[] = "
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
        ";
    }

    /*
     * Set up header text on grid for epicor_comm/erp_mapping_attributes table
     */

    public function getHeaderText()
    {
        $x = $this->registry->registry('erpattributes_mapping_data');
        if ($this->registry->registry('erpattributes_mapping_data') && $this->registry->registry('erpattributes_mapping_data')->getMagentoId()) {
            $title = $this->registry->registry('erpattributes_mapping_data')->getMagentoId();
            return __('Edit Attribute Mapping %s"', $this->htmlEscape($title));
        } else {
            return __('New Attribute Mapping');
        }
    }

}
