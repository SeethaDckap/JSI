<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Adminhtml\Groups;


/**
 * Dealer Groups admin actions
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    const GROUP_STATUS_ACTIVE = 'A';
    const GROUP_STATUS_DISABLED = 'D';

    /**
     * @var \Epicor\Dealerconnect\Model\ResourceModel\Dealergroups\CollectionFactory
     */
    protected $dealerGroupsResourceModelCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Dealerconnect\Model\ResourceModel\Dealergroups\CollectionFactory $dealerGroupsResourceModelCollectionFactory,
        array $data = []
    )
    {
        $this->dealerGroupsResourceModelCollectionFactory = $dealerGroupsResourceModelCollectionFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('dealer_group_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->dealerGroupsResourceModelCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'id', array(
            'header' => __('ID'),
            'index' => 'id',
            'type' => 'number'
            )
        );

        $this->addColumn(
            'title', array(
            'header' => __('Title'),
            'index' => 'title',
            'type' => 'text'
            )
        );

        $this->addColumn(
            'active', array(
            'header' => __('Active'),
            'index' => 'active',
            'type' => 'options',
            'options' => array(
                0 => __('No'),
                1 => __('Yes')
            )
            )
        );

        $this->addColumn(
            'status', array(
            'header' => __('Current Status'),
            'index' => 'active',
            'start_date' => 'start_date',
            'end_date' => 'end_date',
            'renderer' => '\Epicor\Dealerconnect\Block\Adminhtml\Widget\Grid\Column\Renderer\Active',
            'type' => 'options',
            'options' => array(
                self::GROUP_STATUS_ACTIVE => __('Active'),
                self::GROUP_STATUS_DISABLED => __('Disabled'),
            ),
            'filter_condition_callback' => array($this, '_statusFilter'),
            )
        );

        $this->addColumn('action', array(
            'header' => __('Action'),
            'width' => '100',
            'renderer' => '\Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Action',
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
                    'confirm' => __('Are you sure you want to delete this Dealer Group? This cannot be undone')
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
            'confirm' => __('Delete selected Dealer Group?')
        ));

        $status_data = array('1' => __('Active'), '0' => __('Disabled'));

        $this->getMassactionBlock()->addItem('changestatus', array(
            'label' => __('Change Status'),
            'url' => $this->getUrl('*/*/massAssignStatus'),
            'additional' => array(
                'list_status' => array(
                    'name' => 'assign_status',
                    'type' => 'select',
                    'values' => $status_data,
                    'label' => __('Change Status'),
                )
            )
        ));



        return $this;
    }

    public function _statusFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        switch ($value) {
            case self::GROUP_STATUS_ACTIVE:
                $collection->filterActive();
                break;

            case self::GROUP_STATUS_DISABLED:
                $collection->addFieldToFilter('active', 0);
                break;
        }

        return $this;
    }

}
