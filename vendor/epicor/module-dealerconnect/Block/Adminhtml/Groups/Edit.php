<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Adminhtml\Groups;


/**
 * Dealerconnect edit form setup
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    /**
     * @var \Epicor\Dealerconnect\Model\Dealergroups
     */
    private $_dealerGrp;

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
        $this->_controller = 'adminhtml\Groups';
        $this->_blockGroup = 'Epicor_Dealerconnect';
        $this->_mode = 'edit';

        $this->addButton('save_and_continue', array(
            'label' => __('Save And Continue Edit'),
            'class' => 'save',
            ), -100);
        
        $params = array(
            'id' => $this->getRequest()->getParam('id')
        );
        $this->removeButton('save', 'label', __('Save'));
        
        $this->addButton('save', array(
            'label' => __('Save'),
            'class' => 'save primary',
            ), -10);

        $checkUrl = $this->getUrl('*/*/orphanCheck', $params);
        $alertMsg = __('Invalid Option: One or more Dealer Accounts must be chosen.');
        $proceedMsg = __('Do you wish to Proceed?');
        $this->_formScripts[] = "
           require(['jquery'], function($){
                var saveUrl = $('#edit_form').attr('action');
                $('#save_and_continue').on('click',function(){
                    saveAndContinueEdit('sac');
                    return false;
                });

                $('#save').on('click',function(){
                    saveAndContinueEdit('save');
                    return false;
                });
                
                function saveAndContinueEdit(type){
                    // if ERP Account tab loaded, then analyse changes
                    if(true){
                        var formData = $('#edit_form').serialize(true);
                        $.ajax({
                            showLoader: true,
                            url: '" . $checkUrl . "',
                            data: formData,
                            type: 'POST',
                            dataType: 'json'
                        }).done(function (data) {                     
                            var json = data;
                            var displayMessage = json.message;
                            if(json.type == 'success'){
                                if (json.exlusionerror) {
                                    json.message = json.message + \"\\n\\n\" + '$alertMsg'
                                    alert(json.message);
                                    return false;
                                } else {
                                    json.message = json.message + \"\\n\\n\" + '$proceedMsg';
                                    if (!window.confirm(json.message)) {
                                        return false;
                                    }
                                }
                            } else if (json.exlusionerror) {
                                json.message = '$alertMsg'
                                alert(json.message);
                                return false;
                            }

                            if (type != 'save') {
                                $('#edit_form').attr('action',saveUrl + 'back/edit/')
                            }
                            
                            $('#edit_form').eq(0).submit();
                            return false;
                        });
                    } else {
                        if (type != 'save') {
                            $('#edit_form').attr('action',saveUrl + 'back/edit/')
                        }
                        $('#edit_form').eq(0).submit();
                        return false;
                    }
                }
           });
        ";
        //M1 > M2 Translation End
    }

    /**
     * Gets the current Dealer Group
     * 
     * @return \Epicor\Dealerconnect\Model\Dealergroups
     */
    public function getDealerGrp()
    {
        if (!$this->_dealerGrp) {
            $this->_dealerGrp = $this->registry->registry('dealergrp');
        }
        return $this->_dealerGrp;
    }

    /**
     * Sets the header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        $dealerGrp = $this->getDealerGrp();
        /* @var $dealerGrp Epicor_Dealerconnect_Model_Dealergroups */
        if ($dealerGrp->getId()) {
            $title = $dealerGrp->getTitle();
            return __('Dealer Group: %1', $title);
        } else {
            return __('New Dealer Group');
        }
    }

}
