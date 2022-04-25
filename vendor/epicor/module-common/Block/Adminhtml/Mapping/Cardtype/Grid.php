<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Cardtype;


class Grid extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Filter
{

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\Cardtype
     */
    protected $commErpMappingCardtype;


    public function __construct(
       \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
       \Epicor\Comm\Model\Erp\Mapping\Cardtype $commErpMappingCardtype,
       array $data = []
    )
    {

        $this->commErpMappingCardtype = $commErpMappingCardtype;
        parent::__construct($context, $backendHelper, $data);
        $this->setId('cardtypemappingGrid');
        $this->setDefaultSort('erp_code');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->commErpMappingCardtype->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'payment_method', array(
            'header' => __('Payment Method'),
            'align' => 'left',
            'index' => 'payment_method',
            'renderer' => '\Epicor\Common\Block\Adminhtml\Mapping\Cardtype\Renderer\Paymentmethod'
            )
        );

        $this->addColumn(
            'magento_code', array(
            'header' => __('Card Type Code'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'magento_code',
            )
        );

        $this->addColumn(
            'erp_code', array(
            'header' => __('ERP Cardtype Code'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'erp_code',
            )
        );



        $this->addColumn(
            'action', array(
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
            )
        );

        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportXml', __('XML'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
