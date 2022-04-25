<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Pricebreaks\Renderer;


class Quantity extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($this->registry->registry('rfq_editable')) {

            $oldDetails = array(
                'price_break_code' => $row->getPriceBreakCode(),
                'quantity' => $row->getQuantity(),
                'days_out' => $row->getDaysOut(),
                'modifier' => $row->getModifier(),
                'effective_price' => $row->getEffectivePrice()
            );

            $html = '<input type="hidden" name="price_breaks[existing][' . $row->getQuantity() . '][old_data]" value="' . base64_encode(serialize($oldDetails)) . '" />';
            $html .= '<input type="hidden" name="price_breaks[existing][' . $row->getQuantity() . '][price_break_code]" value="' . $row->getPriceBreakCode() . '" />';
            $html .= '<input type="text" class="price_break_min_quantity" name="price_breaks[existing][' . $row->getQuantity() . '][quantity]" value="' . $row->getQuantity() . '" />';
        } else {
            $html = $row->getQuantity();
        }
        return $html;
    }

}
