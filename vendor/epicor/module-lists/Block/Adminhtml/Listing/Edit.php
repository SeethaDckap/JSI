<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing;


/**
 * List edit form setup
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    /**
     * @var \Epicor\Lists\Model\ListModel
     */
    private $_list;

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
        $this->_controller = 'adminhtml_listing';
        $this->_blockGroup = 'Epicor_Lists';
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
        $alertMsg = __('Invalid Option: One or more ERP Accounts must be chosen if "Exclude selected ERP Accounts" is not ticked');
        $proceedMsg = __('Do you wish to Proceed?');
        //M1 > M2 Translation Begin (Rule 17)
        /*$this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('form_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'edit_form');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'edit_form');
                }
            }
        ";*/
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
                    if($('#erp_account_link_type').length > 0){
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
     * Gets the current List
     * 
     * @return \Epicor\Lists\Model\ListModel
     */
    public function getList()
    {
        if (!$this->_list) {
            $this->_list = $this->registry->registry('list');
        }
        return $this->_list;
    }

    /**
     * Sets the header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        $list = $this->getList();
        /* @var $list Epicor_Lists_Model_ListModel */
        if ($list->getId()) {
            $title = $list->getTitle();
            //M1 > M2 Translation Begin (Rule 55)
            //return __('List: %s', $title);
            return __('List: %1', $title);
            //M1 > M2 Translation End
        } else {
            return __('New List');
        }
    }

}
