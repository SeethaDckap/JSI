<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Block\Adminhtml\Certificates;


/**
 * Certificates edit form container block
 * 
 * @category   Epicor
 * @package    Epicor_HostingManager
 * @author     Epicor Websales Team
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

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
        $this->_controller = 'adminhtml\Certificates';
        $this->_blockGroup = 'Epicor_HostingManager';
        $this->_mode = 'edit';

        parent::__construct(
            $context,
            $data
        );

        $this->addButton('generate_csr', array(
            'label' => __('Generate CSR'),
            //'onclick' => 'generateCsr()',
            'class' => 'save',
            ), -100);

        $this->addButton('selfSign', array(
            'label' => __('Create Self Signed Certificate'),
            //'onclick' => 'createSsc()',
            'class' => 'save',
            ), -100);
        //M1 > M2 Translation Begin (Rule 17)
        /*$this->_formScripts[] = "
            function generateCsr(){
                $('certificate_tabs_csr_content').select('.form-list input').invoke('addClassName','required-entry');
                editForm.submit($('edit_form').action+'generate_csr/1/');
                $('certificate_tabs_csr_content').select('.form-list input').invoke('removeClassName','required-entry');
            }
            function createSsc(){
                $('request').addClassName('required-entry');
                $('private_key').addClassName('required-entry');
                editForm.submit($('edit_form').action+'create_ssc/1/');
                $('request').removeClassName('required-entry');
                $('private_key').removeClassName('required-entry');
            }
        ";*/
        $this->_formScripts[] = "
        require(['jquery'], function($){
                var saveUrl = $('#edit_form').attr('action');
                $('#generate_csr').on('click',function(){
                $('#certificate_tabs_csr_content').find('input').addClass('required-entry');
                $('#edit_form').attr('action',saveUrl + 'generate_csr/1/')
                $('#edit_form').eq(0).submit();
                $('#certificate_tabs_csr_content').find('input').removeClass('required-entry');
            });
            $('#selfSign').click(function(){
                $('#request').addClass('required-entry');
                $('#private_key').addClass('required-entry');
                $('#country').removeClass('minimum-length-2');
                $('#country').removeClass('maximum-length-2');
                $('#edit_form').attr('action',saveUrl + 'create_ssc/1/')
                $('#edit_form').eq(0).submit();
                $('#country').addClass('minimum-length-2');
                $('#country').addClass('maximum-length-2');                
                $('#request').removeClass('required-entry');
                $('#private_key').removeClass('required-entry');
            });
        });
        ";
        //M1 > M2 Translation End

    }

    public function getHeaderText()
    {
        if ($this->registry->registry('current_certificate')->getId()) {
            return $this->escapeHtml($this->registry->registry('current_certificate')->getName());
        } else {
            return __('New Certificate');
        }
    }

}
