<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Reasoncode;


class Grid extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Filter
{

    /**
     * @var \Epicor\Customerconnect\Model\ResourceModel\Erp\Mapping\Reasoncode\CollectionFactory
     */
    protected $customerconnectResourceErpMappingReasoncodeCollectionFactory;

    /**
     * @var \Epicor\Customerconnect\Model\Erp\Mapping\ReasoncodetypesFactory
     */
    protected $customerconnectErpMappingReasoncodetypesFactory;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Customerconnect\Model\ResourceModel\Erp\Mapping\Reasoncode\CollectionFactory $customerconnectResourceErpMappingReasoncodeCollectionFactory,
        \Epicor\Customerconnect\Model\Erp\Mapping\ReasoncodetypesFactory $customerconnectErpMappingReasoncodetypesFactory,

        array $data = [])
    {
        $this->customerconnectResourceErpMappingReasoncodeCollectionFactory = $customerconnectResourceErpMappingReasoncodeCollectionFactory;
        $this->customerconnectErpMappingReasoncodetypesFactory = $customerconnectErpMappingReasoncodetypesFactory;

        parent::__construct($context, $backendHelper, $data);
        $this->setId('reasoncodemappingGrid');
        $this->setDefaultSort('code');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->customerconnectResourceErpMappingReasoncodeCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('code', array(
            'header' => __('Reason Code'),
            'align' => 'left',
            'index' => 'code',
        ));
        $this->addColumn('description', array(
            'header' => __('Reason Code Description'),
            'align' => 'left',
            'index' => 'description'
        ));
        $this->addColumn('type', array(
            'header' => __('Reason Code Type'),
            'align' => 'left',
            'index' => 'type',
            'type' => 'options',
            'options' => $this->customerconnectErpMappingReasoncodetypesFactory->create()->toArray(),
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
                    'field' => 'id'
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
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
