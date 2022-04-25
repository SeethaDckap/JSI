<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Locationgroups;


class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    private $_localList;
    private $_coreRegistry;


    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        \Magento\Framework\Registry $registry,
        array $data = [])
    {
        $this->_localList = $localeLists;
        $this->_coreRegistry = $registry;
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_Locationgroups';
        $this->_blockGroup = 'Epicor_Comm';
        $this->_mode = 'edit';
        parent::__construct($context, $data);
        $this->addButton('save_and_continue', array(
            'label' => __('Save And Continue Edit'),
//            'onclick' => 'saveAndContinueEdit()',
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

    /**
     *
     * @return \Epicor\Comm\Model\Location\Groups
     */
    public function getGroup()
    {
        if (!$this->_group) {
            $this->_group = $this->_coreRegistry->registry('group');
        }
        return $this->_group;
    }

    public function getHeaderText()
    {
        $group = $this->getGroup();
        $name = $location->getGroupName();
        return __('Group: %1', $name);
    }

}
