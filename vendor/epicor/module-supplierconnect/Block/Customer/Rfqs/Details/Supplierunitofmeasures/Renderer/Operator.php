<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Supplierunitofmeasures\Renderer;


class Operator extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
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
            if (!$this->registry->registry('allow_conversion_override')) {
                $html = '<input type="hidden" class="suom_operator" name="supplier_unit_of_measures[existing][' . $row->getUnitOfMeasure() . '][operator]" value="' . htmlspecialchars($row->getOperator()) . '" /> ';
                $html .= ($row->getOperator() == '*') ? 'Multiply' : 'Divide';
            } else {
                $html = '<select class="suom_operator" name="supplier_unit_of_measures[existing][' . $row->getUnitOfMeasure() . '][operator]">';
                $html .= '<option value="*" ' . (($row->getOperator() == '*') ? 'selected="selected"' : '') . '>Multiply</option>';
                $html .= '<option value="/" ' . (($row->getOperator() == '/') ? 'selected="selected"' : '') . '>Divide</option>';
                $html .= '</select>';
            }
        } else {
            $html = ($row->getOperator() == '*') ? 'Multiply' : 'Divide';
        }
        return $html;
    }

}
