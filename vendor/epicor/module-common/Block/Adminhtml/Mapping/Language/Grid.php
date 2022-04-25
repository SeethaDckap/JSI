<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Language;


class Grid extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Filter
{

    /**
     * @var \Epicor\Common\Model\ResourceModel\Erp\Mapping\Language\CollectionFactory
     */
    protected $commonResourceErpMappingLanguageCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\ResourceModel\Erp\Mapping\Language\CollectionFactory $commonResourceErpMappingLanguageCollectionFactory,
        array $data = [])
    {
        $this->commonResourceErpMappingLanguageCollectionFactory = $commonResourceErpMappingLanguageCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
        $this->setId('languagemappingGrid');
        $this->setDefaultSort('erp_code');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->commonResourceErpMappingLanguageCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {


        $this->addColumn('erp_code', array(
            'header' => __('ERP Language Code'),
            'align' => 'left',
            'width' => '50px',
            'index' => 'erp_code',
        ));

        $this->addColumn('languages', array(
            'header' => __('Languages'),
            'align' => 'left',
//          'width'     => '50px',
            'index' => 'languages',
        ));

        $this->addColumn('language_codes', array(
            'header' => __('Language Codes'),
            'align' => 'left',
            'width' => '150px',
            'index' => 'language_codes',
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
