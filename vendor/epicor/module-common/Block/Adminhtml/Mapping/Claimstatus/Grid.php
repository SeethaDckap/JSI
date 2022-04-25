<?php

/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Block\Adminhtml\Mapping\Claimstatus;

class Grid extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Filter {

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Erp\Mapping\Shippingmethod\CollectionFactory
     */
    protected $commResourceErpMappingShippingstatusCollectionFactory;

    public function __construct(
            \Magento\Backend\Block\Template\Context $context,
            \Magento\Backend\Helper\Data $backendHelper,
            \Epicor\Comm\Model\ResourceModel\Erp\Mapping\Claimstatus\CollectionFactory $commResourceErpMappingClaimstatusCollectionFactory,
            array $data = []) {
        $this->commResourceErpMappingClaimstatusCollectionFactory = $commResourceErpMappingClaimstatusCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
        $this->setId('claimstatussgrid');
        $this->setDefaultSort('claim_status');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = $this->commResourceErpMappingClaimstatusCollectionFactory->create();
        $this->setCollection($collection);
        if($this->_getStoreParam()){
                $collection->addFieldToFilter('store_id', $this->_getStoreParam());
        }
        return parent::_prepareCollection();
    }
    protected function _getStoreParam()
    {
        $storeId = $this->getRequest()->getParam('store');
        return $storeId;
    }
    protected function _prepareColumns() {
        $this->addColumn('erp_code', array(
            'header' => __('ERP code'),
            'align' => 'left',
            'index' => 'erp_code',
        ));
        $this->addColumn('claim_status', array(
            'header' => __('ECC Status'),
            'align' => 'left',
            'index' => 'claim_status',
            'renderer' => '\Epicor\Common\Block\Adminhtml\Mapping\Claimstatus\Claimstatus',
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
                    'confirm' => __('Delete selected items?')
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

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
