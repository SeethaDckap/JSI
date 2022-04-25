<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Locations\Edit\Tab;


class Products extends \Magento\Backend\Block\Widget\Grid\Extended
{

    private $_selected = array();

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $catalogResourceModelProductCollectionFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\Product\CollectionFactory
     */
    protected $commResourceLocationProductCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogResourceModelProductCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Epicor\Comm\Model\ResourceModel\Location\Product\CollectionFactory $commResourceLocationProductCollectionFactory,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->catalogResourceModelProductCollectionFactory = $catalogResourceModelProductCollectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->commResourceLocationProductCollectionFactory = $commResourceLocationProductCollectionFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('productGrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);

        $this->setDefaultSort('sku');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setFilterVisibility(true);

        $this->setDefaultFilter(array('selected_product' => 1));
    }

    public function canShowTab()
    {
        return true;
    }

    public function getTabLabel()
    {
        return 'Products';
    }

    public function getTabTitle()
    {
        return 'Products';
    }

    public function isHidden()
    {
        return false;
    }

    /**
     *
     * @return \Epicor\Comm\Model\Location
     */
    public function getLocation()
    {
//        if (!$this->_location) {
        $this->_location = $this->registry->registry('location');
//        }
        return $this->_location;
    }

    /**
     *
     * @return type
     */
    protected function _prepareCollection()
    {
//        $collection = Mage::getModel('epicor_comm/location_product')->getCollection();
//        /* @var $collection Epicor_Comm_Model_Resource_Location_Product_Collection */
//
        $locationCode = $this->getLocation()->getCode();
//
//        $collection->addFieldToFilter('main_table.location_code', $location->getCode());
//        $collection->joinExtraProductInfo();

        $collection = $this->catalogResourceModelProductCollectionFactory->create();
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */

        $collection->addAttributeToSelect('name');

        $table = $collection->getTable('ecc_location_product');
        //M1 > M2 Translation Begin (Rule 39)
        // $locationCode = $this->resourceConnection->getConnection('default_write')->quote($locationCode);
        $locationCode = $this->resourceConnection->getConnection()->quote($locationCode);
        //M1 > M2 Translation End


        $collection->getSelect()->joinLeft(array('loc' => $table), 'loc.product_id=e.entity_id AND loc.location_code=' . $locationCode . '', array('*'), null, 'left');
        $collection->getSelect()->group('e.entity_id');
        $collection->addAttributeToSelect('uom');
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {

        $this->addColumn('selected_product', array(
            'header' => __('Select'),
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'selected_product',
            'values' => $this->_getSelected(),
            'filter_condition_callback' => array($this, '_filterSelectedCondition'),
            'align' => 'center',
            'index' => 'entity_id',
            'sortable' => false,
            'field_name' => 'links[]',
            'use_index' => true
        ));

        $this->addColumn('row_id', array(
            'header' => __('Position'),
            'name' => 'row_id',
            'type' => 'number',
            'validate_class' => 'validate-number',
            'index' => 'entity_id',
            'width' => 0,
            'editable' => true,
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display'
        ));

        $this->addColumn('sku', array(
            'header' => __('SKU'),
            'align' => 'left',
            'index' => 'sku',
            'filter_index' => 'sku',
            'column_css_class' => 'col-sku',
            'width' => '200px',
        ));

        $this->addColumn('uom', array(
            'header' => __('UOM'),
            'index' => 'uom',
            'filter_index' => 'uom',
            'type' => 'text',
            'sortable' => true,
            'filterable' => true,
        ));

        $this->addColumn('product_name', array(
            'header' => __('Product Name'),
            'align' => 'left',
            'index' => 'name',
            'column_css_class' => 'col-product_name',
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        $params = array(
            'id' => $this->getLocation()->getId(),
            '_current' => true,
        );
        return $this->getUrl('adminhtml/epicorcomm_locations/productsgrid', $params);
    }

    public function getRowUrl($row)
    {
        return null;
    }

    protected function _getSelected()
    {   // Used in grid to return selected customers values.
        return array_keys($this->getSelected());
    }

    public function getSelected()
    {
        if (empty($this->_selected) && $this->getRequest()->getParam('ajax') !== 'true') {
            $collection = $this->commResourceLocationProductCollectionFactory->create();
            /* @var $collection Epicor_Comm_Model_Resource_Customer_Erpaccount_Collection */

            $location = $this->getLocation();

            $collection->addFieldToFilter('location_code', $location->getCode());

            foreach ($collection->getItems() as $location_product) {
                $this->_selected[$location_product->getProductId()] = array('entity_id' => $location_product->getProductId());
            }
        }
        return $this->_selected;
    }

    public function setSelected($selected)
    {
        if (!empty($selected)) {
            foreach ($selected as $id) {
                $this->_selected[$id] = array('entity_id' => $id);
            }
        }
    }

    /**
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param \Magento\Framework\DataObject $column
     */
    protected function _filterSelectedCondition($collection, $column)
    {
        if ($column->getFilter()->getValue() === null) {
            return;
        }
        $ids = $this->_getSelected();
        if (empty($ids)) {
            $ids[] = 0;
        }
        if ($column->getFilter()->getValue()) {
            $this->getCollection()->addFieldToFilter('entity_id', array('in' => $ids));
        } else {
            $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $ids));
        }
    }

}
