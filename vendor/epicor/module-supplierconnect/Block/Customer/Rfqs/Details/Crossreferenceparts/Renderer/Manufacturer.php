<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Crossreferenceparts\Renderer;


class Manufacturer extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
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

            $oldDetails = array(
                'manufacturer_code' => $row->getManufacturerCode(),
                'manufacturers_product_code' => $row->getManufacturersProductCode(),
                'supplier_product_code' => $row->getSupplierProductCode(),
                'supplier_lead_days' => $row->getSupplierLeadDays(),
                'supplier_reference' => $row->getSupplierReference()
            );

            $html = '<input type="hidden" name="cross_reference_parts[existing][' . $row->getUniqueId() . '][old_data]" value="' . base64_encode(serialize($oldDetails)) . '"/>';

            $rfq = $this->registry->registry('supplier_connect_rfq_details');
            $selected = $row->getManufacturerCode();

            $html .= '<select class="cross_reference_part_manufacturer" name="cross_reference_parts[existing][' . $row->getUniqueId() . '][manufacturer_code]">';
            if ($rfq->getCrossReferenceManufacturers()) {
                foreach ($rfq->getCrossReferenceManufacturers()->getasarrayCrossReferenceManufacturer() as $x => $manufacturer) {
                    $html .= '<option value="' . $manufacturer->getManufacturerCode() . '" ' . (($manufacturer->getManufacturerCode() == $selected) ? 'selected="selected"' : '') . '>' . $manufacturer->getDescription() . '</option>';
                }
            }
            $html .= '</select>';

            //$html .= '<input type="text" class="cross_reference_part_manufacturer" name="cross_reference_parts[existing][' . $row->getUniqueId() . '][manufacturer]" value="' . $row->getManufacturerCode() . '"/>';
        } else {
            $rfq = $this->registry->registry('supplier_connect_rfq_details');
            $selected = $row->getManufacturerCode();
            if ($rfq->getCrossReferenceManufacturers()) {
                $html = '';

                foreach ($rfq->getCrossReferenceManufacturers()->getasarrayCrossReferenceManufacturer() as $x => $manufacturer) {
                    if ($manufacturer->getManufacturerCode() == $selected) {
                        $html = $manufacturer->getDescription();
                    }
                }

                if (empty($html)) {
                    $html = $selected;
                }
            } else {
                $html = $selected;
            }
        }
        return $html;
    }

}
