<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Locationgroups\Listing;


class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\Groups\CollectionFactory
     */
    protected $commResourceGroupsCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Comm\Model\ResourceModel\Location\Groups\CollectionFactory $commResourceGroupsCollectionFactory,
        array $data = []
    )
    {
        $this->commResourceGroupsCollectionFactory = $commResourceGroupsCollectionFactory;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('groups_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->commResourceGroupsCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => __('Id'),
            'align' => 'left',
            'index' => 'id',
            'type' => 'number'
        ));
        $this->addColumn('group_name', array(
                'header' => __('Group Name'),
                'align' => 'left',
                'index' => 'group_name',
                'type' => 'text'
        ));
        $this->addColumn('group_expandable', array(
            'header' => __('Group Expandable'),
            'align' => 'left',
            'index' => 'group_expandable',
            'type' => 'options',
            'options' => array(
                0 => __('No'),
                1 => __('Yes')
            )
        ));
        $this->addColumn('show_aggregate_stock', array(
            'header' => __('Show Aggregate Stock'),
            'align' => 'left',
            'index' => 'show_aggregate_stock',
            'type' => 'options',
            'options' => array(
                0 => __('No'),
                1 => __('Yes')
            )
        ));
        $this->addColumn('enabled', array(
            'header' => __('Enabled'),
            'align' => 'left',
            'index' => 'enabled',
            'type' => 'options',
            'options' => array(
                0 => __('No'),
                1 => __('Yes')
            )
        ));
        $this->addColumn('order', array(
            'header' => __('Sort Order'),
            'align' => 'left',
            'index' => 'order',
            'type' => 'text'
        ));
        $this->addColumn('action', array(
            'header' => __('Action'),
            'width' => '100',
            'type' => 'action',
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
                    'confirm' => __('Are you sure you want to delete this Group? This cannot be undone')
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'id',
            'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('groupid');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => __('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => __('Delete selected Grougs?')
        ));

        return $this;
    }

}
