<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Catalog\Product\Edit\Tab\Locations;

use Magento\Customer\Controller\RegistryConstants;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    private $_selected = array();

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;
    
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\Product\CollectionFactory
     */
    protected $commResourceLocationProductCollectionFactory;

    /**
     * @var \Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer\Product\Locations\LeadtimeFactory
     */
    protected $commAdminhtmlWidgetGridColumnRendererProductLocationsLeadtimeFactory;

    /**
     * @var \Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer\Product\Locations\ManufacturersFactory
     */
    protected $commAdminhtmlWidgetGridColumnRendererProductLocationsManufacturersFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Epicor\Comm\Model\ResourceModel\Location\Product\CollectionFactory $commResourceLocationProductCollectionFactory,
        \Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer\Product\Locations\LeadtimeFactory $commAdminhtmlWidgetGridColumnRendererProductLocationsLeadtimeFactory,
        \Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer\Product\Locations\ManufacturersFactory $commAdminhtmlWidgetGridColumnRendererProductLocationsManufacturersFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    )
    {
         $this->commAdminhtmlWidgetGridColumnRendererProductLocationsLeadtimeFactory = $commAdminhtmlWidgetGridColumnRendererProductLocationsLeadtimeFactory;
        $this->commAdminhtmlWidgetGridColumnRendererProductLocationsManufacturersFactory = $commAdminhtmlWidgetGridColumnRendererProductLocationsManufacturersFactory;
        $this->storeManager = $context->getStoreManager();
        $this->registry = $registry;
        $this->_jsonEncoder = $jsonEncoder;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->commResourceLocationProductCollectionFactory = $commResourceLocationProductCollectionFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
    }
    /**
     * @return void
     */
    protected function _construct()
    {
      
        $this->setId('locationslistGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('locations_filter');
      //  $this->setRowInitCallback('locationslistGridJsObject.rowInit.bind(productLocations)');
        parent::_construct();
    }

    protected function _prepareLayout()
    {
        $this->setChild('add_button', $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
                ->setData(array(
                    'label' => __('Add'),
                    'onclick' => 'productLocations.add()',
                    'class' => 'task'
                ))
        );
        return parent::_prepareLayout();
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    public function getMainButtonsHtml()
    {
        $html = $this->getAddButtonHtml();
        $html .= parent::getMainButtonsHtml();
        return $html;
    }

    
    
     protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return $this->storeManager->getStore($storeId);
    }

    protected function _getProduct()
    {
        
        if (!$this->registry->registry('current_product')) {
            $this->registry->register('current_product', $this->catalogProductFactory->create()->load($this->getRequest()->getParam('id')));
        }
        return $this->registry->registry('current_product');
    }

    protected function _prepareCollection()
    {
        $id = $this->_getProduct()->getId();
        $collection = $this->commResourceLocationProductCollectionFactory->create();


        /* @var $collection Epicor_Comm_Model_Resource_Location_Product_Collection */
        $collection->addFieldToFilter('main_table.product_id', $id);
        $collection->joinLocationInfo();
        $collection->joinExtraProductInfo($this->_getStore()->getId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('id', array(
            'header' => __('Id'),
            'align' => 'center',
            'index' => 'id',
            'type'      => 'radio',
            'html_name' => 'products_location',
            'column_css_class'=>'no-display',
            'header_css_class'=>'no-display',
            
        ));
        
        
        $this->addColumn('rowdata', array(
            'header' => __('Row'),
            'align' => 'left',
            'name' => 'rowdata',
            'width' => 0,
            'renderer' => '\Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Rowdata',
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display',
        ));
        
        $this->addColumn('location_code', array(
            'header' => __('Location Code'),
            'align' => 'center',
            'index' => 'location_code',
            'filter_index' => 'main_table.location_code',
            'column_css_class' => 'col-location_code',
        ));

        $this->addColumn('company', array(
            'header' => __('Company'),
            'align' => 'center',
            'index' => 'company',
            'filter_index' => 'location_info.company',
            'column_css_class' => 'col-company',
        ));

        $this->addColumn('stock_status', array(
            'header' => __('Stock Status'),
            'align' => 'left',
            'index' => 'stock_status',
            'filter_index' => 'main_table.stock_status',
            'column_css_class' => 'col-stock_status',
        ));

        $this->addColumn('free_stock', array(
            'header' => __('Free Stock'),
            'align' => 'left',
            'index' => 'free_stock',
            'filter_index' => 'main_table.free_stock',
            'type' => 'number',
            'column_css_class' => 'col-free_stock',
        ));

        $this->addColumn('minimum_order_qty', array(
            'header' => __('Min. Order Qty'),
            'align' => 'left',
            'index' => 'minimum_order_qty',
            'filter_index' => 'main_table.minimum_order_qty',
            'type' => 'number',
            'column_css_class' => 'col-minimum_order_qty',
        ));

        $this->addColumn('maximum_order_qty', array(
            'header' => __('Max. Order Qty'),
            'align' => 'left',
            'index' => 'maximum_order_qty',
            'filter_index' => 'main_table.maximum_order_qty',
            'type' => 'number',
            'column_css_class' => 'col-maximum_order_qty',
        ));

        $this->addColumn('base_price', array(
            'header' => __('Price'),
            'align' => 'left',
            'index' => 'base_price',
            'type' => 'currency',
            'currency' => 'currency_code',
            'filter_condition_callback' => array($this, '_filterPriceCondition'),
            'column_css_class' => 'col-base_price',
        ));

        $this->addColumn('lead_time', array(
            'header' => __('Lead Time'),
            'align' => 'left',
            'index' => 'lead_time_days',
            'filter_index' => 'main_table.lead_time_days',
            'renderer' => '\Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer\Product\Locations\Leadtime',
        ));

        $this->addColumn('supplier_brand', array(
            'header' => __('Supplier Brand'),
            'align' => 'left',
            'index' => 'supplier_brand',
            'filter_index' => 'main_table.supplier_brand',
            'column_css_class' => 'col-supplier_brand',
        ));

        $this->addColumn('tax_code', array(
            'header' => __('Tax Code'),
            'align' => 'left',
            'index' => 'tax_code',
            'filter_index' => 'main_table.tax_code',
            'column_css_class' => 'col-tax_code',
        ));


        $this->addColumn('manufacturers', array(
            'header' => __('Manufacturers'),
            'align' => 'left',
            'index' => 'manufacturers',
            'filter_index' => 'main_table.manufacturers',
            'renderer' => '\Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer\Product\Locations\Manufacturers',
            'column_css_class' => 'col-manufacturers',
        ));
        $this->addColumn('sort_order', array(
            'header' => __('Sort Order'),
            'align' => 'left',
            'index' => 'sort_order',
            'filter_index' => 'main_table.sort_order',
            'column_css_class' => 'col-sort_order',
        ));

        $this->addColumn('actions', array(
            'header' => __('Actions'),
            'width' => '100',
            'renderer' => '\Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Action',
            'links' => 'true',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => __('Edit'),
                    'url' => array('base' => 'adminhtml/epicorcomm_catalog_product/editlocation'),
                    'field' => 'id',
                ),
                array(
                    'caption' => __('Delete'),
                    'url' => array(
                        'base' => 'adminhtml/epicorcomm_catalog_product/deletelocation',
                        'params'=>array('productId'=>$this->_getProduct()->getId())
                        ),
                    'field' => 'id',
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'id',
            'is_system' => true,
        ));

 
        return parent::_prepareColumns();
    }

    /**
     * 
     * @param \Epicor\Comm\Model\ResourceModel\Location\Product\Collection $collection
     * @param type $column
     * @return type
     */
    public function _filterPriceCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        if (array_key_exists('from', $value)) {
            $this->getCollection()->getSelect()->where(// main sql call
                "COALESCE(`store_location_product_info`.`base_price`,
            `store_location_product_info`.`base_price`) >= ?", $value['from']);
        }
        if (array_key_exists('to', $value)) {
            $this->getCollection()->getSelect()->where(// main sql call
                "COALESCE(`store_location_product_info`.`base_price`,
            `store_location_product_info`.`base_price`) <= ?", $value['to']);
        }
    }

    
   public function getRowUrl($row)
    {
        return "javascript:window.productLocations.rowEdit(this, " . $row->getId() . ");";
    }
    
      public function getGridUrl()
    {
        $params = array(
            'id' => $this->_getProduct()->getId(),
            '_current' => true,
            'ajax' => true
        );
        return $this->getUrl('adminhtml/epicorcomm_catalog_product/locationsgrid', $params);
    }

//    public function getTabUrl()
//    {
//        return $this->getUrl('adminhtml/epicorcomm_catalog_product/locations', array('_current' => true));
//    }

   

}
