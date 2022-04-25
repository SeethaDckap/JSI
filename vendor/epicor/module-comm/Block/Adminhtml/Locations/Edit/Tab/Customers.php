<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Locations\Edit\Tab;


class Customers extends \Magento\Backend\Block\Widget\Grid\Extended
{

    private $_selected = array();

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $storeSystemStore;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Magento\Store\Model\System\Store $storeSystemStore,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->commLocationsHelper = $commLocationsHelper;
        $this->storeSystemStore = $storeSystemStore;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('customerGrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);

        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setFilterVisibility(true);
        $this->setDefaultFilter(array('selected_customer' => 1));
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag

        if ($column->getId() == 'selected_customer') {
            $ids = $this->_getSelected();
            if (empty($ids)) {
                $ids = array(0);
            }

            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $ids));
            } else {
                if ($ids) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $ids));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    public function canShowTab()
    {
        return true;
    }

    public function getTabLabel()
    {
        return 'Customers';
    }

    public function getTabTitle()
    {
        return 'Customers';
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

    protected function _prepareCollection()
    {
        /* @var $helper Epicor_Comm_Helper_Locations */
        $helper = $this->commLocationsHelper;
        $this->setCollection($helper->getCustomersCollectionForLocation($this->getLocation()->getCode()));
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('selected_customer', array(
            'header' => __('Select'),
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'selected_customer',
            'values' => $this->_getSelected(),
            'align' => 'center',
            'index' => 'entity_id',
            'sortable' => false,
            'field_name' => 'links[]',
            'use_index' => true
        ));

        $this->addColumn('customer_name', array(
            'header' => __('Customer'),
            'width' => '150',
            'index' => 'name',
            'filter_index' => 'name'
        ));

        $this->addColumn('email', array(
            'header' => __('Email'),
            'width' => '150',
            'index' => 'email',
            'filter_index' => 'email'
        ));

        //M1 > M2 Translation Begin (Rule P2-6.8)
        //if (!Mage::app()->isSingleStoreMode()) {
        if (!$this->_storeManager->isSingleStoreMode()) {
            //M1 > M2 Translation End
            $this->addColumn('website_id', array(
                'header' => __('Website'),
                'align' => 'center',
                'width' => '80px',
                'type' => 'options',
                'options' => $this->storeSystemStore->getWebsiteOptionHash(true),
                'index' => 'website_id',
            ));
        }

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

        return parent::_prepareColumns();
    }

    protected function _getSelected()
    {   // Used in grid to return selected customers values.
        return array_keys($this->getSelected());
    }

    public function getSelected()
    {
        if (empty($this->_selected) && $this->getRequest()->getParam('ajax') !== 'true') {

            /* @var $helper Epicor_Comm_Helper_Locations */
            $helper = $this->commLocationsHelper;

            $locationCode = $this->getLocation()->getCode();
            $customers = $helper->getCustomersCollectionForLocation($locationCode);


            foreach ($customers->getItems() as $customer) {
                /* @var $customer Epicor_Comm_Model_Customer */
                $linkType = $customer->getEccLocationLinkType();
                if (is_null($linkType) || $customer->isLocationAllowed($locationCode)) {
                    $this->_selected[$customer->getEntityId()] = array('id' => $customer->getEntityId());
                }
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
            'id' => $this->getLocation()->getId(),
            '_current' => true,
        );
        return $this->getUrl('adminhtml/epicorcomm_locations/customersgrid', $params);
    }

    public function getRowUrl($row)
    {
        return null;
    }

}

