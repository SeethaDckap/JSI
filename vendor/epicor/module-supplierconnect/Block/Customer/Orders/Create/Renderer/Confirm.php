<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Orders\Create\Renderer;


/**
 * Line comment display
 *
 * @author Pradeep.Kumar
 */
class Confirm extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {

        $disabled = (!$this->registry->registry('orders_editable')) ? 'disabled="disabled"' : '';

        $html = '<input type="checkbox" name="confirmed[]" value="' . $row->getId() . '" id="po_confirm_' . $row->getId() . '" class="po_confirm" ' . $disabled . '/>'
            . '<input type="hidden" name="purchase_order[' . $row->getId() . '][order_date]" value="' . $row->getOrderDate() . '"/>'
            . '<input type="hidden" name="purchase_order[' . $row->getId() . '][order_status]" value="' . $row->getOrderStatus() . '"/>'
            . '<input type="hidden" name="purchase_order[' . $row->getId() . '][order_confirmed]" value="' . $row->getOrderConfirmed() . '"/>'
        ;

        return $html;
    }

}
