<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Sales\Order;

class Productsearchgrid extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Sales\Order
{

    public function __construct(

    ) {
    }
    public function execute()
    {

        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('epicor_comm/adminhtml_sales_order_view_addproducts_search_grid')->toHtml()
        );
    }

    }
