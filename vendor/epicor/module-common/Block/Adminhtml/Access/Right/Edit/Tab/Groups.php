<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Access\Right\Edit\Tab;


class Groups extends \Magento\Backend\Block\Widget\Grid
{

    private $_selected = array();

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Common\Model\ResourceModel\Access\Group\CollectionFactory
     */
    protected $commonResourceAccessGroupCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Model\ResourceModel\Access\Group\CollectionFactory $commonResourceAccessGroupCollectionFactory,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->commonResourceAccessGroupCollectionFactory = $commonResourceAccessGroupCollectionFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
    }


    public function _construct()
    {
        $this->setId('accessRightGroupsGrid');
        $this->setDefaultSort('entity_name');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setFilterVisibility(true);
        if ($this->getRight()->getId()) {
            $this->setDefaultFilter(array('group_in_right' => 1));
        }
    }

    /**
     * 
     * @return \Epicor\Common\Model\Access\Right
     */
    public function getRight()
    {

        if (!$this->_accessright) {
            $this->_accessright = $this->registry->registry('access_right_data');
        }
        return $this->_accessright;
    }

    protected function _prepareCollection()
    {
        $collection = $this->commonResourceAccessGroupCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('group_in_right', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'field_name' => 'group[]',
            'name' => 'group',
            'values' => $this->_getSelected(),
            'align' => 'center',
            'editable' => true,
            'index' => 'entity_id'
        ));

        $this->addColumn('group_name', array(
            'header' => __('Group'),
            'align' => 'left',
            'name' => 'group_name',
            'index' => 'entity_name'
        ));

        $this->addColumn('row_id', array(
            'header' => __('Position'),
            'name' => 'row_id',
            'type' => 'number',
            'validate_class' => 'validate-number',
            'index' => 'entity_id',
            'editable' => true,
            'width' => 0,
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display'
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->_getData('grid_url') ? $this->_getData('grid_url') : $this->getUrl('*/*/groupsgrid', array('id' => $this->getRight()->getId(), '_current' => true));
    }

    public function setSelectedGroups($selected)
    {
        foreach ($selected as $id) {
            $this->_selected[$id] = array('id' => $id);
        }
    }

    public function getSelected()
    {
        if (empty($this->_selected) && $this->getRequest()->getParam('ajax') !== 'true') {
            $this->_selected = array();
            foreach ($this->getRight()->getLinkedGroups() as $right) {
                $this->_selected[$right->getGroupId()] = array('id' => $right->getGroupId());
            }
        }
        return $this->_selected;
    }

    public function _getSelected()
    {
        return array_keys($this->getSelected());
    }

    /**
     * Sets the currently selected items
     * 
     * @param array $selected
     */
    public function setSelected($selected)
    {
        if (!empty($selected)) {
            foreach ($selected as $id) {
                $this->_selected[$id] = array('id' => $id);
            }
        }
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in category flag
        if ($column->getId() == 'group_in_right') {
            $groupIds = $this->_getSelected();
            if (empty($groupIds)) {
                $groupIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $groupIds));
            } elseif (!empty($groupIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $groupIds));
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    public function getRowUrl($row)
    {
        return null;
    }

}
