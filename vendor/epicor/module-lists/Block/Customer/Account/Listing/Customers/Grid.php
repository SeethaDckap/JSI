<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Customer\Account\Listing\Customers;


/**
 * List's  customer Grid config
 * 
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid
{

    private $_selected = array();

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Magento\Backend\Helper\Data 
     */
    protected $backendHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Lists\Helper\Data $listsHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
         array $data = []
    )
    {
        $this->storeManager = $context->getStoreManager();
        $this->commHelper = $commHelper;
        $this->listsHelper = $listsHelper;
        $this->registry = $registry;
        $this->listsListModelFactory = $listsListModelFactory;
        $this->generic = $context->getSession();
        $this->backendHelper = $backendHelper;
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $data
            );
        $this->setId('list_customers');
        $this->setIdColumn('id');
        $this->setSaveParametersInSession(false);
        $this->setMessageBase('epicor_comm');
        $this->setCustomColumns($this->_getColumns());
        $this->setCacheDisabled(true);
        $this->setUseAjax(true);
        $this->setFilterVisibility(true);
        $this->setDefaultFilter(array('selected_customers' => 1));
    }

    protected function _prepareCollection()
    {

        $store_id = $this->storeManager->getStore()->getStoreId();
        $erpAccount = $this->commHelper->getErpAccountInfo();
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
        $collection = $erpAccount->getCustomers($erpAccount->getId());
        /* @var $collection Mage_Customer_Model_Resource_Customer_Collection */
        $collection->addFieldToFilter('website_id', $this->storeManager->getWebsite()->getId());
        $collection->addNameToSelect();
        /* filter start */
        $filter = $this->getParam($this->getVarNameFilter(), null);
        if (!is_null($filter)) {
            $filter = $this->backendHelper->prepareFilterString($filter);
            if (isset($filter['address_email_address'])) {
                $collection->addAttributeToFilter('email', array("like" => '%' . $filter['address_email_address'] . '%'));
            }
        }
        /* ends here */
        $this->setCollection($collection);
        return \Magento\Backend\Block\Widget\Grid::_prepareCollection();
    }

    protected function _toHtml()
    {
        $html = parent::_toHtml(false);
        return $html;
    }

    public function getRowUrl($row)
    {
        return '#';
    }

    protected function _getColumns()
    {
        $helper = $this->listsHelper;
        $columns = array(
            'selected_customers' => array(
                'header' => 'Select',
                'header_css_class' => 'a-center',
                'index' => 'entity_id',
                'type' => 'checkbox',
                'name' => 'selected_customers',
                'values' => $this->_getSelected(),
                'align' => 'center',
                'filter_index' => 'main_table.entity_id',
                'sortable' => false,
                'field_name' => 'links[]'
            ),
            'name' => array(
                'header' => __('Name'),
                'index' => 'name',
                'type' => 'text',
                'condition' => 'LIKE',
            ),
            'address_email_address' => array(
                'header' => __('Email'),
                'index' => 'email',
                'type' => 'text',
            ),
            'row_id' => array(
                'header' => __('Position'),
                'name' => 'row_id',
                'type' => 'number',
                'validate_class' => 'validate-number',
                'index' => 'entity_id',
                'width' => 0,
                'editable' => true,
                'column_css_class' => 'no-display',
                'header_css_class' => 'no-display'
            ),
        );
        return $columns;
    }

    /**
     * Used in grid to return selected Products values.
     * 
     * @return array
     */
    protected function _getSelected()
    {
        return array_keys($this->getSelected());
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
     * Gets the List for this tab
     *
     * @return boolean
     */
    public function getList()
    {
        if (!isset($this->list)) {
            if ($this->registry->registry('list')) {
                $this->list = $this->registry->registry('list');
            } else {
                $this->list = $this->listsListModelFactory->create()->load($this->getRequest()->getParam('list_id'));
            }
        }
        return $this->list;
    }

    /**
     * Builds the array of selected customers
     * 
     * @return array
     */
    public function getSelected()
    {
        if (!$this->getList()->getId()) {
            $selectedCustomers = $this->generic->getSelectedCustomers(true);
         if (is_array($selectedCustomers)) {
                foreach ($selectedCustomers as $customer) {
                    $this->_selected[$customer] = array(
                        'id' => $customer
                    );
                }
            }
        }

        if (empty($this->_selected) && $this->getList()->getId()) {
            $list = $this->getList();
            /* @var $list Epicor_Lists_Model_ListModel */
            foreach ($list->getCustomers() as $customer) {
                $this->_selected[$customer->getId()] = array('id' => $customer->getId());
            }
        }
        return $this->_selected;
    }

    protected function _addColumnFilterToCollection($column)
    {

        if ($column->getId() == 'selected_customers') {
            $ids = $this->_getSelected();
            if (empty($ids)) {
                $ids = array(0);
            }

            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $ids));
            } else if ($ids) {
                $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $ids));
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/customersgrid', array('_current' => true));
    }

}
