<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Message\Syn;


class Log extends \Magento\Backend\Block\Widget\Grid\Container
{
    public function _construct()
    {
        $this->_controller = 'adminhtml_message_syn_log';
        $this->_blockGroup = 'Epicor_Comm';
        $this->_headerText = __('SYN Log');

        parent::_construct();
        
        $this->removeButton('add');
    }
}
