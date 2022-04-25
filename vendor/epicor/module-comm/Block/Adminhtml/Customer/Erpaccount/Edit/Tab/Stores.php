<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab;


class Stores extends \Magento\Backend\Block\Widget\Grid\Extended implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    private $_selected = array();
    protected $_erp_customer;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\ResourceModel\Website\CollectionFactory
     */
    protected $websiteCollection;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websitesFactory,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->scopeConfig = $context->getScopeConfig();
        $this->websiteCollection = $websitesFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('storeGrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);

        $this->setDefaultSort('website_title');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setFilterVisibility(true);
        $this->setDefaultFilter(array('selected_store' => 1));
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag

        if ($column->getId() == 'selected_store') {
            $storeIds = $this->_getSelected();
            if (empty($storeIds)) {
                $storeIds = array(0);
            }

            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('store_table.store_id', array('in' => $storeIds));
            } else {
                if ($storeIds) {
                    $this->getCollection()->addFieldToFilter('store_table.store_id', array('nin' => $storeIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    public function getGroupStores()
    {
        $customer = $this->getErpCustomer();
        return $customer->getValidStores();
    }

    public function canShowTab()
    {
        return true;
    }

    public function getTabLabel()
    {
        return 'Stores';
    }

    public function getTabTitle()
    {
        return 'Stores';
    }

    public function isHidden()
    {
        return false;
    }

    public function getErpCustomer()
    {
        if (!$this->_erp_customer) {
            $this->_erp_customer = $this->registry->registry('customer_erp_account');
        }
        return $this->_erp_customer;
    }

    protected function _prepareCollection()
    {
        //M1 > M2 Translation Begin (Rule p2-1)
        //$collection = Mage::getModel('core/website')
        //    ->joinGroupAndStore();
        $collection = $this->websiteCollection->create()->joinGroupAndStore();
        //M1 > M2 Translation End
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        if (!$this->scopeConfig->isSetFlag('Epicor_Comm/brands/erpaccount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $this->addColumn('selected_store', array(
                'header' => __('Select'),
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'selected_store',
                'values' => $this->_getSelected(),
                'align' => 'center',
                'index' => 'store_id',
                'filter_index' => 'store_table.store_id',
                'sortable' => false,
                'field_name' => 'links[]'
            ));
        } else {
            $this->addColumn('selected_store', array(
                'header' => __('Select'),
                //'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'selected_store',
                'values' => $this->_getSelected(),
                'align' => 'center',
                'index' => 'store_id',
                'filter_index' => 'store_table.store_id',
                'sortable' => false,
                'field_name' => 'links[]',
                'column_css_class' => 'no-display',
                'header_css_class' => 'no-display'
            ));
        }

        $this->addColumn('website_title', array(
            'header' => __('Website Name'),
            'align' => 'left',
            'index' => 'name',
            'filter_index' => 'main_table.name',
        ));

        $this->addColumn('group_title', array(
            'header' => __('Store Name'),
            'align' => 'left',
            'index' => 'group_title',
            'filter_index' => 'group_table.name',
        ));

        $this->addColumn('store_title', array(
            'header' => __('Store View Name'),
            'align' => 'left',
            'index' => 'store_title',
            'filter_index' => 'store_table.name',
        ));

        $this->addColumn('row_id', array(
            'header' => __('Position'),
            'name' => 'row_id',
            'type' => 'number',
            'validate_class' => 'validate-number',
            'index' => 'store_id',
            'width' => 0,
            'editable' => true,
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display'
        ));

        return parent::_prepareColumns();
    }

    protected function _getSelected()
    {   // Used in grid to return selected customers values.
        return array_keys($this->getSelected());
    }

    public function getSelected()
    {
        if (empty($this->_selected) && $this->getRequest()->getParam('ajax') !== 'true') {

            $customer = $this->getErpCustomer();

            foreach ($customer->getValidStores() as $store) {
                $this->_selected[$store] = array('id' => $store);
            }
        }
        return $this->_selected;
    }

    public function setSelected($selected)
    {
        if (!empty($selected)) {
            foreach ($selected as $id) {
                $this->_selected[$id] = array('id' => $id);
            }
        }
    }

    public function getGridUrl()
    {
        $params = array(
            'id' => $this->getErpCustomer()->getId(),
            '_current' => true,
        );
        return $this->getUrl('adminhtml/epicorcomm_customer_erpaccount/storesgrid', $params);
    }

    public function getRowUrl($row)
    {
        return null;
    }

}
