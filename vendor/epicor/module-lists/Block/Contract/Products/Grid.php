<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Contract\Products;

/**
 * List Products Frontend Serialized Grid
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid {

    private $_selected = array();

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $catalogResourceModelProductCollectionFactory;

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $catalogProductType;

    /**
     * @var \Epicor\Lists\Helper\Messaging\Customer
     */
    protected $listsMessagingCustomerHelper;

    /**
     * @var \Epicor\Lists\Block\Contract\Renderer\SkunodelimiterFactory
     */
    protected $listsContractRendererSkunodelimiterFactory;

    /**
     * @var \Epicor\Lists\Block\Contract\Renderer\StatusFactory
     */
    protected $listsContractRendererStatusFactory;

    /**
     * @var \Epicor\Lists\Block\Adminhtml\List\Edit\Tab\Renderer\ContractquantitiesFactory
     */
    protected $listsAdminhtmlListEditTabRendererContractquantitiesFactory;
    
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $_eavAttribute;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backendHelper, \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory, \Epicor\Common\Helper\Data $commonHelper, \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper, \Magento\Checkout\Model\Cart $checkoutCart, \Epicor\Lists\Model\ListModelFactory $listsListModelFactory, \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogResourceModelProductCollectionFactory, \Epicor\Lists\Helper\Data $listsHelper, \Magento\Catalog\Model\Product\Type $catalogProductType, \Epicor\Lists\Helper\Messaging\Customer $listsMessagingCustomerHelper, \Epicor\Lists\Block\Contract\Renderer\SkunodelimiterFactory $listsContractRendererSkunodelimiterFactory, \Epicor\Lists\Block\Contract\Renderer\StatusFactory $listsContractRendererStatusFactory, \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Renderer\ContractquantitiesFactory $listsAdminhtmlListEditTabRendererContractquantitiesFactory, \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,array $data = []
    ) {
        $this->listsContractRendererSkunodelimiterFactory = $listsContractRendererSkunodelimiterFactory;
        $this->listsContractRendererStatusFactory = $listsContractRendererStatusFactory;
        $this->listsAdminhtmlListEditTabRendererContractquantitiesFactory = $listsAdminhtmlListEditTabRendererContractquantitiesFactory;
        $this->checkoutCart = $checkoutCart;
        $this->listsListModelFactory = $listsListModelFactory;
        $this->catalogResourceModelProductCollectionFactory = $catalogResourceModelProductCollectionFactory;
        $this->listsHelper = $listsHelper;
        $this->catalogProductType = $catalogProductType;
        $this->listsMessagingCustomerHelper = $listsMessagingCustomerHelper;
        $this->_eavAttribute = $eavAttribute;
        parent::__construct(
                $context, $backendHelper, $commonMessageCollectionFactory, $commonHelper, $frameworkHelperDataHelper, $data
        );
        $this->setId('productsgrid');
        $this->setDefaultSort('type');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(false);
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);

        $this->setMessageBase('epicor_common');
        $this->setIdColumn('id');

        $this->setFilterVisibility(true);
        $this->setPagerVisibility(true);
        $this->setCacheDisabled(true);
        $this->setUseAjax(true);
        $this->setTemplate('Epicor_Common::widget/grid/extended.phtml');

        //$this->setSkipGenerateContent(true);
    }

    protected function _prepareLayout() {
        $this->setChild('add_button', $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
                        ->setData(array(
                            'label' => __('Close'),
                            'onclick' => 'productSelector.closepopup()',
                            'class' => 'task'
                        ))
        );

        $urlRedirect = $this->getUrl('*/*/selectcontract', array('_current' => true, 'contract' => $this->getRequest()->getParam('contract')));
        $onClick = 'location.href=\'' . $urlRedirect . '\';';
        $quote = $this->checkoutCart->getQuote();
        /* @var $quote Epicor_Comm_Model_Quote */
        if ($quote->hasItems()) {
            $message = __('Changing Contract may remove items from the cart that are not valid for the selected Contract. Do you wish to continue?');
            $onClick = 'if(confirm(\'' . $message . '\')) { ' . $onClick . ' }';
        }


        $this->setChild('select_button', $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
                        ->setData(array(
                            'label' => __('Select List'),
                            'onclick' => $onClick,
                            'class' => 'task'
                        ))
        );
        return parent::_prepareLayout();
    }

    public function getAddButtonHtml() {
        return $this->getChildHtml('add_button');
    }

    public function getSelectButtonHtml() {
        return $this->getChildHtml('select_button');
    }

    public function getMainButtonsHtml() {
        $html = $this->getSelectButtonHtml();
        $html .= $this->getAddButtonHtml();
        $html .= parent::getMainButtonsHtml();
        return $html;
    }

    /**
     * Gets the List for this tab
     *
     * @return \Epicor\Lists\Model\ListModel
     */
    public function getList() {
        $this->list = $this->listsListModelFactory->create()->load($this->getRequest()->getParam('contract'));
        return $this->list;
    }

    /**
     * Row _LineFilter
     *
     * @param $collection, $column
     * 
     * @return null
     */
    protected function _LineFilter($collection, $column) {
        $filterroleid = $column->getFilter()->getValue();
        if (!$filterroleid) {
            return $this;
        }
        $this->getCollection()->getSelect()
                ->where("line_number like ?", "%" . $filterroleid . "%");

        return;
    }

    /**
     * Row _PartFilter
     *
     * @param $collection, $column
     * 
     * @return null
     */
    protected function _PartFilter($collection, $column) {
        $filterroleid = $column->getFilter()->getValue();
        if (!$filterroleid) {
            return $this;
        }
        $this->getCollection()->getSelect()
                ->where("part_number like ?", "%" . $filterroleid . "%");
        return;
    }

    /**
     * Build data for List Products
     *
     * @return \Epicor\Lists\Block\Adminhtml\List\Edit\Tab\Products
     */
    protected function _prepareCollection() {
        $contract = $this->getRequest()->getParam('contract');
        $visibilityAttId = $this->_eavAttribute->getIdByCode('catalog_product', 'visibility');
        $collection = $this->catalogResourceModelProductCollectionFactory->create();

        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('ecc_uom');
        $collection->addAttributeToSelect('type_id');
        $collection->addFieldToSelect('status');
        $collection->setFlag('no_product_filtering', true);
        $ids = $this->_getSelected();
        $stringIds = array_map('strval', $ids);
        $collection->addFieldToFilter('sku', array('in' => $stringIds));
        $collection->addFieldToFilter('type_id', array('neq' => 'grouped'));
        //$collection->getSelect()->join(array('visibility' => $collection->getTable('catalog_product_entity_int')), 'e.entity_id = visibility.entity_id AND visibility.attribute_id = "'.$visibilityAttId.'"', array('visibility' => 'visibility.value'));
        $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        $collection->getSelect()->join(array('list' => $collection->getTable('ecc_list_product')), 'e.sku = list.sku AND list.list_id = "' . $contract . '"', array('product_list_id' => 'list.id', 'qty'));
        $collection->getSelect()->join(array('contract' => $collection->getTable('ecc_contract_product')), 'list.id = contract.list_product_id', array('start_date', 'line_number', 'part_number', 'end_date', 'contract_product_status' => 'status', 'min_order_qty', 'max_order_qty'));
        $collection->getSelect()->group('e.entity_id');
        $this->setCollection($collection);
        return \Magento\Backend\Block\Widget\Grid\Extended::_prepareCollection();
    }

    /**
     * Build columns for List Products
     *
     * @return \Epicor\Lists\Block\Adminhtml\List\Edit\Tab\Products
     */
    protected function _prepareColumns() {
        $helper = $this->listsHelper;
        /* @var $helper Epicor_Lists_Helper_Data */

        $this->addColumn('product_list_id', array(
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display',
            'header' => __('SKU'),
        ));
//        $this->addColumn('start_date', array(           
//            'column_css_class'=> 'no-display',
//            'header_css_class'=> 'no-display',           
//        ));
//        $this->addColumn('end_date', array(           
//            'column_css_class'=> 'no-display',
//            'header_css_class'=> 'no-display',           
//        ));
        $this->addColumn('status', array(
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display',
            'header' => __('SKU'),
        ));
        $this->addColumn('visibility', array(
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display',
            'header' => __('SKU'),
        ));
        $this->addColumn(
                'sku', array(
            'header' => __('SKU'),
            'index' => 'sku',
            'filter_index' => 'sku',
            'type' => 'text',
            'sortable' => true,
            'renderer' => '\Epicor\Lists\Block\Contract\Renderer\Skunodelimiter',
                )
        );

        $this->addColumn(
                'uom', array(
            'header' => __('UOM'),
            'index' => 'ecc_uom',
            'filter_index' => 'ecc_uom',
            'type' => 'text',
            'sortable' => true,
            'filterable' => true,
                )
        );

        $this->addColumn(
                'type_id', array(
            'header' => __('Product Type'),
            'index' => 'type_id',
            'type' => 'options',
            'options' => $this->catalogProductType->getOptionArray(),
            'filter_index' => 'type_id',
            'sortable' => true,
            'filterable' => true,
                )
        );

        $this->addColumn(
                'product_name', array(
            'header' => __('Product Name'),
            'index' => 'name',
            'filter_index' => 'name',
            'type' => 'text'
                )
        );

        $this->addColumn(
                'line_number', array(
            'header' => __('Line Number'),
            'index' => 'line_number',
            'filter_index' => 'line_number',
            'type' => 'text',
            'sortable' => false,
            'filterable' => true,
            'filter_condition_callback' => array($this, '_LineFilter'),
                )
        );

        $this->addColumn(
                'part_number', array(
            'header' => __('Part Number'),
            'index' => 'part_number',
            'filter_index' => 'part_number',
            'type' => 'text',
            'sortable' => false,
            'filterable' => true,
            'filter_condition_callback' => array($this, '_PartFilter'),
                )
        );

        $this->addColumn(
                'start_date', array(
            'header' => __('Start Date'),
            'index' => 'start_date',
            'filter_index' => 'start_date',
            'type' => 'datetime',
            'sortable' => false,
            'filterable' => false,
            'filter' => false,
                )
        );

        $this->addColumn(
                'end_date', array(
            'header' => __('End Date'),
            'index' => 'end_date',
            'filter_index' => 'end_date',
            'type' => 'datetime',
            'sortable' => false,
            'filterable' => false,
            'filter' => false,
                )
        );

        $this->addColumn(
                'status_name', array(
            'header' => __('Status'),
            //    'index' => 'status_name',
            //    'filter_index' => 'status_name',
            'type' => 'options',
            'options' => $this->getStatusName(),
            'renderer' => '\Epicor\Lists\Block\Contract\Renderer\Status',
            'filter_condition_callback' => array($this, 'statusFilter'),
                )
        );

        $this->addColumn(
                'max_order_qty', array(
            'header' => __('Quanities'),
            'index' => 'max_order_qty',
            'filter_index' => 'max_order_qty',
            'type' => 'options',
            'sortable' => false,
            'filterable' => false,
            'filter' => false,
            'renderer' => '\Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Renderer\Contractquantities'
                )
        );

        return parent::_prepareColumns();
    }

    /**
     * Used in grid to return selected Products values.
     * 
     * @return array
     */
    protected function _getSelected() {
        return array_keys($this->getSelected());
    }

    /**
     * Builds the array of selected Products
     * 
     * @return array
     */
    public function getSelected() {

        $list = $this->getList();
        /* @var $list Epicor_Lists_Model_ListModel */
        foreach ($list->getProducts() as $product) {
            $this->_selected[$product->getSku()] = array('sku' => $product->getSku());
        }

        return $this->_selected;
    }

    /**
     * Sets the selected items array
     *
     * @param array $selected
     *
     * @return void
     */
    public function setSelected($selected) {
        if (!empty($selected)) {
            foreach ($selected as $sku) {
                $this->_selected[$sku] = array('sku' => $sku);
            }
        }
    }

    /**
     * Gets grid url for ajax reloading
     *
     * @return string
     */
    public function getGridUrl() {
        return $this->getUrl('*/*/productsgrid', array('_current' => true));
    }

    /**
     * Row Click URL
     * 
     * @return null
     */
    public function getRowUrl($row)
    {
        return null;
    }
    
    /**
     * Row _uomFilter
     *
     * @param $collection, $column
     * 
     * @return null
     */
    public function _uomFilter($collection, $column) {
        $value = $column->getFilter()->getValue();

        /* @var $delimiter epicor_lists_helper_messaging_customer */
        $delimiter = $this->listsMessagingCustomerHelper->getUOMSeparator();
        // if unable to get a value of the column don't attempt filter 
        if (!$value) {
            return $this;
        }
        $this->getCollection()->getSelect()
                ->where("e.sku like ?", "%" . $delimiter . $value . "%");

        $collection->getSelect()->order('sku');
    }

    public function getStatusName() {
        $statusName = array('Active' => 'Active', 'Inactive' => 'Inactive', 'Expired' => 'Expired', 'Pending' => 'Pending', 'Not available on this Store' => 'Not available on this Store');

        return $statusName;
    }

    public function statusFilter($collection, $column) {

        if (!$value = $column->getFilter()->getValue()) {               // if unable to get a value of the column don't attempt filter  
            return $this;
        }
        $nowTime = date('Y-m-d H:i:s', time());
        switch ($value) {
            case 'Not available on this Store':
//                may need to update this, as not really checking whether product has a parent, only that it is available and visibility is 'not visible individually'                  
//                $collection->getSelect()->joinLeft(array('link_table' => 'catalog_product_super_link'),
//                    'link_table.product_id = e.entity_id',
//                    array('product_id')
//                );
//                $collection->getSelect()->where('link_table.product_id IS NOT NULL');
                $collection->addFieldToFilter('status', array('eq' => 1));
                $collection->getSelect()->where("visibility.value = 1");
                break;
            case 'Inactive':
                $collection->getSelect()->where("contract.status = 0 OR contract.status IS NULL");
                break;
            case 'Pending':
                $collection->getSelect()->where("contract.start_date IS NOT null AND contract.start_date > ?", $nowTime);
                break;
            case 'Expired':
                $collection->getSelect()->where("contract.end_date IS NOT null AND contract.end_date < ?", $nowTime);
                break;
            case 'Active':
                $collection->getSelect()->where("visibility.value <> 1");
                $collection->getSelect()->where("contract.status = 1");
                $collection->getSelect()->where("contract.start_date IS NOT null AND contract.start_date <= ?", $nowTime);
                $collection->getSelect()->where("contract.end_date IS NOT null AND contract.end_date >= ?", $nowTime);
                break;
            default:
                break;
        }
        return $this;
    }

}
