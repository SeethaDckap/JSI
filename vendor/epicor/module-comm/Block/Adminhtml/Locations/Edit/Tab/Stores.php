<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Locations\Edit\Tab;


class Stores extends \Magento\Backend\Block\Widget\Grid\Extended
{

    private $_selected = array();

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Store\Model\ResourceModel\Group\CollectionFactory
     */
    protected $groupFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\ResourceModel\Group\CollectionFactory $collectionFactory,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->groupFactory = $collectionFactory;
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
                $this->getCollection()->addFieldToFilter('main_table.group_id', array('in' => $storeIds));
            } else {
                if ($storeIds) {
                    $this->getCollection()->addFieldToFilter('main_table.group_id', array('nin' => $storeIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    public function getGroupStores()
    {
        $location = $this->getLocation();
        /* @var $location Epicor_Comm_Model_Customer_Erpaccount */
        return $location->getValidStores();
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
        //M1 > M2 Translation Begin (Rule p2-1)
        //$collection = Mage::getModel('core/store_group')
        $collection = $this->groupFactory->create();
        //M1 > M2 Translation End
        /* @var $collection Mage_Core_Model_Resource_Store_Group_Collection */

        $collection->join(array('web' => 'store_website'), 'main_table.website_id = web.website_id', array('website_name' => 'web.name'));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('selected_store', array(
            'header' => __('Select'),
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'selected_store',
            'values' => $this->_getSelected(),
            'align' => 'center',
            'index' => 'group_id',
            'filter_index' => 'main_table.group_id',
            'sortable' => false,
            'field_name' => 'links[]'
        ));

        $this->addColumn('website_title', array(
            'header' => __('Website Name'),
            'align' => 'left',
            'index' => 'website_name',
            'filter_index' => 'website_name',
        ));

        $this->addColumn('group_title', array(
            'header' =>__('Store Name'),
            'align' => 'left',
            'index' => 'name',
            'filter_index' => 'name',
        ));

        $this->addColumn('row_id', array(
            'header' => __('Position'),
            'name' => 'row_id',
            'type' => 'number',
            'validate_class' => 'validate-number',
            'index' => 'group_id',
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
            $location = $this->getLocation();
            /* @var $location Epicor_Comm_Model_Location */

            foreach ($location->getStoreLinks() as $link) {
                /* @var $link Epicor_Comm_Model_Location_Link */
                $this->_selected[$link->getEntityId()] = array('id' => $link->getEntityId());
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
        return $this->getUrl('adminhtml/epicorcomm_locations/storesgrid', $params);
    }

    public function getRowUrl($row)
    {
        return null;
    }


}
