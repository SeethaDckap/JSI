<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Remotelinks;


class Edit extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Edit
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

        public function __construct(
        \Magento\Backend\Block\Widget\Context  $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        array $data)
    {
        $this->registry = $registry;
        $this->_localeResolver = $localeResolver;
        parent::__construct($context, $data);

        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_mapping_remotelinks';
        $this->_blockGroup = 'Epicor_Common';
        $this->_mode = 'edit';

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

    public function getHeaderText()
    {
        if ($this->registry->registry('remotelinks_mapping_data') && $this->registry->registry('remotelinks_mapping_data')->getMagentoId()) {
            $title = $this->registry->registry('remotelinks_mapping_data')->getMagentoId();
            //M1 > M2 Translation Begin (Rule p2-6.4)
            //$title = Mage::app()->getLocale()->getRemotelinksTranslation($title);
            $title =$this->_localeResolver->getLocale()->getRemotelinksTranslation($title);
            //M1 > M2 Translation End
            //M1 > M2 Translation Begin (Rule 55)
            //return __('Edit Mapping "%s"', $this->htmlEscape($title));
            return __('Edit Mapping "%1"', $this->htmlEscape($title));
            //M1 > M2 Translation End
        } else {
            return __('New Mapping');
        }
    }

}
