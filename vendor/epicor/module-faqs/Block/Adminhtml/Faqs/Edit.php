<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Faqs\Block\Adminhtml\Faqs;


/**
 * F.A.Q. adminhtml edit form container
 * 
 * @category   Epicor
 * @package    Faq
 * @author     Epicor Websales Team
 *
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{




    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Epicor_Faqs';
        $this->_controller = 'adminhtml_faqs';

        parent::_construct();

        if ($this->_authorization->isAllowed('Epicor_Faqs::faqs_management')) {
            $this->buttonList->update('save', 'label', __('Save F.A.Q.'));
            $this->buttonList->update('delete', 'label', __('Delete F.A.Q.'));
        } else {
            $this->buttonList->remove('save');
            $this->buttonList->remove('delete');
        }
        //JS function to toggle WYSIWYG editor when clicked, doesn't do anything in IE
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('page_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'page_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'page_content');
                }
            }
        ";
    }




}
