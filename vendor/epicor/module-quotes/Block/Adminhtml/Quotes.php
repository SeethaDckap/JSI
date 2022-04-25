<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Block\Adminhtml;


class Quotes extends \Magento\Backend\Block\Widget\Grid\Container
{

    public function _construct()
    {
        $this->_controller = 'adminhtml_quotes';
        $this->_blockGroup = 'Epicor_Quotes';
        $this->_headerText = __('Quotes');
        parent::_construct();

        $this->removeButton('add');
    }

}
