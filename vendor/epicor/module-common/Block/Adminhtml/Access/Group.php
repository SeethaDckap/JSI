<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Access;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Group extends \Magento\Backend\Block\Widget\Grid\Container
{

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        $this->_controller = 'adminhtml\Access_group';
        $this->_blockGroup = 'Epicor_Common';
        $this->_headerText = __('Access Groups');
        $this->_addButtonLabel = __('Add New Group');
        parent::__construct(
            $context,
            $data
        );
    }

}
