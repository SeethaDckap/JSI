<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Supplierunitofmeasures\Renderer;


class Value extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
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

            $type = $this->registry->registry('allow_conversion_override') ? 'text' : 'hidden';

            $html = '<input type="' . $type . '" class="suom_value" name="supplier_unit_of_measures[existing][' . $row->getUnitOfMeasure() . '][value]" value="' . htmlspecialchars($row->getValue()) . '" /> ';

            if (!$this->registry->registry('allow_conversion_override')) {
                $html .= $row->getValue();
            }
        } else {
            $html = $row->getValue();
        }

        return $html;
    }

}
