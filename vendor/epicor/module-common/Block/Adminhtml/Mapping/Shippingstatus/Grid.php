<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Block\Adminhtml\Mapping\Shippingstatus;

class Grid extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Filter {

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Erp\Mapping\Shippingmethod\CollectionFactory
     */
    protected $commResourceErpMappingShippingstatusCollectionFactory;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backendHelper, \Epicor\Comm\Model\ResourceModel\Erp\Mapping\Shippingstatus\CollectionFactory $commResourceErpMappingShippingstatusCollectionFactory, array $data = []) {
        $this->commResourceErpMappingShippingstatusCollectionFactory = $commResourceErpMappingShippingstatusCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
        $this->setId('shippingstatussgrid');
        $this->setDefaultSort('shipping_status_code');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = $this->commResourceErpMappingShippingstatusCollectionFactory->create();
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
        $this->addColumn('shipping_status_code', array(
            'header' => __('Ship Status Code'),
            'align' => 'left',
            'index' => 'shipping_status_code',
        ));
        $this->addColumn('description', array(
            'header' => __('Ship Status Description'),
            'align' => 'left',
            'index' => 'description',
        ));
        $this->addColumn('status_help', array(
            'header' => __('Status Help'),
            'align' => 'left',
            'index' => 'status_help',
            'renderer' => '\Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Textarea',
        ));
        $this->addColumn('is_default', array(
            'header' => __('Default'),
            'align' => 'center',
            'index' => 'is_default',
            'type' => 'checkbox',
            'filter' => false,
            'renderer' => '\Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Checkbox',
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
        //return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
