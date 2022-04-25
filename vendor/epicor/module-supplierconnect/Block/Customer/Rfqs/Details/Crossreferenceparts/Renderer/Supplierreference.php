<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Crossreferenceparts\Renderer;


class Supplierreference extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
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
            $html = '<input type="text" class="cross_reference_part_supplier_reference" name="cross_reference_parts[existing][' . $row->getUniqueId() . '][supplier_reference]" value="' . $row->getSupplierReference() . '" />';
        } else {
            $html = $row->getSupplierReference();
        }
        return $html;
    }

}
