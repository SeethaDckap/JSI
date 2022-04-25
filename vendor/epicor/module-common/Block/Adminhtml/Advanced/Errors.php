<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Advanced;


/**
 * Error grid container
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Errors extends \Magento\Backend\Block\Widget\Grid\Container
{

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        $this->_controller = 'adminhtml_advanced_errors';
        $this->_blockGroup = 'Epicor_Common';
        $this->_headerText = __('Error Reports');
        parent::__construct(
            $context,
            $data
        );
        $this->removeButton('add');
    }

}
