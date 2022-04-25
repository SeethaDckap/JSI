<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Pricebreaks\Renderer;


class Delete extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{

    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '<input type="checkbox" class="price_break_delete" name="price_breaks[existing][' . $row->getQuantity() . '][delete]" value="1"/>';
        return $html;
    }

}
