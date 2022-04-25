<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Block\Adminhtml;


/**
 * Certificates grid container block
 * 
 * @category   Epicor
 * @package    Epicor_HostingManager
 * @author     Epicor Websales Team
 */
class Certificates extends \Magento\Backend\Block\Widget\Grid\Container
{

    public function __construct(\Magento\Backend\Block\Widget\Context $context, array $data)
    {
        $this->_blockGroup = 'Epicor_HostingManager';
        $this->_controller = 'adminhtml\Certificates';
        $this->_headerText = __('Certificates');
        $this->_addButtonLabel = __('Add Certificate');

        parent::__construct($context, $data);
    }

}
