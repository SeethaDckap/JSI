<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Orders\Details;


/**
 * Order Details page title
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Title extends  \Epicor\Supplierconnect\Block\Customer\Orders\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );
    }
    /**
     * get Order
     */
    public function getPurchaseOrder()
    {
        $order = $this->registry->registry('supplier_connect_order_details');
        return $order->getPurchaseOrder();
    }
    /**
     * get Order
     */
    public function getOrderDisplay()
    {
        return $this->registry->registry('supplier_connect_order_display');
    }

}
