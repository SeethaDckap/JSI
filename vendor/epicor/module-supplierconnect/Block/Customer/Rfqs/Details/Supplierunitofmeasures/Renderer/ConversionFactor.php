<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Supplierunitofmeasures\Renderer;


class ConversionFactor extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
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
        $html = '';

        if ($this->registry->registry('rfq_editable')) {
            $html .= '<input type="hidden" class="suom_conversion_factor" name="supplier_unit_of_measures[existing][' . $row->getUnitOfMeasure() . '][conversion_factor]" value="' . htmlspecialchars($row->getConversionFactor()) . '" /> ';
        }

        $html .= $row->getConversionFactor();

        return $html;
    }

}
