<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Locations;


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
        $this->_controller = 'adminhtml_Locations';
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
     * @return \Epicor\Comm\Model\Location
     */
    public function getLocation()
    {
        if (!$this->_location) {
            $this->_location = $this->_coreRegistry->registry('location');
        }
        return $this->_location;
    }

    public function getHeaderText()
    {
        $location = $this->getLocation();
        $code = $location->getCode();
        //M1 > M2 Translation Begin (Rule 55)
        //return __('Location: %s', $code);
        return __('Location: %1', $code);
        //M1 > M2 Translation End
    }

}
