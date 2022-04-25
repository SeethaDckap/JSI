<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Block\Adminhtml\Sales\Returns\View\Tab\Details\Lines;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended {

    protected $_defaultLimit = 10000;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\Line\CollectionFactory
     */
    protected $commResourceCustomerReturnModelLineCollectionFactory;

    /**
     * @var \Epicor\Comm\Helper\Returns
     */
    protected $commReturnsHelper;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backendHelper, \Magento\Framework\Registry $registry, \Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\Line\CollectionFactory $commResourceCustomerReturnModelLineCollectionFactory, \Epicor\Comm\Helper\Returns $commReturnsHelper, \Epicor\Comm\Block\Customer\Returns\Lines\Renderer\NumberFactory $commCustomerReturnsLinesRendererNumberFactory, \Epicor\Comm\Block\Customer\Returns\Lines\Renderer\QtyFactory $commCustomerReturnsLinesRendererQtyFactory, \Epicor\Comm\Block\Customer\Returns\Lines\Renderer\ReturncodeFactory $commCustomerReturnsLinesRendererReturncodeFactory, \Epicor\Comm\Block\Customer\Returns\Lines\Renderer\NotesFactory $commCustomerReturnsLinesRendererNotesFactory, \Epicor\Comm\Block\Customer\Returns\Lines\Renderer\SourceFactory $commCustomerReturnsLinesRendererSourceFactory, \Epicor\Comm\Block\Customer\Returns\Lines\Renderer\AttachmentsFactory $commCustomerReturnsLinesRendererAttachmentsFactory, array $data = []
    ) {
        $this->commCustomerReturnsLinesRendererNumberFactory = $commCustomerReturnsLinesRendererNumberFactory;
        $this->commCustomerReturnsLinesRendererQtyFactory = $commCustomerReturnsLinesRendererQtyFactory;
        $this->commCustomerReturnsLinesRendererReturncodeFactory = $commCustomerReturnsLinesRendererReturncodeFactory;
        $this->commCustomerReturnsLinesRendererNotesFactory = $commCustomerReturnsLinesRendererNotesFactory;
        $this->commCustomerReturnsLinesRendererSourceFactory = $commCustomerReturnsLinesRendererSourceFactory;
        $this->commCustomerReturnsLinesRendererAttachmentsFactory = $commCustomerReturnsLinesRendererAttachmentsFactory;
        $this->registry = $registry;
        $this->commResourceCustomerReturnModelLineCollectionFactory = $commResourceCustomerReturnModelLineCollectionFactory;
        $this->commReturnsHelper = $commReturnsHelper;
        parent::__construct(
                $context, $backendHelper, $data
        );
        $this->setId('lines');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
    }

    protected function _prepareCollection() {
        $return = $this->registry->registry('return');
        /* @var $return Epicor_Comm_Model_Customer_ReturnModel */

        $lines = $this->commResourceCustomerReturnModelLineCollectionFactory->create();
        /* @var $lines Epicor_Comm_Model_Resource_Customer_Return_Line_Collection */
        $lines->addFieldToFilter('return_id', array('eq' => $return->getId()));

        $this->setCollection($lines);

        return parent::_prepareCollection();
    }

    public function getRowUrl($row) {
        return false;
    }

    protected function _prepareColumns() {

        $columns = array(
            'entity_id' => array(
                'header' => __('Line'),
                'align' => 'left',
                'index' => 'id',
                'type' => 'number',
                'sortable' => false,
                'renderer' => '\Epicor\Comm\Block\Customer\Returns\Lines\Renderer\Number'
            ),
            'product_code' => array(
                'header' => __('SKU'),
                'align' => 'left',
                'index' => 'product_code',
                'type' => 'text',
                'filterable' => false,
                'sortable' => false,
            ),
            'unit_of_measure_code' => array(
                'header' => __('UOM'),
                'align' => 'left',
                'index' => 'unit_of_measure_code',
                'type' => 'text',
                'filterable' => false,
                'sortable' => false,
            ),
            'qty' => array(
                'header' => __('Qty'),
                'align' => 'left',
                'index' => 'qty',
                'type' => 'text',
                'sortable' => false,
                'renderer' => '\Epicor\Comm\Block\Customer\Returns\Lines\Renderer\Qty'
            ),
            'return_code' => array(
                'header' => __('Return Code'),
                'align' => 'left',
                'index' => 'return_code',
                'type' => 'text',
                'sortable' => false,
                'renderer' => '\Epicor\Comm\Block\Customer\Returns\Lines\Renderer\Returncode'
            ),
            'note_text' => array(
                'header' => __('Notes'),
                'align' => 'left',
                'index' => 'note_text',
                'type' => 'text',
                'filterable' => false,
                'sortable' => false,
                'renderer' => '\Epicor\Comm\Block\Customer\Returns\Lines\Renderer\Notes'
            ),
            'source' => array(
                'header' => __('Source'),
                'align' => 'left',
                'index' => 'source',
                'type' => 'text',
                'filterable' => false,
                'sortable' => false,
                'renderer' => '\Epicor\Comm\Block\Customer\Returns\Lines\Renderer\Source'
            ),
            'attachments' => array(
                'header' => __('Attachments'),
                'align' => 'left',
                'filterable' => false,
                'index' => 'attachments',
                'renderer' => '\Epicor\Comm\Block\Customer\Returns\Lines\Renderer\Attachments',
                'type' => 'text',
                'filter' => false,
                'keep_data_format' => 1,
            )
        );

        $helper = $this->commReturnsHelper;
        /* @var $helper Epicor_Comm_Helper_Returns */

        $return = $this->registry->registry('return');
        /* @var $return Epicor_Comm_Model_Customer_ReturnModel */

        $this->addColumn('entity_id', $columns['entity_id']);
        $this->addColumn('product_code', $columns['product_code']);
        $this->addColumn('unit_of_measure_code', $columns['unit_of_measure_code']);
        $this->addColumn('qty', $columns['qty']);
        $this->addColumn('return_code', $columns['return_code']);
        $this->addColumn('note_text', $columns['note_text']);
        $this->addColumn('source', $columns['source']);

        if ($helper->checkConfigFlag('line_attachments', $return->getReturnType(), $return->getStoreId())) {
            $this->addColumn('attachments', $columns['attachments']);
        }

        parent::_prepareColumns();
    }

}
