<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Products;


class Grid extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Filter
{

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Erp\Mapping\Products\CollectionFactory
     */
    protected $commResourceErpMappingProductsCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Comm\Model\ResourceModel\Erp\Mapping\Products\CollectionFactory $commResourceErpMappingProductsCollectionFactory,
        array $data = [])
    {
        $this->commResourceErpMappingProductsCollectionFactory = $commResourceErpMappingProductsCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
        $this->setId('productsgrid');
        $this->setDefaultSort('code');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->commResourceErpMappingProductsCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('product_sku', array(
            'header' => __('SKU'),
            'align' => 'left',
            'index' => 'product_sku',
        ));

        $this->addColumn('product_uom', array(
            'header' => __('UOM'),
            'align' => 'left',
            'index' => 'product_uom',
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
