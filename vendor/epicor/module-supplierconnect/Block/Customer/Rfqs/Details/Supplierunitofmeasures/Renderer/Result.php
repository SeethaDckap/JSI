<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Supplierunitofmeasures\Renderer;


class Result extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
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

            $html = '<input type="' . $type . '" class="suom_result" name="supplier_unit_of_measures[existing][' . $row->getUnitOfMeasure() . '][result]" value="' . htmlspecialchars($row->getResult()) . '" /> ';

            if (!$this->registry->registry('allow_conversion_override')) {
                $html .= $row->getResult();
            }
        } else {
            $html = $row->getResult();
        }

        return $html;
    }

}
