<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Supplierunitofmeasures\Renderer;


class UnitOfMeasure extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
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

            $oldDetails = array(
                'unit_of_measure' => $row->getUnitOfMeasure(),
                'conversion_factor' => $row->getConversionFactor(),
                'operator' => $row->getOperator(),
                'value' => $row->getValue(),
                'result' => $row->getResult()
            );

            $html = '<input type="hidden" class="suom_old_details" name="supplier_unit_of_measures[existing][' . $row->getUnitOfMeasure() . '][old_data]" value="' . base64_encode(serialize($oldDetails)) . '" /> ';
            $html .= '<input type="hidden" class="suom_unit_of_measure" name="supplier_unit_of_measures[existing][' . $row->getUnitOfMeasure() . '][unit_of_measure]" value="' . htmlspecialchars($row->getUnitOfMeasure()) . '" /> ';
        }

        $html .= $row->getUnitOfMeasure();

        return $html;
    }

}
