<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Locations\Edit\Tab;


class Groups extends \Magento\Backend\Block\Widget\Grid\Extended
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
    
    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\Groups\CollectionFactory
     */
    protected $commResourceGroupsCollectionFactory;
    
    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\Grouplocations\CollectionFactory
     */
    protected $commResourceGrouplocationsCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Magento\Store\Model\System\Store $storeSystemStore,
        \Epicor\Comm\Model\ResourceModel\Location\Groups\CollectionFactory $commResourceGroupsCollectionFactory,
        \Epicor\Comm\Model\ResourceModel\Location\Grouplocations\CollectionFactory $commResourceGrouplocationsCollectionFactory,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->commLocationsHelper = $commLocationsHelper;
        $this->storeSystemStore = $storeSystemStore;
        $this->commResourceGroupsCollectionFactory = $commResourceGroupsCollectionFactory;
        $this->commResourceGrouplocationsCollectionFactory = $commResourceGrouplocationsCollectionFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('groupsGrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);

        $this->setDefaultSort('code');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setFilterVisibility(true);
        $this->setDefaultFilter(array('selected_groups' => 1));
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag

        if ($column->getId() == 'selected_groups') {
            $ids = $this->_getSelected();
            if (empty($ids)) {
                $ids = array(0);
            }

            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('id', array('in' => $ids));
            } else {
                if ($ids) {
                    $this->getCollection()->addFieldToFilter('id', array('nin' => $ids));
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
        return 'Groups';
    }

    public function getTabTitle()
    {
        return 'Groups';
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
        $locationId = $this->getLocation()->getId();
        $collection = $this->commResourceGroupsCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('selected_groups', array(
            'header' => __('Select'),
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'selected_groups',
            'values' => $this->_getSelected(),
            'align' => 'center',
            'index' => 'id',
            'sortable' => false,
            'field_name' => 'links[]',
            'use_index' => true
        ));

        $this->addColumn('group_name', array(
            'header' => __('Group Name'),
            'width' => '150',
            'index' => 'group_name',
            'filter_index' => 'group_name'
        ));
        $this->addColumn('group_expandable', array(
            'header' => __('Group Expandable'),
            'width' => '150',
            'index' => 'group_expandable',
            'type' => 'options',
            'filter_index' => 'group_expandable',
            'options' => array(
                0 => __('No'),
                1 => __('Yes')
            )
        ));
        $this->addColumn('enabled', array(
            'header' => __('Enabled'),
            'width' => '150',
            'index' => 'enabled',
            'type' => 'options',
            'filter_index' => 'enabled',
            'options' => array(
                0 => __('No'),
                1 => __('Yes')
            )
        ));
        $this->addColumn('row_id', array(
            'header' => __('Position'),
            'name' => 'row_id',
            'type' => 'number',
            'validate_class' => 'validate-number',
            'index' => 'id',
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
            $locationId = $this->getLocation()->getId();
            $collection = $this->commResourceGrouplocationsCollectionFactory->create();
            /* @var $collection \Epicor\Comm\Model\ResourceModel\Location\Relatedlocations\CollectionFactory */
            $collection->addFieldToFilter('group_location_id', $locationId);
            $items = $collection->getItems();
            foreach ($items as $group) {
                $this->_selected[$group->getGroupId()] = array('id' => $group->getGroupId());
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
        return $this->getUrl('adminhtml/epicorcomm_locations/groupsgrid', $params);
    }

    public function getRowUrl($row)
    {
        return null;
    }

}

