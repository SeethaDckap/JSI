<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Block\Adminhtml\Mapping\Country;

class Grid extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Filter
{

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Erp\Mapping\Country\CollectionFactory
     */
    protected $commResourceErpMappingCountryCollectionFactory;

    /**
     * @var 
     */
    protected $backendWidgetGridColumnRendererCountryFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Comm\Model\ResourceModel\Erp\Mapping\Country\CollectionFactory $commResourceErpMappingCountryCollectionFactory,
        array $data = [])
    {
        $this->commResourceErpMappingCountryCollectionFactory=$commResourceErpMappingCountryCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
        $this->setId('countrymappingGrid');
        $this->setDefaultSort('erp_code');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->commResourceErpMappingCountryCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('magento_id', array(
            'header' => __('Magento Description'),
            'align' => 'left',
            'index' => 'magento_id',
            'renderer' =>'\Magento\TaxImportExport\Block\Adminhtml\Rate\Grid\Renderer\Country' //Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Country
        ));


        $this->addColumn('magento_code', array(
            'header' => __('Magento Code'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'magento_id',
        ));


        $this->addColumn('erp_code', array(
            'header' => __('ERP Country Code'),
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
