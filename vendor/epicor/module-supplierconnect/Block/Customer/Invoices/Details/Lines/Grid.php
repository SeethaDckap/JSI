<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Invoices\Details\Lines;


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

        $this->setId('supplierconnect_invoices_lines');
        $this->setDefaultSort('_attributes_number');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);

        $this->setCustomColumns($this->_getColumns());
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setCacheDisabled(true);
        $this->setShowAll(true);

        $this->setDataSubset('invoice/lines/line');
        $this->setMessageType('suid');
        $this->setMessageBase('supplierconnect');

        $invoice = $this->registry->registry('supplier_connect_invoice_details');
        /* @var $order Epicor_Common_Model_Xmlvarien */
        if($invoice) {
            $this->setCustomData((array)$invoice->getVarienDataArrayFromPath('invoice/lines/line'));
        }
    }

    protected function _getColumns()
    {

        $columns = array(
            '_attributes_number' => array(
                'header' => __('Line'),
                'align' => 'left',
                'index' => '_attributes_number',
                'type' => 'number',
                'filter' => false
            ),
            'supplier_product_code' => array(
                'header' => __('Part Num'),
                'align' => 'left',
                'index' => 'supplier_product_code',
                'type' => 'text',
                'filter' => false,
                'keys' => array(
                    'product_code',
                    'supplier_product_code',
                ),
                'labels' => array(
                    'supplier_product_code' => 'Supplier'
                ),
                'join' => '<br />',
                'renderer' => 'Epicor\Common\Block\Renderer\Composite'
            ),
            'description' => array(
                'header' => __('Part Desc'),
                'align' => 'left',
                'index' => 'description',
                'type' => 'text',
                'filter' => false
            ),
            'supplier_quantity' => array(
                'header' => __('Qty'),
                'align' => 'left',
                'index' => 'supplier_quantity',
                'type' => 'number',
                'filter' => false,
                'keys' => array(
                    'quantity',
                    'supplier_quantity',
                ),
                'labels' => array(
                    'supplier_quantity' => 'Supplier'
                ),
                'join' => '<br />',
                'renderer' => 'Epicor\Common\Block\Renderer\Composite'
            ),
            'supplier_unit_of_measure_code' => array(
                'header' => __('U/M'),
                'align' => 'left',
                'index' => 'supplier_unit_of_measure_code',
                'type' => 'text',
                'filter' => false,
                'keys' => array(
                    'unit_of_measure_code',
                    'supplier_unit_of_measure_code',
                ),
                'labels' => array(
                    'supplier_unit_of_measure_code' => 'Supplier'
                ),
                'join' => '<br />',
                'renderer' => 'Epicor\Common\Block\Renderer\Composite'
            ),
            'price' => array(
                'header' => __('Unit Cost'),
                'align' => 'left',
                'index' => 'price',
                'type' => 'number',
                'filter' => false,
                'renderer' => 'Epicor\Supplierconnect\Block\Customer\Invoices\Details\Lines\Renderer\Currency'
            ),
            'per' => array(
                'header' => __('Per'),
                'align' => 'left',
                'index' => 'per',
                'type' => 'text',
                'filter' => false,
            ),
            'line_value' => array(
                'header' => __('Amount'),
                'align' => 'left',
                'index' => 'line_value',
                'type' => 'number',
                'filter' => false,
                'renderer' => 'Epicor\Supplierconnect\Block\Customer\Invoices\Details\Lines\Renderer\Currency'
            ),
            'charges' => array(
                'header' => __('Misc Charges'),
                'align' => 'left',
                'index' => 'charges',
                'type' => 'number',
                'filter' => false,
                'renderer' => 'Epicor\Supplierconnect\Block\Customer\Invoices\Details\Lines\Renderer\Currency'
            ),
            'packing_slip' => array(
                'header' => __('Packing'),
                'align' => 'left',
                'index' => 'packing_slip',
                'type' => 'text',
                'filter' => false,
                'keys' => array(
                    'packing_slip',
                    'pack_line',
                ),
                'labels' => array(
                    'packing_slip' => 'Slip',
                    'pack_line' => 'Line'
                ),
                'join' => '<br />',
                'renderer' => 'Epicor\Common\Block\Renderer\Composite'
            )
        );

        return $columns;
    }

    public function getRowUrl($row)
    {
        return null;
    }

}
