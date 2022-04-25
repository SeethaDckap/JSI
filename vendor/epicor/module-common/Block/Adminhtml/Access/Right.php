<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Access;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Right extends \Magento\Backend\Block\Widget\Grid\Container
{

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        $this->_controller = 'adminhtml_access_right';
        $this->_blockGroup = 'epicor_common';
        $this->_headerText = __('Access Rights');
        $this->_addButtonLabel = __('Add New Right');
        parent::__construct(
            $context,
            $data
        );
    }

}
