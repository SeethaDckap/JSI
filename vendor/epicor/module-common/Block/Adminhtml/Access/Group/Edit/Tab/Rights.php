<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Access\Group\Edit\Tab;


class Rights extends \Magento\Backend\Block\Widget\Grid
{

    private $_selected = array();

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Common\Model\ResourceModel\Access\Right\CollectionFactory
     */
    protected $commonResourceAccessRightCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Model\ResourceModel\Access\Right\CollectionFactory $commonResourceAccessRightCollectionFactory,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->commonResourceAccessRightCollectionFactory = $commonResourceAccessRightCollectionFactory;
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
        if ($this->getGroup()->getId()) {
            $this->setDefaultFilter(array('right_in_group' => 1));
        }
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in category flag
        if ($column->getId() == 'right_in_group') {
            $rightIds = $this->_getSelected();
            if (empty($rightIds)) {
                $rightIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $rightIds));
            } elseif (!empty($rightIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $rightIds));
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Gets the current group data
     * 
     * @return \Epicor\Common\Model\Access\Group
     */
    public function getGroup()
    {

        if (!$this->_accessright) {
            $this->_accessright = $this->registry->registry('access_group_data');
        }
        return $this->_accessright;
    }

    protected function _prepareCollection()
    {
        $collection = $this->commonResourceAccessRightCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    public function getSelected()
    {
        if (empty($this->_selected) && $this->getRequest()->getParam('ajax') !== 'true') {
            $this->_selected = array();

            foreach ($this->getGroup()->getLinkedRights() as $right) {
                $this->_selected[$right->getRightId()] = array('id' => $right->getRightId());
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

    protected function _prepareColumns()
    {

        $this->addColumn('right_in_group', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'field_name' => 'right[]',
            'name' => 'right',
            'values' => $this->_getSelected(),
            'align' => 'center',
            'editable' => true,
            'index' => 'entity_id'
        ));

        $this->addColumn('right_name', array(
            'header' => __('Access Right'),
            'align' => 'left',
            'name' => 'right_name',
            'index' => 'entity_name'
        ));

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

    public function getGridUrl()
    {
        return $this->_getData('grid_url') ? $this->_getData('grid_url') : $this->getUrl('*/*/rightsgrid', array('id' => $this->getGroup()->getId(), '_current' => true));
    }

    public function getRowUrl($row)
    {
        return null;
    }

}
