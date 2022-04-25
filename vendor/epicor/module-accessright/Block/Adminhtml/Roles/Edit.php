<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Block\Adminhtml\Roles;

/**
 * Role edit form setup
 *
 * @category   Epicor
 * @package    Epicor_AccessRight
 * @author     Epicor Websales Team
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    /**
     * @var string|null
     */
    private $checkUrl = null;

    /**
     * @var \Magento\Framework\Phrase|null
     */
    private $alertERPMsg = null;

    /**
     * @var \Magento\Framework\Phrase|null
     */
    private $proceedMsg = null;

    /**
     * @var \Epicor\AccessRight\Model\RoleModel
     */
    private $_role;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Edit Role constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );

        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_roles';
        $this->_blockGroup = 'Epicor_AccessRight';
        $this->_mode = 'edit';

        /* Its not duplicate Window then add duplicate button */
        $copyFromId = $this->getRequest()->getParam('cid');
        $id = $this->getRequest()->getParam('id');
        if(!$copyFromId && $id) {
            $duplicateUrl = $this->getUrl('*/*/duplicate',
                array('cid' => $this->getRequest()->getParam('id')));

            $this->addButton('duplicate', array(
                'label' => __('Duplicate'),
                'class' => 'save',
                'onclick' => 'setLocation("' . $duplicateUrl . '")'
            ), -80);
        }
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

        $this->checkUrl = $this->getUrl('*/*/orphanCheck', $params);
        $this->alertERPMsg = __('Invalid Option: One or more ERP Accounts must be chosen if "Exclude selected ERP Accounts" is not ticked');
        $this->proceedMsg = __('Do you wish to Proceed?');

        //Custom Js on Save or Save and Continue
        $this->_formScripts[] = $this->getFormScript();
    }

    /**
     * Gets the current ROle
     *
     * @return \Epicor\AccessRight\Model\RoleModel
     */
    public function getRole()
    {
        if (!$this->_role) {
            $this->_role = $this->registry->registry('role');
        }
        return $this->_role;
    }

    /**
     * Sets the header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        $role = $this->getRole();
        /* @var $role Epicor/AccessRight/Model/RoleModel */
        if ($role->getId()) {
            $title = $role->getTitle();
            return __('Role: %1', $title);
        } else {
            return __('New Role');
        }
    }

    /**
     * Form submit action
     *
     * @return string
     */
    public function getFormScript()
    {
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
        $checkUrl = $this->checkUrl;
        $alertERPMsg = $this->alertERPMsg;
        $proceedMsg = $this->proceedMsg;
        return "
           require([
                'jquery',
                'Magento_Ui/js/modal/alert',
                'Magento_Ui/js/modal/confirm'
           ], function ($, alert, confirmation) {
                var saveUrl = $('#edit_form').attr('action');
                $('#save_and_continue').on('click', function () {
					$('.read').attr('disabled', false);
                    $('#treeview').find('.chkbox').each(function () {
                        indeterminateToChecked($(this));
                    });
                    saveAndContinueEdit('sac');
                    return false;
                });
            
                $('#save').on('click', function () {
					  $('.read').attr('disabled', false);
                    $('#treeview').find('.chkbox').each(function () {
                        indeterminateToChecked($(this));
                    });
                    saveAndContinueEdit('save');
                    return false;
                });
				function indeterminateToChecked(target){
                    if ($(target).prop('indeterminate')) {
                        $(target).prop('checked', true);
                    }
                };
            
                function saveAndContinueEdit(type) {
                    if (type != 'save') {
                        $('#edit_form').attr('action', saveUrl + 'back/edit/');
                    }
                    // if ERP Account tab loaded, then analyse changes
                    if ($('#erp_account_link_type').length > 0) {
                        var formData = $('#edit_form').serialize(true);
                        var br = '<br>';
                        $.ajax({
                            showLoader: true,
                            url: '" . $checkUrl . "',
                            data: formData,
                            type: 'POST',
                            dataType: 'json'
                        }).done(function (data) {
                            var json = data;
                            var displayMessage = json.message;
                            if (json.type == 'success') {
                                if (json.exlusionerror) {
                                    json.message = json.message + br + '$alertERPMsg';
                                    //alert(json.message);
                                    alert({
                                        title: 'Error',
                                        content: json.message
                                    });
                                    return false;
                                } else {
                                    json.message = json.message + br + '$proceedMsg';
                                    //if (!window.confirm(json.message)) {
                                    //    return false;
                                    //}
                                    confirmation({
                                        title: 'confirmation',
                                        content: json.message,
                                        actions: {
                                            confirm: function () {
                                                $('#edit_form').eq(0).submit();
                                            },
                                            cancel: function () {
                                                return false;
                                            }
                                        }
                                    });
                                    return false;
                                }
                            } else if (json.exlusionerror) {
                                json.message = '$alertERPMsg';
                                //alert(json.message);
                                alert({
                                    title: 'Error',
                                    content: json.message
                                });
                                return false;
                            }
                            $('#edit_form').eq(0).submit();
                            return false;
                        });
                    } else {
                        $('#edit_form').eq(0).submit();
                        return false;
                    }
                }
           });
        ";
    }
}
