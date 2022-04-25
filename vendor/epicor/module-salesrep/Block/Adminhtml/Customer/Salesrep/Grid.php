<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Adminhtml\Customer\Salesrep;


class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Epicor\SalesRep\Model\ResourceModel\Account\CollectionFactory
     */
    protected $salesRepResourceAccountCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\SalesRep\Model\ResourceModel\Account\CollectionFactory $salesRepResourceAccountCollectionFactory,
        array $data = []
    )
    {
        $this->salesRepResourceAccountCollectionFactory = $salesRepResourceAccountCollectionFactory;
        $this->setId('salesrep_grid');
        $this->setSaveParametersInSession(true);

        parent::__construct(
            $context,
            $backendHelper,
            $data
        );

    }

    protected function _prepareCollection()
    {
        $collection = $this->salesRepResourceAccountCollectionFactory->create();
        /* @var $collection \Epicor\SalesRep\Model\ResourceModel\Account\Collection */
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('accounts');

        //M1 > M2 Translation Begin (Rule 58)
        //$groups = $this->helper('customer')->getGroups()->toOptionArray();
        //M1 > M2 Translation End

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => __('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => __('Delete selected Sales Rep Accounts?')
        ));

        return $this;
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();



        $this->addColumn('sales_rep_id', array(
            'header' => __('Sales Rep Id'),
            'index' => 'sales_rep_id',
            'width' => '20px',
            'filter_index' => 'sales_rep_id'
        ));

        $this->addColumn('name', array(
            'header' => __('Name'),
            'index' => 'name',
            'filter_index' => 'name',
        ));

        $this->addColumn('created_at', array(
            'header' => __('Created'),
            'index' => 'created_at',
            'width' => '200px',
            'filter_index' => 'created_at',
            'type' => 'datetime',
        ));

        $this->addColumn('updated_at', array(
            'header' => __('Last Updated'),
            'index' => 'updated_at',
            'width' => '200px',
            'type' => 'datetime',
            'filter_index' => 'updated_at',
        ));
        $this->addColumn('action', array(
            'header' => __('Action'),
            'width' => '100',
            'renderer' => 'Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Action',
            'links' => 'true',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => __('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                ),
                array(
                    'caption' => __('Delete'),
                    'url' => array('base' => '*/*/delete'),
                    'field' => 'id',
                    'confirm' => __('Are you sure you want to delete this sales rep? This action cannot be undone')
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));
        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportXml', __('XML'));

        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
