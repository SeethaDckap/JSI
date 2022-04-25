<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Message\Log;


class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Message\Log\CollectionFactory
     */
    protected $commResourceMessageLogCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\Message\LogFactory
     */
    protected $commMessageLogFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Comm\Model\ResourceModel\Message\Log\CollectionFactory $commResourceMessageLogCollectionFactory,
        \Epicor\Comm\Model\Message\LogFactory $commMessageLogFactory,
       array $data = []
    )
    {
        $this->commResourceMessageLogCollectionFactory = $commResourceMessageLogCollectionFactory;
        $this->commMessageLogFactory = $commMessageLogFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('log_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->commResourceMessageLogCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('id', array(
            'header' => __('ID'),
            'align' => 'left',
            'index' => 'id',
            'type' => 'range'
        ));

        $this->addColumn('message_parent', array(
            'header' => __('Parent'),
            'align' => 'left',
            'index' => 'message_parent',
            'type' => 'options',
            'options' => $this->commMessageLogFactory->create()->getMessageParents(),
        ));

        $this->addColumn('store', array(
            'header' => __('Store'),
            'align' => 'left',
            'index' => 'store',
            'width' => '100',
        ));

        $this->addColumn('message_type', array(
            'header' => __('Type'),
            'align' => 'left',
            'index' => 'message_type',
            'renderer' => '\Epicor\Comm\Block\Renderer\Message',
            'width' => '120',
        ));

        $this->addColumn('message_status', array(
            'header' => __('Status'),
            'align' => 'left',
            'type' => 'options',
            'index' => 'message_status',
            'renderer' => '\Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer\Messagestatus',
            'options' => $this->commMessageLogFactory->create()->getMessageStatuses(),
        ));

        $this->addColumn('cached', array(
            'header' => __('Cached'),
            'align' => 'left',
            'type' => 'text',
            'index' => 'cached',
        ));

        $this->addColumn('message_subject', array(
            'header' => __('Subject'),
            'align' => 'left',
            'index' => 'message_subject'
        ));

        $this->addColumn('message_secondary_subject', array(
            'header' => __('2nd Subject'),
            'align' => 'left',
            'index' => 'message_secondary_subject',
            'renderer' => '\Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer\Logsubject',
            'width' => '150px'
        ));

        $this->addColumn('status_code', array(
            'header' => __('Code'),
            'align' => 'left',
            'index' => 'status_code',
            'width' => '40',
        ));

        $this->addColumn('status_description', array(
            'header' => __('Description'),
            'width' => '150',
            'type' => 'text',
            'align' => 'left',
            'index' => 'status_description'
        ));

        $this->addColumn('start_datestamp', array(
            'header' => __('Start Time'),
            'align' => 'left',
            'type' => 'datetime',
            'index' => 'start_datestamp',
        ));

        $this->addColumn('duration', array(
            'header' => __('Duration (ms)'),
            'align' => 'left',
            'index' => 'duration',
            'width' => '40',
            'type' => 'range'
        ));

        $this->addColumn('url', array(
            'header' => __('Url'),
            'align' => 'left',
            'type' => 'text',
            'index' => 'url',
            'renderer' => '\Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer\Logurl',
        ));

        $this->addColumn('action', array(
            'header' => __('Action'),
            'width' => '100',
            'renderer' => '\Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Action',
            'links' => 'true',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => __('View'),
                    'url' => array('base' => '*/*/view'),
                    'field' => 'id'
                ),
                array(
                    'caption' => __('Reprocess'),
                    'url' => array('base' => '*/*/reprocess'),
                    'field' => 'id'
                ),
                array(
                    'caption' => __('Delete'),
                    'url' => array('base' => '*/*/delete'),
                    'field' => 'id',
                    'confirm' => __('Are you sure you want to delete this log entry? This action cannot be undone')
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportXml', __('XML'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/view', array('id' => $row->getId()));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('logid');

        $this->getMassactionBlock()->addItem('reprocess', array(
            'label' => __('Reprocess'),
            'url' => $this->getUrl('*/*/massReprocess'),
            'confirm' => __('Reprocess selected messages?')
        ));

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => __('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => __('Delete selected messages?')
        ));

        return $this;
    }

}
