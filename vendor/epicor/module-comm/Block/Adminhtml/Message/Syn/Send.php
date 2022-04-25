<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Message\Syn;


/**
 * Epicor_Comm_Block_Adminhtml_Message_Syn_Send
 * 
 * Form Block for SYN Send form
 * 
 * @author Gareth.James
 */
class Send extends \Magento\Backend\Block\Widget\Form\Container
{

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->_controller = 'adminhtml\Message\Syn';
        $this->_blockGroup = 'Epicor_Comm';
        $this->_headerText = __('Send SYN request');
        $this->_mode = 'send';
       
        
        parent::__construct(
            $context,
            $data
        );
        
        
        $ajaxUrl = $this->getUrl('adminhtml/epicorcomm_message_ajax/networktest');
        $javascript = " new Ajax.Request('$ajaxUrl', {
            method:     'get',
            onSuccess: function(transport){
            
                switch(transport.responseText) {

                    case 'true':
                        alert('Connection Successful');
                        break;
                        
                    case 'disabled':
                        alert('Connection Test Message Disabled\\nPlease Enable the Test Message and try again');
                        break;
                        
                    default:
                        alert('Connection Failed');
                        break;
                }
            }
        });";
        
        
        $this->addButton('networktest', [
            'label' =>  __('Test Network Connection'),
            'onclick' => $javascript,
            'class' => 'save primary',
        ]);
        
        $this->removeButton('back');
        $this->updateButton('save', 'label', __('Send SYN'));
       
    }

}
