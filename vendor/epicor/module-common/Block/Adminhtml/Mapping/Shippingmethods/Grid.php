<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Shippingmethods;


class Grid extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Filter
{

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Erp\Mapping\Shippingmethod\CollectionFactory
     */
    protected $commResourceErpMappingShippingmethodCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Comm\Model\ResourceModel\Erp\Mapping\Shippingmethod\CollectionFactory $commResourceErpMappingShippingmethodCollectionFactory,

        array $data = []
    ) {
        $this->commResourceErpMappingShippingmethodCollectionFactory = $commResourceErpMappingShippingmethodCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
        $this->setId('shippingmethodsgrid');
        $this->setDefaultSort('code');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->commResourceErpMappingShippingmethodCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('shipping_method', array(
            'header' => __('Shipping Method'),
            'align'  => 'left',
            'index'  => 'shipping_method',
        ));

        $this->addColumn('shipping_method_code', array(
            'header' => __('Shipping Method Code'),
            'align'  => 'left',
            'index'  => 'shipping_method_code',
        ));

        $this->addColumn('erp_code', array(
            'header' => __('Erp Code Value'),
            'align'  => 'left',
            'index'  => 'erp_code',
        ));

        $this->addColumn('tracking_url', array(
            'header' => __('Tracking Url'),
            'align'  => 'left',
            'index'  => 'tracking_url',
        ));


        $this->addColumn('action', array(
            'header'    => __('Action'),
            'width'     => '100',
            'type'      => 'action',
            'getter'    => 'getId',
            'actions'   => array(
                array(
                    'caption' => __('Edit'),
                    'url'     => array('base' => '*/*/edit'),
                    'field'   => 'id',
                ),
                array(
                    'caption' => __('Delete'),
                    'url'     => array('base' => '*/*/delete'),
                    'field'   => 'id',
                ),
            ),
            'filter'    => false,
            'sortable'  => false,
            'index'     => 'stores',
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
