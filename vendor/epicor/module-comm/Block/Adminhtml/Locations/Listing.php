<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Locations;


class Listing extends \Magento\Backend\Block\Widget\Grid\Container
{

    protected function _construct()
    {
        $this->_controller = 'adminhtml\Locations_Listing';
        $this->_blockGroup = 'Epicor_Comm';
        $this->_headerText = __('Locations');
        $this->_addButtonLabel = __('Add New Location');
        parent::_construct();
    }

}
