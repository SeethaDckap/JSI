<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Advanced\Syslog;

/**
 * System log grid container
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Container
{

    protected function _construct()
    {
        $this->_blockGroup = 'Epicor_Common';
        $this->_controller = 'adminhtml_advanced_syslog';
        $this->_headerText = __('System Logs');
        parent::_construct();
        $this->removeButton('add');
    }
}
