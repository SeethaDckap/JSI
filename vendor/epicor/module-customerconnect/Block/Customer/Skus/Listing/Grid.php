<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Skus\Listing;

use Epicor\Common\Block\Generic\Listing\ColumnRendererReader;
use Epicor\Common\Helper\Data as CommonHelper;
use Epicor\Common\Model\GridConfigOptionsModelReader;
use Epicor\Common\Model\Message\CollectionFactory;
use Epicor\Customerconnect\Block\Generic\Listing\Search;
use Epicor\Customerconnect\Helper\Skus;
use Epicor\Customerconnect\Model\Skus\CpnuManagement;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Url\Helper\Data as FrameworkUrlHelper;

/**
 * Class Grid
 * @package Epicor\Customerconnect\Block\Customer\Skus\Listing
 */
class Grid extends Search
{
    /**
     * Access control path for export
     */
    const FRONTEND_RESOURCE_EXORT = 'Epicor_Customerconnect::customerconnect_account_skus_export';

    /**
     * @var Skus
     */
    protected $customerconnectSkusHelper;

    /**
     * @var CpnuManagement
     */
    private $cpnuManagement;

    /**
     * Grid constructor.
     * @param Context $context
     * @param Data $backendHelper
     * @param CollectionFactory $commonMessageCollectionFactory
     * @param CommonHelper $commonHelper
     * @param FrameworkUrlHelper $frameworkHelperDataHelper
     * @param GridConfigOptionsModelReader $configOptionsModelReader
     * @param ColumnRendererReader $columnRendererReader
     * @param Skus $customerconnectSkusHelper
     * @param CpnuManagement $cpnuManagement
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $commonMessageCollectionFactory,
        CommonHelper $commonHelper,
        FrameworkUrlHelper $frameworkHelperDataHelper,
        GridConfigOptionsModelReader $configOptionsModelReader,
        ColumnRendererReader $columnRendererReader,
        Skus $customerconnectSkusHelper,
        CpnuManagement $cpnuManagement,
        array $data = []
    ) {
        $this->cpnuManagement = $cpnuManagement;
        $this->customerconnectSkusHelper = $customerconnectSkusHelper;
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $configOptionsModelReader,
            $columnRendererReader,
            $data
        );

        $this->setFooterPagerVisibility(true);
        $this->setId('customerconnect_skus');
        $this->setDefaultSort('product_sku');
        $this->setDefaultDir('desc');
        $this->setIdColumn('entity_id');
        $this->setMassactionBlockName('Epicor\Customerconnect\Block\Widget\Grid\Massaction\Extended');
        $this->initColumns();
        $this->setNoFilterMassactionColumn(true);
        $this->setExportTypeCsv(array('text' => 'CSV', 'url' => '*/*/exportToCsv'));
        $this->setExportTypeXml(array('text' => 'XML', 'url' => '*/*/exportToXml'));
    }

    /**
     * Columns initialization
     */
    protected function initColumns()
    {
        $columns = array(
            'product_sku' => array(
                'index' => 'product.sku',
                'header' => 'Product SKU',
                'type' => 'text',
                'sortable' => true
            ),
            'customer_sku' => array(
                'index' => 'sku',
                'header' => 'My SKU',
                'type' => 'text',
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Skus\Renderer\CustomerSku',
                'sortable' => true,
                'filter_index' => 'main_table.sku'
            ),
            'description' => array(
                'index' => 'description',
                'header' => 'Description',
                'type' => 'text',
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Skus\Renderer\Description',
                'sortable' => true,
            )
        );

        if ($this->cpnuManagement->isEditable() &&
            $this->cpnuManagement->isAccessAllowed(CpnuManagement::FRONTEND_RESOURCE_ACCOUNT_SKU_DELETE)) {
            $columns['delete'] = array(
                'header' => __('Action'),
                'width' => '80',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => __('Delete'),
                        'url' => array('base' => '*/*/delete',
                            'params' => array('id' => $this->getRequest()->getParam('id'))
                        ),
                        'field' => 'id'
                    ),
                ),
                'filter' => false,
                'sortable' => false,
                'is_system' => true,
            );
        }

        $this->setCustomColumns($columns);
    }

    /**
     * @return \Epicor\Customerconnect\Block\Generic\Listing\Grid|void
     */
    protected function _prepareCollection()
    {
        $this->setCollection($this->customerconnectSkusHelper->getCustomerSkus());
        \Magento\Backend\Block\Widget\Grid::_prepareCollection();
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return null
     */
    public function getRowUrl($row)
    {
        return null;
    }

    /**
     * @return $this|Grid
     */
    protected function _prepareMassaction()
    {
        if (!$this->cpnuManagement->isEditable()) {
            return $this;
        }
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('entityid[]');

        if ($this->cpnuManagement->isAccessAllowed(CpnuManagement::FRONTEND_RESOURCE_ACCOUNT_SKU_DELETE)) {
            $this->getMassactionBlock()->addItem('delete', array(
                'label' => __('Delete'),
                'url' => $this->getUrl(
                    'customerconnect/massactions/massSkuDelete',
                    array('_query' => array('entity' => $this->getEntityType()))
                )
            ));
        }

        if ($this->cpnuManagement->isAccessAllowed(CpnuManagement::FRONTEND_RESOURCE_ACCOUNT_SKU_EDIT) &&
            $this->cpnuManagement->erpUpdateAllow()) {
            $this->getMassactionBlock()->addItem('update', array(
                'label' => __('Update'),
                'url' => $this->getUrl(
                    'customerconnect/massactions/massSkuUpdate',
                    array('_query' => array('entity' => $this->getEntityType()))
                )
            ));
        }

        return $this;
    }
}
