<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Adminhtml;


/**
 * Dealer Groups Admin actions
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Groups extends \Magento\Backend\Block\Widget\Grid\Container
{
 
    public function _construct()
    {
        $this->_controller = 'adminhtml\Groups';
        $this->_blockGroup = 'Epicor_Dealerconnect';
        $this->_headerText = __('Dealer Groups');
        $this->_addButtonLabel = __('Add New Group');

        parent::_construct();
    }
}
