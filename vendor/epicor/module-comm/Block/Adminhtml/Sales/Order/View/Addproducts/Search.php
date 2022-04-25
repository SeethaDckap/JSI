<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Sales\Order\View\Addproducts;


class Search extends \Magento\Backend\Block\Widget\Grid\Container
{

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        $this->_controller = 'adminhtml_sales_order_view_addproducts_search';
        $this->_blockGroup = 'epicor_comm';
        $this->_headerText = __('Add Products');
        // $this->_addButtonLabel = Mage::helper('epicor_comm')->__('Add Products To Order');

        $this->addButton(10, array('label' => 'Add Products To Order', 'onclick' => "addProduct.addProducts()", 'class' => "add"), 1);
        $this->addButton(20, array('label' => 'Cancel', 'onclick' => "addProduct.closeProductSearch()"), 1);

        parent::__construct(
            $context,
            $data
        );
        $this->removeButton('add');
    }

}
