<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Locationgroups\Edit\Tab;


class Grouplocations extends \Magento\Backend\Block\Widget\Grid\Extended
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
     * @var \Epicor\Comm\Model\ResourceModel\Location\CollectionFactory
     */
    protected $commResourceLocationCollectionFactory;
    
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
        \Epicor\Comm\Model\ResourceModel\Location\CollectionFactory $commResourceLocationCollectionFactory,
        \Epicor\Comm\Model\ResourceModel\Location\Grouplocations\CollectionFactory $commResourceGrouplocationsCollectionFactory,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->commLocationsHelper = $commLocationsHelper;
        $this->storeSystemStore = $storeSystemStore;
        $this->commResourceLocationCollectionFactory = $commResourceLocationCollectionFactory;
        $this->commResourceGrouplocationsCollectionFactory = $commResourceGrouplocationsCollectionFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('grouplocationsGrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);

        $this->setDefaultSort('code');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setFilterVisibility(true);
        $this->setDefaultFilter(array('selected_grouplocations' => 1));
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag

        if ($column->getId() == 'selected_grouplocations') {
            $ids = $this->_getSelected();
            if (empty($ids)) {
                $ids = array(0);
            }

            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('main_table.id', array('in' => $ids));
            } else {
                if ($ids) {
                    $this->getCollection()->addFieldToFilter('main_table.id', array('nin' => $ids));
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
        return 'Locations';
    }

    public function getTabTitle()
    {
        return 'Locations';
    }

    public function isHidden()
    {
        return false;
    }

    /**
     *
     * @return \Epicor\Comm\Model\Location\Groups
     */
    public function getGroup()
    {
//        if (!$this->_location) {
        $this->_group = $this->registry->registry('group');
//        }
        return $this->_group;
    }

    protected function _prepareCollection()
    {
        $group = $this->getGroup();
        $collection = $this->commResourceLocationCollectionFactory->create();
        $joinCondition = "main_table.id = grouplocations.group_location_id and grouplocations.group_id = ".$group->getId();
        $collection->getSelect()
                ->joinLeft(
                    array("grouplocations" => $collection->getTable('ecc_location_grouplocations')),
                    $joinCondition,
                    array("position" => "grouplocations.position")
                );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('selected_grouplocations', array(
            'header' => __('Select'),
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'selected_grouplocations',
            'values' => $this->_getSelected(),
            'align' => 'center',
            'index' => 'id',
            'sortable' => false,
            'field_name' => 'links[]',
            'use_index' => true
        ));

        $this->addColumn('code', array(
            'header' => __('Code'),
            'width' => '150',
            'index' => 'code',
            'filter_index' => 'code'
        ));

        $this->addColumn('name', array(
            'header' => __('Name'),
            'width' => '150',
            'index' => 'name',
            'filter_index' => 'name'
        ));
        $this->addColumn('address1', array(
            'header' => __('Address 1'),
            'width' => '150',
            'index' => 'address1',
            'filter_index' => 'address1'
        ));
        $this->addColumn('address2', array(
            'header' => __('Address 2'),
            'width' => '150',
            'index' => 'address2',
            'filter_index' => 'address2'
        ));
        $this->addColumn('address3', array(
            'header' => __('Address 3'),
            'width' => '150',
            'index' => 'address3',
            'filter_index' => 'address3'
        ));
        $this->addColumn('city', array(
            'header' => __('City'),
            'width' => '150',
            'index' => 'city',
            'filter_index' => 'city'
        ));
        $this->addColumn('county', array(
            'header' => __('County'),
            'width' => '150',
            'index' => 'county',
            'filter_index' => 'county'
        ));
        $this->addColumn('postcode', array(
            'header' => __('Postcode'),
            'width' => '150',
            'index' => 'postcode',
            'filter_index' => 'postcode'
        ));
        $this->addColumn('location_visible', array(
            'header' => __('Location Visible'),
            'width' => '150',
            'index' => 'location_visible',
            'type' => 'options',
            'filter_index' => 'location_visible',
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
            'index' => 'position',
            'width' => 0,
            'editable' => true
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
            $groupId = $this->getGroup()->getId();
            $collection = $this->commResourceGrouplocationsCollectionFactory->create();
            /* @var $collection \Epicor\Comm\Model\ResourceModel\Location\Relatedlocations\CollectionFactory */
            $collection->addFieldToFilter('group_id', $groupId);
            $items = $collection->getItems();
            foreach ($items as $groupLocation) {
                $this->_selected[$groupLocation->getGroupLocationId()] = array('id' => $groupLocation->getGroupLocationId());
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
            'id' => $this->getGroup()->getId(),
            '_current' => true,
        );
        return $this->getUrl('adminhtml/epicorcomm_locationgroups/grouplocationsgrid', $params);
    }

    public function getRowUrl($row)
    {
        return null;
    }

}

