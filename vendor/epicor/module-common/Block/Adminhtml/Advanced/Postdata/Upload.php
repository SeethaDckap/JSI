<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Block\Adminhtml\Advanced\Postdata;

/**
 * Epicor_Common_Block_Adminhtml_Advanced_Cleardata
 * 
 * Form Container for post Data Form
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Upload extends \Magento\Backend\Block\Widget\Form\Container 
{

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) 
    {
        $this->_controller = 'adminhtml\Advanced\Postdata';
        $this->_blockGroup = 'Epicor_Common';
        $this->_headerText = __('Post Data');
        $this->_mode = 'upload';

        parent::__construct(
            $context,
            $data
        );

        $this->removeButton('back');
        $this->removeButton('save');

        $this->addButton('post', array(
            'label' => __('Post'),
            'class' => 'save primary',
        ), -100);
        
        $this->_formScripts[] = "
            require(['jquery'], function($){
                var saveUrl = $('#edit_form').attr('action');
                $('#post').on('click',function(){
                    if($('#post_data_store_id').val() != '' && $('#xml').val() != ''){
                        var xml = $('#xml').val();
                        $('#post-xml').val(btoa(xml));
                        $('#xml').val($('<p> . </p>').text());
                        $('#edit_form').attr('action',saveUrl)
                        $('#edit_form').eq(0).submit();
                    }else{
                        alert('Please entire required fields');
                    }
                    
                });
           }); 
        ";
    }

}
