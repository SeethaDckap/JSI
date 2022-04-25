<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Message;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Log
 *
 * @author David.Wylie
 */
class Log extends \Magento\Backend\Block\Widget\Grid\Container
{

    public function _construct()
    {
        $this->_controller = 'adminhtml_message_log';
        $this->_blockGroup = 'Epicor_Comm';
        $this->_headerText = __('Message Log');

        parent::_construct();
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
                        alert('Connection Failed\\n'+transport.responseText);
                        break;
                }
            }
        });";

        $this->addButton('networktest', array(
            'label' => __('Test Network Connection'),
            'onclick' => $javascript,
            'class' => 'add',
        ));
        $this->buttonList->add(
            'networktest',
            [
                'label' => __('Test Network Connection'),
                'onclick' => $javascript,
                'class' => ' primary'
            ]
        );
        $this->removeButton('add');
    }

}
