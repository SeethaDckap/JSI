<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Orderstatus;


class Grid extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Filter
{

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\Orderstatus
     */
    protected $commErpMappingOrderstatus;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Comm\Model\Erp\Mapping\Orderstatus $commErpMappingOrderstatus,
        array $data = []
    )
    {

        $this->commErpMappingOrderstatus = $commErpMappingOrderstatus;

        parent::__construct($context, $backendHelper, $data);
        $this->setId('orderstatusmappingGrid');
        $this->setDefaultSort('code');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        //     $collection = Mage::getModel('epicor_comm/erp_mapping_orderstatus');
        $collection = $this->commErpMappingOrderstatus->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('code', array(
            'header' => __('Erp Code'),
            'align' => 'left',
            'index' => 'code',
            'width' => '50px'
        ));

//      $this->addColumn('description', array(
//          'header'    => Mage::helper('epicor_comm')->__('Description'),
//          'align'     => 'left',
//          'index'     => 'description',
//      ));
//        $this->addColumn('state', array(
//            'header' => Mage::helper('epicor_comm')->__('Order State'),
//            'align' => 'left',
//            'index' => 'state',
//            'width' => '50px',
//            'column_css_class'=> 'no-display',
//            'header_css_class'=> 'no-display',
//        ));

        $this->addColumn('status', array(
            'header' => __('Order Status'),
            'align' => 'left',
            'index' => 'status',
            //'renderer' => $this->commAdminhtmlMappingOrderstatusRendererStatusFactory->create(),
            'width' => '50px',
        ));
        $this->addColumn('sou_trigger', array(
            'header' => __('Sou Trigger'),
            'align' => 'left',
            'index' => 'sou_trigger',
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
