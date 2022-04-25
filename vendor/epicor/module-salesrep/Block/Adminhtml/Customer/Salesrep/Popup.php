<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Adminhtml\Customer\Salesrep;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Erpaccount
 *
 * @author David.Wylie
 */
class Popup extends \Magento\Backend\Block\Widget\Grid\Container
{

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        $this->_controller = 'adminhtml_customer_salesrep_popup';
        $this->_blockGroup = 'Epicor_SalesRep';
        $this->_headerText = __('Sales Rep Accounts');


        parent::__construct(
            $context,
            $data
        );
        $this->addButton(20, array('label' => 'Cancel', 'onclick' => "accountSelector.closepopup()"), 1);
        $this->removeButton('add');
    }

}
