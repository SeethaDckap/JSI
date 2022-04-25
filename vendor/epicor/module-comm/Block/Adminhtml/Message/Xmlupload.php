<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Message;


class Xmlupload extends \Magento\Backend\Block\Widget\Form\Container
{

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        $this->_controller = 'adminhtml\Message';
        $this->_blockGroup = 'Epicor_Comm';
        $this->_headerText = __('XML Upload');
        $this->_mode = 'xmlupload';
        //M1 > M2 Translation Begin (Rule 16)
        $this->_formScripts = ["
             require(['jquery'], function($){
                var saveUrl = $('#edit_form').attr('action');
                $('#input_type').on('change',function(){
                    if($(this).val() == 1){
                        $('#xml_message').addClass('no-display');
                        $('#xml_message').addClass('ignore-validate');
                        $('#xml_file').removeClass('no-display');
                        $('#xml_file').removeClass('ignore-validate');
                    }else{
                        $('#xml_file').addClass('no-display');
                        $('#xml_file').addClass('ignore-validate');
                        $('#xml_message').removeClass('no-display');
                        $('#xml_message').removeClass('ignore-validate');
                    }
                });
                $('#save').on('click',function(){
                    if($('#xml_message').val() != ''){
                        var xml = $('#xml_message').val();
                        $('#post-xml').val(btoa(xml));
                        $('#xml_message').val($('<p> . </p>').text());
                        $('#edit_form').attr('action',saveUrl)
                    }
                    
                });
           });
        "];
        //M1 > M2 Translation End
        parent::__construct(
            $context,
            $data
        );
        $this->removeButton('back');

        $this->updateButton('save', 'label', __('Upload'));
    }

}
