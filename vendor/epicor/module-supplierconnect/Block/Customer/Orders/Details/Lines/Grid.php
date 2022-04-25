<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Orders\Details\Lines;

/**
 * Customer Orders list Grid config
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid
{

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory
     * @param \Epicor\Common\Helper\Data $commonHelper
     * @param \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
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

        $this->setId('supplierconnect_orders_lines');
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
        $this->setIdColumn('_attributes_number');

        $this->setDataSubset('purchase_order/lines/line');
        $this->setMessageType('spod');
        $this->setMessageBase('supplierconnect');
        $this->getLineData();
    }

    /**
     * Getting Line Data from SPOD
     */
    private function getLineData()
    {
        $order = $this->registry->registry('supplier_connect_order_details');
        /* @var $order Epicor_Common_Model_Xmlvarien */
        if($order) {
            $lines = [];
            $linesData = ($order->getPurchaseOrder()->getLines()) ? $order->getPurchaseOrder()->getLines()->getasarrayLine() : [];
            foreach ($linesData as $row) {
                $row->setUniqueId(uniqid());
                $lines[] = $row;
            }
            $this->setCustomData($lines);
        }
        return;
    }

    /**
     * @return array[]
     */
    protected function _getColumns()
    {

        $columns = array(
            'expand' => array(
                'header' => __(''),
                'align' => 'left',
                'index' => 'expand',
                'type' => 'text',
                'column_css_class' => "expand-row",
                'renderer' => 'Epicor\Supplierconnect\Block\Customer\Orders\Details\Lines\Renderer\Expand'
            ),
            '_attributes_number' => array(
                'header' => __('Line'),
                'align' => 'left',
                'index' => '_attributes_number',
                'type' => 'number',
            ),
            'product_code' => array(
                'header' => __('Part Number'),
                'align' => 'left',
                'index' => 'product_code',
                'type' => 'text',
            ),
            'revision' => array(
                'header' => __('Revision'),
                'align' => 'left',
                'index' => 'revision',
                'type' => 'text',
            ),
            'description' => array(
                'header' => __('Description'),
                'align' => 'left',
                'index' => 'description',
                'type' => 'text',
            ),
            'quantity' => array(
                'header' => __('Ordered Qty'),
                'align' => 'left',
                'index' => 'quantity',
                'type' => 'number',
            ),
            'unit_of_measure_code' => array(
                'header' => __('UOM'),
                'align' => 'left',
                'index' => 'unit_of_measure_code',
                'type' => 'text',
            ),
            'price' => array(
                'header' => __('Unit cost'),
                'align' => 'left',
                'index' => 'price',
                'type' => 'number',
            ),
            'line_value' => array(
                'header' => __('Total Cost'),
                'align' => 'left',
                'index' => 'line_value',
                'type' => 'number',
                'renderer' => 'Epicor\Supplierconnect\Block\Customer\Orders\Details\Lines\Renderer\Currency'
            ),
            'line_status' => array(
                'header' => __('Status'),
                'align' => 'left',
                'index' => 'line_status',
                'type' => 'text',
                'renderer' => 'Epicor\Supplierconnect\Block\Customer\Orders\Details\Lines\Renderer\Status'
            ),
            'comment' => array(
                'header' => __('Line Comments'),
                'align' => 'left',
                'index' => 'comment',
                'type' => 'text',
                'renderer' => 'Epicor\Supplierconnect\Block\Customer\Orders\Details\Lines\Renderer\Linecomments'
            ),
            'releases' => array(
                'header' => __(''),
                'align' => 'left',
                'index' => 'releases',
                'renderer' => 'Epicor\Supplierconnect\Block\Customer\Orders\Details\Lines\Renderer\Linereleases',
                'type' => 'text',
                'filter' => false,
                'column_css_class' => "expand-content",
                'header_css_class' => "expand-content",
                'keep_data_format' => 1 // prevents data from this index being flattened
            ),
            'attachments' => array(
                'header' => __(''),
                'align' => 'left',
                'index' => 'attachments',
                'renderer' => 'Epicor\Supplierconnect\Block\Customer\Orders\Details\Lines\Renderer\Attachments',
                'type' => 'text',
                'filter' => false,
                'keep_data_format' => 1,
                'column_css_class' => "expand-content",
                'header_css_class' => "expand-content",
                'sortable' => false
            ),
        );

        return $columns;
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string|void|null
     */
    public function getRowUrl($row)
    {
        return null;
    }

}
