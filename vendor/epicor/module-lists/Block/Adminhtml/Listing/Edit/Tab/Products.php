<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab;


/**
 * List Products Serialized Grid
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Products extends \Magento\Backend\Block\Widget\Grid\Extended
//   implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    const CUSTOM_FILTER_FIELDS = ['selected_products', 'list_position'];

    private $_selected = array();

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    /**
     * @var \Epicor\Common\Helper\Locale\Format\Currency
     */
    protected $commonLocaleFormatCurrencyHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $catalogResourceModelProductCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $catalogProductType;

    /**
     * @var \Epicor\Lists\Helper\Messaging\Customer
     */
    protected $listsMessagingCustomerHelper;

    /**
     * @var \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Renderer\SkunodelimiterFactory
     */
    protected $listsAdminhtmlListingEditTabRendererSkunodelimiterFactory;

    /**
     * @var \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Renderer\ContractquantitiesFactory
     */
    protected $listsAdminhtmlListingEditTabRendererContractquantitiesFactory;
    /**
     * @var \Epicor\QuickOrderPad\Model\ColumnSort
     */
    private $columnSort;
    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListProductPosition
     */
    private $listProductPosition;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Lists\Helper\Data $listsHelper,
        \Epicor\Common\Helper\Locale\Format\Currency $commonLocaleFormatCurrencyHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Epicor\Lists\Model\ResourceModel\Product\CollectionFactory $catalogResourceModelProductCollectionFactory,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Epicor\Lists\Helper\Messaging\Customer $listsMessagingCustomerHelper,
        \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Renderer\SkunodelimiterFactory $listsAdminhtmlListingEditTabRendererSkunodelimiterFactory,
        \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Renderer\ContractquantitiesFactory $listsAdminhtmlListingEditTabRendererContractquantitiesFactory,
        array $data = [],
        \Epicor\QuickOrderPad\Model\ColumnSort $columnSort = null,
        \Epicor\Lists\Model\ResourceModel\ListProductPosition $listProductPosition = null
    ) {
        $this->listsAdminhtmlListingEditTabRendererSkunodelimiterFactory = $listsAdminhtmlListingEditTabRendererSkunodelimiterFactory;
        $this->listsAdminhtmlListingEditTabRendererContractquantitiesFactory = $listsAdminhtmlListingEditTabRendererContractquantitiesFactory;
        $this->listsHelper = $listsHelper;
        $this->commonLocaleFormatCurrencyHelper = $commonLocaleFormatCurrencyHelper;
        $this->registry = $registry;
        $this->listsListModelFactory = $listsListModelFactory;
        $this->catalogResourceModelProductCollectionFactory = $catalogResourceModelProductCollectionFactory;
        $this->catalogProductType = $catalogProductType;
        $this->listsMessagingCustomerHelper = $listsMessagingCustomerHelper;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->columnSort = $columnSort;
        $this->listProductPosition = $listProductPosition;
        $this->setGridDefaultOrder();
    }


    public function _construct()
    {
        parent::_construct();

        $currencyHelper = $this->commonLocaleFormatCurrencyHelper;
        /* @var $currencyHelper Epicor_Common_Helper_Locale_Format_Currency */

        $this->setId('productsGrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);

        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setCacheDisabled(true);
        $this->setFilterVisibility(true);
        $this->setDefaultFilter(array('selected_products' => 1));
        $this->setAdditionalJavaScript("initListProduct({
                table: 'productsGrid',
                listId: '" . $this->getList()->getId() . "',
                jsonPricing: 'json_pricing',
                translations: {
                    'Currency': '" . htmlentities(__('Currency')) . "',
                    'Price': '" . htmlentities(__('Price')) . "',
                    'Breaks': '" . htmlentities(__('Breaks')) . "',
                    'Value Breaks': '" . htmlentities(__('Value Breaks')) . "',
                    'Qty': '" . htmlentities(__('Qty')) . "',
                    'Value': '" . htmlentities(__('Value')) . "',
                    'Description': '" . htmlentities(__('Description')) . "',
                    'Select': '" . htmlentities(__('Select')) . "',
                    'Add': '" . htmlentities(__('Add')) . "',
                    'Delete': '" . htmlentities(__('Delete')) . "',
                    'Clone': '" . htmlentities(__('Clone')) . "',
                    'No records found.': '" . htmlentities(__('No records found.')) . "',
                    'Please choose a file.': '" . htmlentities(__('Please choose a file.')) . "',
                },
                url: '" . $this->getUrl('epicor_lists/epicorlists_lists/productpricing') . "',
                importUrl: '" . $this->getUrl('epicor_lists/epicorlists_lists/productsimportpost', array('id' => $this->getList()->getId())) . "',
                csvDowloadUrl: '" . $this->getUrl('epicor_lists/epicorlists_lists/productimportcsv') . "',
                currencies: " . json_encode($currencyHelper->getAllowedCurrencies()) . ",
                pricingIsEditable: " . ($this->getList()->getTypeInstance()->isSectionEditable('pricing') ? 'true' : 'false') . ",
            });");
    }

    private function setGridDefaultOrder()
    {
        $listId = $this->getList()->getId();

        if ($this->listProductPosition
            && $this->listProductPosition->isListPositionOrderSet($listId)
            && $this->columnSort->isPositionListOrder()) {
            $this->setDefaultSort('list_position');
        } else {
            $this->setDefaultSort('sku');
        }
    }


    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMainButtonsHtml()
    {
       $html = parent::getMainButtonsHtml();

        if ($this->getFilterVisibility()) {
            $html .= $this->getAutoNumberButtonHtml();
        }
        return $html;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAutoNumberButtonHtml()
    {
        return $this->getLayout()
            ->createBlock(\Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Products\AutoNumber::class)
            ->setTemplate('Epicor_Lists::epicor/lists/product/autonumber.phtml')
            ->toHtml();
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($this->isCustomFilter($column)) {
            $this->setCustomerFilters($column);
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * @param $column
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function filterSelectedProducts($column)
    {
        $ids = $this->_getSelected();
        if (!empty($ids)) {
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('sku', array('in' => $ids));
            } else if ($ids) {
                $this->getCollection()->addFieldToFilter('sku', array('nin' => $ids));
            }
        } else {
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('sku', array('in' => ''));
            } else {
                $this->getCollection()->addFieldToFilter('sku', array('nin' => ''));
            }
        }
    }

    /**
     * @param $column
     */
    private function filterPosition($column)
    {
        /** @var \Epicor\Lists\Model\ResourceModel\Product\Collection $collection */
        $positionValues = $column->getFilter()->getValue();
        $positionFrom = $positionValues['from'] ?? false;
        $positionTo = $positionValues['to'] ?? false;
        if($positionTo === '0' && $positionFrom === '0'){
            $collection = $this->getCollection();

            $filterQuery = "lp.list_position is null";
            $collection->getSelect()->where($filterQuery);
            return;
        }
        if ($positionFrom !== false && $positionTo !== false) {
            $collection = $this->getCollection();
            $filterQuery = "lp.list_position >= $positionFrom and lp.list_position <= $positionTo";
            $collection->getSelect()->where($filterQuery);
        }

    }

    /**
     * @param $column
     * @return bool
     */
    private function isCustomFilter($column)
    {
        $columnIdentifier = $column->getId();
        return in_array($columnIdentifier, self::CUSTOM_FILTER_FIELDS, true);
    }

    /**
     * @param $column
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function setCustomerFilters($column)
    {
        if($column->getId() === 'selected_products'){
            $this->filterSelectedProducts($column);
        }
        if($column->getId() === 'list_position'){
            $this->filterPosition($column);
        }
    }

    /**
     * Is this tab shown?
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab Label
     *
     * @return boolean
     */
    public function getTabLabel()
    {
        return 'Products';
    }

    /**
     * Tab Title
     *
     * @return boolean
     */
    public function getTabTitle()
    {
        return 'Products';
    }

    /**
     * Is this tab hidden?
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Gets the List for this tab
     *
     * @return \Epicor\Lists\Model\ListModel
     */
    public function getList()
    {
        if (!isset($this->list)) {
            if ($this->registry->registry('list')) {
                $this->list = $this->registry->registry('list');
            } else {
                $this->list = $this->listsListModelFactory->create()->load($this->getRequest()->getParam('id'));
            }
        }
        return $this->list;
    }

    /**
     * Build data for List Products
     *
     * @return \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Products
     */
    protected function _prepareCollection()
    {
        $collection = $this->catalogResourceModelProductCollectionFactory->create();
        /* @var $collection Epicor_Comm_Model_Product */
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('ecc_uom');
        $collection->addAttributeToSelect('ecc_configurator');
        $collection->addAttributeToSelect('list_position');
        $collection->setFlag('allow_duplicate', 1);
        $collection->getSelect()->joinLeft(
            array('lp' => $collection->getTable('ecc_list_product')), 'e.sku = lp.sku AND lp.list_id = "' . $this->getList()->getId() . '"', array('qty', 'location_code', 'list_position')
        );

        //If the type is contract then search for the product
        if ($this->getList()->getType() == "Co") {
            $collection->getSelect()->joinLeft(
                array('cp' => $collection->getTable('ecc_contract_product')), 'cp.list_product_id =lp.id', array('start_date', 'line_number', 'part_number', 'end_date', 'status', 'is_discountable', 'min_order_qty', 'max_order_qty')
            );
        }

        if ($this->getList()->getTypeInstance()->isSectionEditable('products') == false) {
            $ids = $this->_getSelected();
            if (!empty($ids)) {
                $collection->addFieldToFilter('sku', array('in' => $ids));
            }
        }
        if ($this->columnSort) {
            $this->columnSort->setPositionOrder($collection);
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Build columns for List Products
     *
     * @return \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Products
     */
    protected function _prepareColumns()
    {
        if ($this->getList()->getTypeInstance()->isSectionEditable('products')) {
            $this->addColumn('selected_products', array(
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'selected_products',
                'values' => $this->_getSelected(),
                'align' => 'center',
                'index' => 'sku',
                'filter_index' => 'main_table.sku',
                'sortable' => false,
                'field_name' => 'links[]',
                'use_index' => true
            ));
        }

        $this->addColumn(
            'sku', array(
            'header' => __('SKU'),
            'index' => 'sku',
            'filter_index' => 'sku',
            'type' => 'text',
            'sortable' => true,
            'renderer' => '\Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Renderer\Skunodelimiter',
            )
        );
        $this->addColumn(
            'uom', array(
            'header' => __('UOM'),
            'index' => 'ecc_uom',
            'filter_index' => 'ecc_uom', // put in as sorting wouldn't work properly, might need to look again 
            'type' => 'text',
            'sortable' => true,
            'filterable' => true,
            )
        );


        if($this->getList()->getType() == 'Fa'){
            $this->addColumn(
                'loc_display', array(
                    'header' => __('Location'),
                    'index' => 'location_code',
                    'type' => 'text',
                    'renderer' => '\Epicor\Lists\Block\Customer\Account\Listing\Renderer\Location'
                )
            );
            $this->addColumn(
                'loc', array(
                    'header' => __('Location'),
                    'index' => 'location_code',
                    'type' => 'text',
                    'column_css_class' => "no-display",
                    'header_css_class' => "no-display"
                )
            );
        }

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

        if ($this->getList()->getType() == "Co") {

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
            $this->addColumn('status', array(
                'header' => __('Status'),
                'width' => '50',
                'index' => 'status',
                'align' => 'center',
                'type' => 'options',
                'sortable' => false,
                'options' => array('1' => 'Enabled', '0' => 'Disabled'),
                'filter_condition_callback' => array($this, '_StatusFilter')
            ));
            $this->addColumn(
                'is_discountable', array(
                'header' => __('Discountable'),
                'index' => 'is_discountable',
                'filter_index' => 'is_discountable',
                'type' => 'options',
                'sortable' => false,
                'filterable' => true,
                'options' => array(
                    0 => __('No'),
                    1 => __('Yes')
                ),
                'filter_condition_callback' => array($this, '_DiscountFilter')
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
        }

        if ($this->getList()->getTypeInstance()->isSectionVisible('pricing')) {
            $this->addColumn('actions', array(
                'header' => __('Actions'),
                'width' => '100',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'class' => 'enabled-pricing-link pricing-link',
                        'caption' => __('Pricing'),
                        'onclick' => 'return listProduct.pricing(this, event);',
                        'href' => 'javascript:void(0);',
                        'conditions' => array('ecc_configurator' => array('0', 0, 'empty', 'null'),'type_id' => array('simple','virtual','bundle','downloadable','configurable')),
                    ),
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
                'renderer' => 'Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Action',
                'links' => 'true',
            ));
        }

        $this->addColumn(
            'list_position', array(
                'header' => __('Position'),
                'index' => 'list_position',
                'type' => 'number',
                'validate_class' => 'validate-number',
                'sortable' => true,
                'filterable' => true,
                'is_grid_update' => true,
                'editable' => true,
                'column_css_class' => 'ecc-list-position',
                'renderer' => '\Epicor\Lists\Block\Customer\Account\Listing\Renderer\Position'

            )
        );

        $this->addColumn('imgicon', array(
            'type' => 'text',
            'filter' => false,
            'sortable' => false,
            'width' => 25,
            'renderer' => '\Epicor\Lists\Block\Customer\Account\Listing\Renderer\IsGrouped',
        ));

        $this->addColumn('row_id', array(
            'header' => __('Position'),
            'name' => 'row_id',
            'type' => 'input',
            'validate_class' => 'validate-number',
            'index' => 'sku',
            'width' => 0,
            'editable' => true,
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display'
        ));

        //Export product with pricing
        $this->addExportType('*/*/exportProductCsv', __('CSV'));

        return parent::_prepareColumns();
    }

    /**
     * Used in grid to return selected Products values.
     * 
     * @return array
     */
    protected function _getSelected()
    {
        return array_map('strval', array_keys($this->getSelected()));
    }

    /**
     * Builds the array of selected Products
     * 
     * @return array
     */
    public function getSelected()
    {
        if (empty($this->_selected) && $this->getRequest()->getParam('ajax') !== 'true') {
            $list = $this->getList();

            foreach ($list->getProducts() as $product) {
                $this->_selected[$product->getSku()] = array('sku' => $product->getSku());
            }
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
    public function setSelected($selected)
    {
        if (!empty($selected)) {
            foreach ($selected as $id) {
                $this->_selected[$id] = array('id' => $id);
            }
        }
    }

    /**
     * Gets grid url for ajax reloading
     *
     * @return string
     */
    public function getGridUrl()
    {
        $params = array(
            'id' => $this->getList()->getId(),
            '_current' => true,
        );
        return $this->getUrl('epicor_lists/epicorlists_lists/productsgrid', $params);
    }

    /**
     * Row Click URL
     *
     * @param \Epicor\Comm\Model\Product $row
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
    public function _uomFilter($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        /* @var $delimiter epicor_lists_helper_messaging_customer */
        $delimiter = $this->listsMessagingCustomerHelper->getUOMSeparator();

        echo $this->getCollection()->getSelect();

        // if unable to get a value of the column don't attempt filter 
        if (!$value) {
            return $this;
        }
        $this->getCollection()->getSelect()
            ->where("e.sku like ?", "%" . $delimiter . $value . "%");

        $collection->getSelect()->order('sku');
    }

    /**
     * Row _LineFilter
     *
     * @param $collection, $column
     * 
     * @return null
     */
    protected function _LineFilter($collection, $column)
    {
        $filterroleid = $column->getFilter()->getValue();
        if (!$filterroleid) {
            return $this;
        }
        $this->getCollection()->getSelect()
            ->where("cp.line_number like ?", "%" . $filterroleid . "%");

        return;
    }

    /**
     * Row _PartFilter
     *
     * @param $collection, $column
     * 
     * @return null
     */
    protected function _PartFilter($collection, $column)
    {
        $filterroleid = $column->getFilter()->getValue();
        if (!$filterroleid) {
            return $this;
        }
        $this->getCollection()->getSelect()
            ->where("cp.part_number like ?", "%" . $filterroleid . "%");

        return;
    }

    /**
     * Row _StatusFilter
     *
     * @param $collection, $column
     * 
     * @return null
     */
    protected function _StatusFilter($collection, $column)
    {
        $filterroleid = $column->getFilter()->getValue();

        $this->getCollection()->getSelect()->where("cp.status =" . $filterroleid);

        return;
    }

    /**
     * Row _DiscountFilter
     *
     * @param $collection, $column
     * 
     * @return null
     */
    protected function _DiscountFilter($collection, $column)
    {
        $filterroleid = $column->getFilter()->getValue();

        $this->getCollection()->getSelect()->where("cp.is_discountable =" . $filterroleid);

        return;
    }

    /**
     * Row _startDateFilter
     *
     * @param $collection, $column
     * 
     * @return null
     */
    protected function _startDateFilter($collection, $column)
    {
        $filterroleid = $column->getFilter()->getValue();
        if ($filterroleid['orig_from']) {
            $dateStart = date('Y-m-d', strtotime($filterroleid['orig_from']));
        }
        if ($filterroleid['orig_to']) {
            $dateEnd = date('Y-m-d', strtotime($filterroleid['orig_to']));
        }
        if (($filterroleid['orig_from']) || ($filterroleid['orig_to'])) {
            $dateCondition = $this->dateCondition($dateStart, $dateEnd, $column->getId());
        }
        if ($dateCondition) {
            $this->getCollection()->getSelect()->where($dateCondition);
        }
        return;
    }

    /**
     * Row _EndDateFilter
     *
     * @param $collection, $column
     * 
     * @return null
     */
    protected function _EndDateFilter($collection, $column)
    {
        $filterroleid = $column->getFilter()->getValue();

        if ($filterroleid['orig_from']) {
            $dateStart = date('Y-m-d', strtotime($filterroleid['orig_from']));
        }
        if ($filterroleid['orig_to']) {
            $dateEnd = date('Y-m-d', strtotime($filterroleid['orig_to']));
        }
        if (($filterroleid['orig_from']) || ($filterroleid['orig_to'])) {
            $dateCondition = $this->dateCondition($dateStart, $dateEnd, $column->getId());
        }
        if ($dateCondition) {
            $this->getCollection()->getSelect()->where($dateCondition);
        }

        return;
    }

    public function dateCondition($dateFrom = null, $dateEnd = null, $columnName)
    {

        if (!empty($dateFrom) && !empty($dateEnd)) {
            $dateConditions = " cp." . $columnName . " BETWEEN '{$dateFrom}' AND '{$dateEnd}'";
        } else if (!empty($dateFrom) && empty($dateEnd)) {
            $dateConditions = " cp." . $columnName . " >= '{$dateFrom}'";
        } else if (empty($dateFrom) && !empty($dateEnd)) {
            $dateConditions = " cp." . $columnName . " >= '{$dateEnd}'";
        } elseif (empty($dateFrom) && empty($dateTo)) {
            $dateConditions = '';
        }
        return $dateConditions;
    }

}
