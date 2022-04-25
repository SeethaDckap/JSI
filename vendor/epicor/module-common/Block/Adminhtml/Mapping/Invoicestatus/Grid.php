<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Invoicestatus;


class Grid extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Filter
{

    /**
     * @var \Epicor\Customerconnect\Model\ResourceModel\Erp\Mapping\Invoicestatus\CollectionFactory
     */
    protected $customerconnectResourceErpMappingInvoicestatusCollectionFactory;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Customerconnect\Model\ResourceModel\Erp\Mapping\Invoicestatus\CollectionFactory $customerconnectResourceErpMappingInvoicestatusCollectionFactory,

        array $data = [])
    {
        $this->customerconnectResourceErpMappingInvoicestatusCollectionFactory = $customerconnectResourceErpMappingInvoicestatusCollectionFactory;

        parent::__construct($context, $backendHelper, $data);
        $this->setId('invoicestatusmappingGrid');
        $this->setDefaultSort('code');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->customerconnectResourceErpMappingInvoicestatusCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('code', array(
            'header' => __('Invoice Status Code'),
            'align' => 'left',
            'index' => 'code',
            'width' => '50px'
        ));
//
//        $this->addColumn('state', array(
//            'header' => Mage::helper('customerconnect')->__('Invoice State'),
//            'align' => 'left',
//            'index' => 'state',
//            'width' => '50px'
//        ));
        $this->addColumn('status', array(
            'header' => __('Invoice Status'),
            'align' => 'left',
            'index' => 'status',
            'width' => '50px'
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
