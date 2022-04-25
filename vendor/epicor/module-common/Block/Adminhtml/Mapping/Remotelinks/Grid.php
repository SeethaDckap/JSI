<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Remotelinks;


//class Epicor_Comm_Block_Adminhtml_Mapping_Remotelinks_Grid extends Epicor_Common_Block_Adminhtml_Mapping_DefaultBlock_Filter
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Erp\Mapping\Remotelinks\CollectionFactory
     */
    protected $commResourceErpMappingRemotelinksCollectionFactory;

    /**
     * @var \Epicor\Common\Block\Adminhtml\Mapping\Renderer\RemotelinksFactory
     */
    protected $commAdminhtmlMappingRendererRemotelinksFactory;

   public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Comm\Model\ResourceModel\Erp\Mapping\Remotelinks\CollectionFactory $commResourceErpMappingRemotelinksCollectionFactory,
        \Epicor\Common\Block\Adminhtml\Mapping\Renderer\RemotelinksFactory $commAdminhtmlMappingRendererRemotelinksFactory,
        array $data = [])
    {
        $this->commAdminhtmlMappingRendererRemotelinksFactory = $commAdminhtmlMappingRendererRemotelinksFactory;
        $this->commResourceErpMappingRemotelinksCollectionFactory = $commResourceErpMappingRemotelinksCollectionFactory;

        parent::__construct($context, $backendHelper, $data);
        $this->setId('remotelinksmappingGrid');
        $this->setDefaultSort('pattern_code');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->commResourceErpMappingRemotelinksCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('pattern_code', array(
            'header' => __('Pattern Code'),
            'align' => 'left',
            'index' => 'pattern_code',
            'width' => '200px',
            'align' => 'left',
        ));


        $this->addColumn('name', array(
            'header' => __('Name'),
            'align' => 'right',
            'width' => '100px',
            'index' => 'name',
            'align' => 'left',
        ));

        $this->addColumn('url_pattern', array(
            'header' => __('Url Pattern'),
            'align' => 'right',
            'width' => '400px',
            'index' => 'url_pattern',
            'align' => 'left',
        ));
        $this->addColumn('http_authorization', array(
            'header' => __('HTTP Authorization'),
            'align' => 'right',
            'width' => '200px',
            'renderer' => '\Epicor\Common\Block\Adminhtml\Mapping\Renderer\Remotelinks',
            'index' => 'http_authorization',
            'align' => 'left',
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
