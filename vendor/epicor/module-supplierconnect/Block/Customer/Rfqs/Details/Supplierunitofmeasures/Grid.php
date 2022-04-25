<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Supplierunitofmeasures;


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
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Magento\Framework\Registry $registry,
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

        $this->setId('rfq_supplier_unit_of_measures');
        $this->setDefaultSort('manufacturer');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);

        $this->setCustomColumns($this->_getColumns());
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);
        $this->setMessageBase('supplierconnect');
        $this->setMessageType('surd');
        $this->setIdColumn('supplier_uom');
        $this->setDataSubset('supplier_unit_of_measures/supplier_unit_of_measure');
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setCacheDisabled(true);
        $this->setShowAll(true);
        $rfq = $this->registry->registry('supplier_connect_rfq_details');
        /* @var $rfq Epicor_Common_Model_Xmlvarien */
        if($rfq) {
            $this->setCustomData((array)$rfq->getVarienDataArrayFromPath('supplier_unit_of_measures/supplier_unit_of_measure'));
        }
    }

    protected function _getColumns()
    {

        $columns = array();
        if ($this->registry->registry('rfq_editable') && $this->registry->registry('allow_conversion_override')) {
            $columns['delete_option'] = array(
                'header' => __("Delete"),
                'align' => 'center',
                'renderer' => 'Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Supplierunitofmeasures\Renderer\Delete',
                'type' => 'checkbox',
                'sortable' => false,
                'width' => '40'
            );
        }

        $columns['unit_of_measure'] = array(
            'header' => __("Unit_Of_Measure"),
            'align' => 'left',
            'index' => 'unit_of_measure',
            'renderer' => 'Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Supplierunitofmeasures\Renderer\UnitOfMeasure',
            'type' => 'text',
            'sortable' => false
        );
        $columns['conversion_factor'] = array(
            'header' => __("Conversion Factor"),
            'align' => 'left',
            'index' => 'conversion_factor',
            'renderer' => 'Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Supplierunitofmeasures\Renderer\ConversionFactor',
            'type' => 'text',
            'sortable' => false
        );
        $columns['operator'] = array(
            'header' => __("Operator"),
            'align' => 'left',
            'index' => 'operator',
            'renderer' => 'Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Supplierunitofmeasures\Renderer\Operator',
            'type' => 'text',
            'sortable' => false
        );
        $columns['value'] = array(
            'header' => __("Value"),
            'align' => 'left',
            'index' => 'value',
            'renderer' => 'Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Supplierunitofmeasures\Renderer\Value',
            'type' => 'number',
            'sortable' => false
        );
        $columns['result'] = array(
            'header' => __("Result"),
            'align' => 'left',
            'index' => 'result',
            'renderer' => 'Epicor\Supplierconnect\Block\Customer\Rfqs\Details\Supplierunitofmeasures\Renderer\Result',
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
        $html .= '<div style="display:none">
            <table>
                <tr title="" class="suom_row" id="supplier_unit_of_measures_row_template">
                    <td class="a-left">
                        <input type="checkbox" title="Delete" value="1" name="template_supplier_unit_of_measures[][delete]" class="suom_delete"/>
                        <input type="text" value="" name="template_supplier_unit_of_measures[][unit_of_measure]" class="suom_unit_of_measure"/>
                    </td>
                    <td class="a-left">
                        <input type="text" value="" name="supplier_unit_of_measures[][conversion_factor]" class="suom_conversion_factor"/>
                    </td>
                    <td class="a-left">
                        <select name="supplier_unit_of_measures[][operator]" class="suom_operator"><option value="*">Multiply</option><option value="/">Divide</option></select>
                    </td>
                    <td class="a-left">
                        <input type="text" value="" name="template_supplier_unit_of_measures[][value]" class="suom_value"/>
                    </td>
                    <td class="a-left last">
                        <input type="text" value="" name="template_supplier_unit_of_measures[][result]" class="suom_result"/>
                    </td>
                </tr>
            </table>
        </div>';
        $html .= '</script>';
        return $html;
    }

    public function getRowClass(\Magento\Framework\DataObject $row)
    {
        return 'suom_row';
    }

}
