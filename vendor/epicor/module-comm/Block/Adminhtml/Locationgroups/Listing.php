<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Locationgroups;


class Listing extends \Magento\Backend\Block\Widget\Grid\Container
{

    protected function _construct()
    {
        $this->_controller = 'adminhtml\Locationgroups_Listing';
        $this->_blockGroup = 'Epicor_Comm';
        $this->_headerText = __('Groups');
        $this->_addButtonLabel = __('Add New Group');
        parent::_construct();
    }

}
