<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Block\Adminhtml;


/**
 * Sites grid container
 * 
 * @category   Epicor
 * @package    Epicor_HostingManager
 * @author     Epicor Websales Team
 */
class Sites extends \Magento\Backend\Block\Widget\Grid\Container
{

    public function __construct(\Magento\Backend\Block\Widget\Context $context, array $data)
    {
        $this->_blockGroup = 'Epicor_HostingManager';
        $this->_controller = 'adminhtml\Sites';
        $this->_headerText = __('Sites');
        $this->_addButtonLabel = __('Add Site');

        parent::__construct($context, $data);
    }


}
