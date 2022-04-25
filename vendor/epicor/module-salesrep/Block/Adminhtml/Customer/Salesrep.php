<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Adminhtml\Customer;


/**
 * Sales Rep Grid Block
 * 
 * @category   Epicor
 * @package    Epicor_SalesRep
 * @author     Epicor Websales Team
 */
class Salesrep extends \Magento\Backend\Block\Widget\Grid\Container
{

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        $this->_controller = 'adminhtml\Customer_Salesrep';
        $this->_blockGroup = 'Epicor_SalesRep';
        $this->_headerText = __('Sales Rep Accounts');
        parent::__construct(
            $context,
            $data
        );

        $this->updateButton('add', 'label', 'Add Sales Rep Account');
    }

}
