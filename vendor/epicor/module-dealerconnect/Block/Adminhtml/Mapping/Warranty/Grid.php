<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Adminhtml\Mapping\Warranty;


class Grid extends \Magento\Backend\Block\Widget\Grid\Extended  
{

    /**
     * @var \Epicor\Dealerconnect\Model\ResourceModel\EccPacAttributes\CollectionFactory
     */
    protected $warrantyCollectionFactory;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Dealerconnect\Model\ResourceModel\Warranty\CollectionFactory $warrantyCollectionFactory,
        array $data = [])
    {
        $this->warrantyCollectionFactory = $warrantyCollectionFactory;
       
        parent::__construct($context, $backendHelper, $data);
        $this->setId('warrantymappingGrid');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->warrantyCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('code', array(
            'header' => __('Warranty Code'),
            'align' => 'left',
            'index' => 'code',
            'width' => '50px'
        ));

      $this->addColumn('description', array(
          'header'    =>  __('Description'),
          'align'     => 'left',
          'index'     => 'description',
      ));
        
        $this->addColumn('status', array(
            'header' => __('Warranty Status'),
            'align' => 'left',
            'type'  => 'options',
            'index' => 'status',
            'width' => '50px',
            'renderer'  => 'Epicor\Dealerconnect\Block\Adminhtml\Mapping\Warranty\Renderer\Status',
            'options' => $this->getStatusName()
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
    
    public function getStatusName()
    {
        $statusName = array('yes' => 'Active', 'no' => 'Inactive');
        return $statusName;
    }       
    
}