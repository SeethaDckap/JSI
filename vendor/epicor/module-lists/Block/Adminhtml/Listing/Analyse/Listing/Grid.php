<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Analyse\Listing;


/**
 * List admin actions
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory
     */
    protected $listsResourceListModelCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    /**
     * @var \Epicor\Lists\Model\ListModel\TypeFactory
     */
    protected $listsListModelTypeFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory $listsResourceListModelCollectionFactory,
        \Magento\Framework\Registry $registry,
        \Epicor\Lists\Helper\Data $listsHelper,
        \Epicor\Lists\Model\ListModel\TypeFactory $listsListModelTypeFactory,
        array $data = []
    )
    {
        $this->listsResourceListModelCollectionFactory = $listsResourceListModelCollectionFactory;
        $this->registry = $registry;
        $this->listsHelper = $listsHelper;
        $this->listsListModelTypeFactory = $listsListModelTypeFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('list_grid');
        $this->setDefaultSort('priority');
        $this->setDefaultDir('DESC');
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->listsResourceListModelCollectionFactory->create();
        /* @var $collection Epicor_Lists_Model_Resource_Listing_Collection */
        $collection->addFieldToFilter('main_table.id', array('in' => $this->registry->registry('epicor_lists_analyse_ids')));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('id', array(
            'header' => __('ID'),
            'index' => 'id',
            'type' => 'number',
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('priority', array(
            'header' => __('Priority'),
            'index' => 'priority',
            'type' => 'number',
            'filter' => false,
            'sortable' => false,
        ));

        $typeModel = $this->listsListModelTypeFactory->create();
        /* @var $typeModel Epicor_Lists_Model_ListModel_Type */

        $this->addColumn('type', array(
            'header' => __('Type'),
            'index' => 'type',
            'type' => 'options',
            'options' => $typeModel->toFilterArray(),
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('title', array(
            'header' => __('Title'),
            'index' => 'title',
            'type' => 'text',
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('erp_code', array(
            'header' => __('ERP Code'),
            'index' => 'erp_code',
            'type' => 'text',
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('start_date', array(
            'header' => __('Start Date'),
            'index' => 'start_date',
            'type' => 'datetime',
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('end_date', array(
            'header' => __('End Date'),
            'index' => 'end_date',
            'type' => 'datetime',
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('active', array(
            'header' => __('Active'),
            'index' => 'active',
            'type' => 'options',
            'options' => array(
                0 => __('No'),
                1 => __('Yes')
            ),
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('status', array(
            'header' => __('Current Status'),
            'index' => 'active',
            'start_date' => 'start_date',
            'end_date' => 'end_date',
            'renderer' => 'Epicor\Lists\Block\Adminhtml\Widget\Grid\Column\Renderer\Active',
            'type' => 'options',
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('source', array(
            'header' => __('Source'),
            'index' => 'source',
            'type' => 'text',
            'filter' => false,
            'sortable' => false,
        ));

        $sku = $this->getRequest()->getPost('sku');
        $this->addColumn('product', array(
            'header' => $sku ? __('Product') : __('Total Products'),
            'filter' => false,
            'sku' => $sku,
            'sortable' => false,
            'renderer' => 'Epicor\Lists\Block\Adminhtml\Widget\Grid\Column\Renderer\Analyse\Products'
        ));


        $this->addColumn('action', array(
            'header' => __('Action'),
            'width' => '100',
            'renderer' => 'Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Action',
            'links' => 'true',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => __('View'),
                    'onclick' => 'javascript: listsAnalyse.openpopup(this, "' . $this->getUrl('*/*/listproducts') . '");',
                    'url' => 'javascript: void(0);'
                ),
                array(
                    'caption' => __('View All'),
                    'onclick' => 'javascript: listsAnalyse.openpopupallproducts(this, "' . $this->getUrl('*/*/listallproducts') . '");',
                    'url' => 'javascript: void(0);'
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'id',
            'is_system' => true,
            'filter' => false,
            'sortable' => false,
            'column_css_class' => 'analyselist_action'
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return '';
    }

}
