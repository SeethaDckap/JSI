<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\HostingManager\Block\Adminhtml;

/**
 * Nginx log grid container block
 * 
 * @category   Epicor
 * @package    Epicor_HostingManager
 * @author     Epicor Websales Team
 */
class Nginxlog extends \Magento\Backend\Block\Widget\Grid\Container
{

    public function _construct()
    {
        $this->_blockGroup = 'Epicor_HostingManager';
        $this->_controller = 'adminhtml_nginxlog';
        $this->_headerText = __('Nginx log');
        parent::_construct();
        $this->removeButton('add');
    }

}
