<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab\Sku;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Erpaccount
 *
 * @author David.Wylie
 */
class Products extends \Magento\Backend\Block\Widget\Grid\Container
{

    public function _construct( )
    {
        $this->_controller = 'adminhtml_customer_erpaccount_edit_tab_sku_products';
        $this->_blockGroup = 'Epicor_Comm';
        $this->_headerText = __('Products');

        $this->buttonList->add(20, array('label' => 'Cancel', 'onclick' => "productSelector.closepopup()"), 1);

        parent::_construct();
        $this->buttonList->remove('add');
    }

}
