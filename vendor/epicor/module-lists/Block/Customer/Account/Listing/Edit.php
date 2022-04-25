<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Customer\Account\Listing;


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
        $this->_controller = 'customer_account_listing';
        $this->_blockGroup = 'epicor_lists';
        $this->_mode = 'edit';

        $this->_addButton('save_and_continue', array(
            'label' => __('Save And Continue Edit'),
            '//onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
            ), -100);

        $this->_updateButton('save', 'label', __('Save'));

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
