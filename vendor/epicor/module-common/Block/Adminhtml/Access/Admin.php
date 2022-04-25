<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Access;


class Admin extends \Magento\Backend\Block\Widget\Form\Container
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

        $this->_controller = 'adminhtml_access';
        $this->_blockGroup = 'epicor_common';
        $this->_mode = 'admin';

        $this->_addButton('save_and_continue', array(
            'label' => __('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
            ), -100);

        $this->_removeButton('save');
        $this->_removeButton('back');

        $ajaxUrl = $this->getUrl('adminhtml/epicorcommon_access_admin/updateelements');

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


        $this->_formScripts[] = "
            
            adminForm = new varienForm('admin_form', '');

            function saveAndContinueEdit(){
                adminForm.submit($('admin_form').action+'back/admin/');
            }
        ";
    }

    public function getHeaderText()
    {
        return __('Access Management Administration');
    }

}
