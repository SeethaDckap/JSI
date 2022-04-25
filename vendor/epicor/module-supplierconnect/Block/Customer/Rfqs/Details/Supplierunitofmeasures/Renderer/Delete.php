<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Supplierunitofmeasures\Renderer;


class Delete extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{

    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '<input type="checkbox" class="suom_delete" name="supplier_unit_of_measures[existing][' . $row->getUnitOfMeasure() . '][delete]" />';
        return $html;
    }

}
