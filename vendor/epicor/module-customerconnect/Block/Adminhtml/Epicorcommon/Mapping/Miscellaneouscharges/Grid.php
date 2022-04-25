<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Adminhtml\Epicorcommon\Mapping\Miscellaneouscharges;


class Grid extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Filter
{

    /**
     * @var \Epicor\Customerconnect\Model\ResourceModel\Erp\Mapping\Miscellaneouscharges\CollectionFactory
     */
    protected $resourceErpMappingMiscCollectionFactory;



    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Customerconnect\Model\ResourceModel\Erp\Mapping\Miscellaneouscharges\CollectionFactory $resourceErpMappingMiscCollectionFactory,
        array $data = [])
    {

        $this->resourceErpMappingMiscCollectionFactory = $resourceErpMappingMiscCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
        $this->setId('miscmappingGrid');
        $this->setDefaultSort('erp_code');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->resourceErpMappingMiscCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('erp_code', array(
            'header' => __('ERP Miscellaneous Code'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'erp_code',
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
