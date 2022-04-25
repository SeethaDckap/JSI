<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Orders\Details\Lines\Renderer;


/**
 * Line comment display
 */
class Linecomments extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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

        $index = $this->getColumn()->getIndex();
        $comment = $row->getData($index);

        $orderDisplay = $this->registry->registry('supplier_connect_order_display');
        if ($this->registry->registry('current_order_row')) {
            $this->registry->unregister('current_order_row');
        }
        $this->registry->register('current_order_row', $row);

        if ($orderDisplay == 'edit') {
            $html = '<textarea name="purchase_order[lines][' . $row->getUniqueId() . '][comment]">' . $comment . '</textarea>';
        } else {
            $html = $comment;
        }

        return $html;
    }

}
