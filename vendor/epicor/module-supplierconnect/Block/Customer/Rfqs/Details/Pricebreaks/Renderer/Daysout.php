<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Pricebreaks\Renderer;


class Daysout extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
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
            $html = '<input type="text" class="price_break_days_out" name="price_breaks[existing][' . $row->getQuantity() . '][days_out]" value="' . $row->getDaysOut() . '" />';
        } else {
            $html = $row->getDaysOut();
        }

        return $html;
    }

}
