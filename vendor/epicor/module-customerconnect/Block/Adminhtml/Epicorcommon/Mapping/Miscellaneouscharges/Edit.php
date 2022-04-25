<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Adminhtml\Epicorcommon\Mapping\Miscellaneouscharges;


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
        $this->_controller = 'adminhtml_epicorcommon_mapping_miscellaneouscharges';
        $this->_blockGroup = 'Epicor_Customerconnect';
        $this->_mode = 'edit';

        $this->addButton('save_and_continue', array(
            'label' => __('Save And Continue Edit'),
            'class' => 'save',
        ), -100);
        $this->updateButton('save', 'label', __('Save'));
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
        if ($this->registry->registry('misc_mapping_data') && $this->registry->registry('misc_mapping_data')->getErpCode()) {
            $title = $this->registry->registry('misc_mapping_data')->getErpCode();
            return __('Edit Mapping "%1"', $this->htmlEscape($title));
        } else {
            return __('New Mapping');
        }
    }

}
