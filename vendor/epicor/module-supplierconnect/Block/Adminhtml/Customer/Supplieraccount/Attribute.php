<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Adminhtml\Customer\Supplieraccount;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Erpaccount
 *
 * @author David.Wylie
 */
class Attribute extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_controller = 'adminhtml_customer_supplieraccount_attribute';
        $this->_blockGroup = 'Epicor_Supplierconnect';
        $this->_headerText = __('Supplier Accounts');

        $this->buttonList->add(
            'cancel', [
            'label' => __('Cancel'),
            'onclick' => 'accountSelector.closepopup()',
            'class' => 'scalable '
            ], 1
        );

        parent::_construct();
        $this->buttonList->remove('add');
    }
}
