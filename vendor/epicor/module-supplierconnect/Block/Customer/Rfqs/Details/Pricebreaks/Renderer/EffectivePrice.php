<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Pricebreaks\Renderer;


class EffectivePrice extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
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
        $html = "";
        if ($this->registry->registry('rfq_editable')) {
            $html = '<input type="hidden" class="price_break_effective_price" name="price_breaks[existing][' . $row->getQuantity() . '][effective_price]" value="' . $row->getEffectivePrice() . '" readonly="readonly" />';
        }

        $html .= '<span class="price_break_effective_price_label">' . $row->getEffectivePrice() . '</span>';

        return $html;
    }

}
