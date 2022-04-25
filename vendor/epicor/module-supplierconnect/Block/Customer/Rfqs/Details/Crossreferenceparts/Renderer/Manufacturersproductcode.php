<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Crossreferenceparts\Renderer;


class Manufacturersproductcode extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
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

            $rfq = $this->registry->registry('supplier_connect_rfq_details');
            $selectedManufacturer = $row->getManufacturerCode();
            $selected = $row->getManufacturersProductCode();
            $html = '';

            $html .= '<select class="cross_reference_part_manufacturers_product_code" name="cross_reference_parts[existing][' . $row->getUniqueId() . '][manufacturers_product_code]">';
            if ($rfq->getCrossReferenceManufacturers()) {
                $manufacturers = $rfq->getCrossReferenceManufacturers()->getasarrayCrossReferenceManufacturer();
                foreach ($manufacturers as $x => $manufacturer) {
                    if ($selectedManufacturer == $manufacturer->getManufacturerCode() || count($manufacturers) == 1) {
                        if ($manufacturer->getManufactureParts()) {
                            foreach ($manufacturer->getManufactureParts()->getasarrayProductCode() as $productCode) {
                                $html .= '<option value="' . $productCode . '" ' . (($productCode == $selected) ? 'selected="selected"' : '') . '>' . $productCode . '</option>';
                            }
                        }
                    }
                }
            }
            $html .= '</select>';

            //$html = '<input type="text" class="cross_reference_part_manufacturers_product_code" name="cross_reference_parts[existing][' . $row->getUniqueId() . '][manufacturers_product_code]" value="' . $row->getManufacturersProductCode() . '" />';
        } else {
            $html = $row->getManufacturersProductCode();
        }
        return $html;
    }

}
