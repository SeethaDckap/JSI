<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Crossreferenceparts\Renderer;


class Delete extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{

    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '<input type="checkbox" class="cross_reference_part_delete" name="cross_reference_parts[existing][' . $row->getUniqueId() . '][delete]" />';
        return $html;
    }

}
