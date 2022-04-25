<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Crossreferenceparts;


/**
 * Customer Orders list Grid config
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

       public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        array $data = []
    )
    {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $data
        );

        #    $this->setId('order_number');     
        $this->setId('rfq_cross_reference_parts');
        $this->setDefaultSort('manufacturer');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);

        $this->setCustomColumns($this->_getColumns());
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);
        $this->setMessageBase('supplierconnect');
        $this->setMessageType('surd');
        $this->setIdColumn('manufacturer');
        $this->setDataSubset('cross_reference_parts/cross_reference_part');
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        $this->setCacheDisabled(true);
        $this->setShowAll(true);


        $rfq = $this->registry->registry('supplier_connect_rfq_details');
        /* @var $order Epicor_Common_Model_Xmlvarien */
        $xref = array();
        if($rfq) {
            $xrefData = (array)$rfq->getVarienDataArrayFromPath('cross_reference_parts/cross_reference_part');

            // add a unique id so we have a html array key for these things
            foreach ($xrefData as $row) {
                $row->setUniqueId(uniqid());
                $xref[] = $row;
            }
        }
        $this->setCustomData($xref);
    }

    protected function _getColumns()
    {
        $columns = array();
        if ($this->registry->registry('rfq_editable')) {
            $columns['delete_option'] = array(
                'header' => __("Delete"),
                'align' => 'center',
                'renderer' => 'Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Crossreferenceparts\Renderer\Delete',
                'type' => 'checkbox',
                'sortable' => false,
                'width' => '40'
            );
        }

        $columns['manufacturer_code'] = array(
            'header' => __('Manufacturer'),
            'align' => 'left',
            'index' => 'manufacturer_code',
            'renderer' => 'Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Crossreferenceparts\Renderer\Manufacturer',
            'type' => 'text',
            'sortable' => false
        );

        $columns['manufacturers_product_code'] = array(
            'header' => __("Manufacturer's Part"),
            'align' => 'left',
            'index' => 'manufacturers_product_code',
            'renderer' => 'Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Crossreferenceparts\Renderer\Manufacturersproductcode',
            'type' => 'text',
            'sortable' => false
        );

        $columns['supplier_product_code'] = array(
            'header' => __("Supplier's Part"),
            'align' => 'left',
            'index' => 'supplier_product_code',
            'renderer' => 'Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Crossreferenceparts\Renderer\Supplierproductcode',
            'type' => 'text',
            'sortable' => false
        );

        $columns['supplier_lead_days'] = array(
            'header' => __("Supplier's Lead Days"),
            'align' => 'left',
            'index' => 'supplier_lead_days',
            'renderer' => 'Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Crossreferenceparts\Renderer\Supplierleaddays',
            'type' => 'text',
            'sortable' => false
        );

        $columns['supplier_reference'] = array(
            'header' => __("Supplier Reference"),
            'align' => 'left',
            'index' => 'supplier_reference',
            'renderer' => 'Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Crossreferenceparts\Renderer\Supplierreference',
            'type' => 'text',
            'sortable' => false
        );

        return $columns;
    }

    public function getRowUrl($row)
    {
        return null;
    }

    public function _toHtml()
    {
        $html = parent::_toHtml();

        $rfq = $this->registry->registry('supplier_connect_rfq_details');
        $manufacturerSelect = '<select class="cross_reference_part_manufacturer" name="template_cross_reference_parts[existing][][manufacturer_code]">';
        $manufacturerSelect .= '<option value=""></option>';
        $productCodeSelect = '';

        if ($rfq->getCrossReferenceManufacturers()) {
            foreach ($rfq->getCrossReferenceManufacturers()->getasarrayCrossReferenceManufacturer() as $x => $manufacturer) {
                $manufacturerSelect .= '<option value="' . $manufacturer->getManufacturerCode() . '">' . $manufacturer->getDescription() . '</option>';
                $productCodeSelect .= '<select id="manufacturer_product_codes_' . $manufacturer->getManufacturerCode() . '" class="cross_reference_part_manufacturers_product_code" name="template_cross_reference_parts[existing][][manufacturers_product_code]">';
                if ($manufacturer->getManufactureParts()) {
                    foreach ($manufacturer->getManufactureParts()->getasarrayProductCode() as $productCode) {
                        $productCodeSelect .= '<option value="' . $productCode . '">' . $productCode . '</option>';
                    }
                }
                $productCodeSelect .= '</select>';
            }
        }

        $manufacturerSelect .= '</select>';

        $html .= '<div style="display:none">
            ' . $productCodeSelect . '
            <table>
            <tr title="" class="xref_row" id="cross_reference_parts_row_template">
                <td class="a-center">
                    <input type="checkbox" name="template_cross_reference_parts[][delete]" class="cross_reference_part_delete" />
                </td>
                <td class="a-left ">
                    ' . $manufacturerSelect . '
                </td>
                <td class="a-left ">
                    <select name="template_cross_reference_parts[][manufacturers_product_code]" class="cross_reference_part_manufacturers_product_code">
                        <option value=""></option>
                    </select>
                </td>
                <td class="a-left ">
                    <input type="text" value="" name="template_cross_reference_parts[][supplier_product_code]" class="cross_reference_part_supplier_product_code">
                </td>
                <td class="a-left ">
                    <input type="text" value="" name="template_cross_reference_parts[][supplier_lead_days]" class="cross_reference_part_supplier_lead_days">
                </td>
                <td class="a-left last">
                    <input type="text" value="" name="template_cross_reference_parts[][supplier_reference]" class="cross_reference_part_supplier_reference">
                </td>
            </tr>
            </table>
        </div>';
        $html .= '</script>';
        return $html;
    }

    public function getRowClass(\Magento\Framework\DataObject $row)
    {
        return 'xref_row';
    }

}
