<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer;


/**
 * 
 * Customer grid for customer selector input
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Attribute extends \Magento\Backend\Block\Widget\Grid\Container
{
    public function _construct()
    {
        $this->_controller = 'adminhtml_customer_attribute';
        $this->_blockGroup = 'Epicor_Comm';
        $this->_headerText = __('Customers');
        parent::_construct();

        $this->buttonList->add(
            'cancel', [
            'label' => __('Cancel'),
            'onclick' => 'accountSelector.closepopup()',
            'class' => 'scalable '
            ], 1
        );
        $this->buttonList->remove('add');
    }
}
