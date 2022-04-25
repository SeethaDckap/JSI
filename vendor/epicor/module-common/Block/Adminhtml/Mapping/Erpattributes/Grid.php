<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Erpattributes;


/*
 * epicor_comm_erp_mapping attributes grid
 */

class Grid extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Filter
{
    /*
     * construct for epicor_comm_erp_mapping attributes grid
     */

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Erp\Mapping\Attributes\CollectionFactory
     */
    protected $commResourceErpMappingAttributesCollectionFactory;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $configConfigSourceYesno;

    /**
     * @var \Epicor\Comm\Block\Adminhtml\Mapping\Erpattributes\Renderer\InputtypeFactory
     */
    protected $commAdminhtmlMappingErpattributesRendererInputtypeFactory;
    /**
     * @var \Epicor\Comm\Model\Config\Source\Filterable
     */
    private $commConfigSourceFilterable;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Comm\Model\ResourceModel\Erp\Mapping\Attributes\CollectionFactory $commResourceErpMappingAttributesCollectionFactory,
        \Magento\Config\Model\Config\Source\Yesno $configConfigSourceYesno,
        \Epicor\Comm\Block\Adminhtml\Mapping\Erpattributes\Renderer\InputtypeFactory $commAdminhtmlMappingErpattributesRendererInputtypeFactory,
        \Epicor\Comm\Model\Config\Source\Filterable $commConfigSourceFilterable,
        array $data = []
    )
    {
        $this->commAdminhtmlMappingErpattributesRendererInputtypeFactory = $commAdminhtmlMappingErpattributesRendererInputtypeFactory;
        $this->commResourceErpMappingAttributesCollectionFactory = $commResourceErpMappingAttributesCollectionFactory;
        $this->configConfigSourceYesno = $configConfigSourceYesno;
        $this->commConfigSourceFilterable = $commConfigSourceFilterable;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('erpattributesmappingGrid');
        $this->setDefaultSort('erp_code');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /*
     * Setup collection for epicor_comm_erp_mapping attributes grid
     */

    protected function _prepareCollection()
    {
        $collection = $this->commResourceErpMappingAttributesCollectionFactory->create();
        $this->setCollection($collection);
        return $this;
    }

    /*
     * Setup columns for epicor_comm_erp_mapping attributes grid
     */

    protected function _prepareColumns()
    {
        $yesno = $this->configConfigSourceYesno->toArray();
        $this->addColumn(
            'attribute_code', array(
            'header' => __('Attribute Code'),
            'align' => 'left',
            'index' => 'attribute_code',
            )
        );
        $this->addColumn(
            'input_type', array(
            'header' => __('Input Type'),
            'align' => 'left',
            'index' => 'input_type',
            'renderer' => '\Epicor\Comm\Block\Adminhtml\Mapping\Erpattributes\Renderer\Inputtype'
            )
        );
        $this->addColumn(
            'is_visible_in_advanced_search', array(
                'header' => __('Visible in Advanced Search'),
                'align' => 'left',
                'index' => 'is_visible_in_advanced_search',
                'type' => 'options',
                'options' => $yesno,
            )
        );
        $this->addColumn(
            'position', array(
                'header' => __('Position'),
                'align' => 'left',
                'index' => 'position',
                'type' => 'text',
                'options' => $yesno,
            )
        );
        $this->addColumn(
            'is_visible_in_advanced_search', array(
                'header' => __('Advanced Search'),
                'align' => 'left',
                'index' => 'is_visible_in_advanced_search',
                'type' => 'options',
                'options' => $yesno,
            )
        );

        $this->addColumn(
            'is_searchable', array(
                'header' => __('Use In Search'),
                'align' => 'left',
                'index' => 'is_searchable',
                'type' => 'options',
                'options' => $yesno,
            )
        );
        $this->addColumn(
            'is_comparable', array(
                'header' => __('Comparable on Storefront'),
                'align' => 'left',
                'index' => 'is_comparable',
                'type' => 'options',
                'options' => $yesno,
            )
        );
        $this->addColumn(
            'is_filterable', array(
                'header' => __('Use in Layered Navigation'),
                'align' => 'left',
                'type' => 'options',
                'index' => 'is_filterable',
                'renderer' =>  '\Epicor\Comm\Block\Adminhtml\Mapping\Erpattributes\Renderer\Useinlayerednavigation',
                'options' => $this->commConfigSourceFilterable->toArray(),
            )
        );


        $this->addColumn(
            'is_filterable_in_search', array(
                'header' => __('Use in Search Results Layered Navigation'),
                'align' => 'left',
                'index' => 'is_filterable_in_search',
                'type' => 'options',
                'options' => $yesno,
            )
        );
        $this->addColumn(
            'is_used_for_promo_rules', array(
                'header' => __('Use for Promo Rule Conditions'),
                'align' => 'left',
                'index' => 'is_used_for_promo_rules',
                'type' => 'options',
                'options' => $yesno,
            )
        );
        $this->addColumn(
            'is_html_allowed_on_front', array(
                'header' => __('Allow HTML Tags on Storefront'),
                'align' => 'left',
                'index' => 'is_html_allowed_on_front',
                'type' => 'options',
                'options' => $yesno,
            )
        );
        $this->addColumn(
            'is_visible_on_front', array(
                'header' => __('Visible on Catalog Pages on Storefront'),
                'align' => 'left',
                'index' => 'is_visible_on_front',
                'type' => 'options',
                'options' => $yesno,
            )
        );
        $this->addColumn(
            'used_in_product_listing', array(
                'header' => __('Used in Product Listing'),
                'align' => 'left',
                'index' => 'used_in_product_listing',
                'type' => 'options',
                'options' => $yesno,
            )
        );
        $this->addColumn(
            'used_for_sort_by', array(
                'header' => __('Used for Sorting in Product Listing'),
                'align' => 'left',
                'index' => 'used_for_sort_by',
                'type' => 'options',
                'options' => $yesno,
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

    /*
     * Allow click on row to be editable for epicor_comm_erp_mapping attributes grid
     */

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
