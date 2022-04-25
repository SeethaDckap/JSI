<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Access\Right\Edit\Tab;


class Elements extends \Magento\Backend\Block\Widget\Grid
{

    private $_selected = array();

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Common\Model\ResourceModel\Access\Element\CollectionFactory
     */
    protected $commonResourceAccessElementCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Model\ResourceModel\Access\Element\CollectionFactory $commonResourceAccessElementCollectionFactory,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->commonResourceAccessElementCollectionFactory = $commonResourceAccessElementCollectionFactory;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
    }


    public function _construct()
    {
        $this->setId('accessRightElementsGrid');
        $this->setDefaultSort('module');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setFilterVisibility(true);
        if ($this->getRight()->getId()) {
            $this->setDefaultFilter(array('element_in_right' => 1));
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
        $collection = $this->commonResourceAccessElementCollectionFactory->create();
        /* @var $collection Epicor_Common_Model_Resource_Access_Element_Collection */
        $collection->addFieldToFilter('excluded', 0);
        if ($this->scopeConfig->isSetFlag('epicor_b2b/registration/reg_portaltype', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $collection->addFieldToFilter('portal_excluded', 0);
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('element_in_right', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'field_name' => 'element[]',
            'name' => 'element',
            'values' => $this->_getSelected(),
            'align' => 'center',
            'editable' => true,
            'index' => 'id'
        ));

        $this->addColumn('module_name', array(
            'header' => __('Module'),
            'align' => 'left',
            'index' => 'module'
        ));

        $this->addColumn('controller_name', array(
            'header' => __('Controller'),
            'align' => 'left',
            'index' => 'controller'
        ));

        $this->addColumn('action_name', array(
            'header' => __('Action'),
            'align' => 'left',
            'index' => 'action'
        ));

        $this->addColumn('block_name', array(
            'header' => __('Block'),
            'align' => 'left',
            'index' => 'block'
        ));


        $this->addColumn('action_type', array(
            'header' => __('Action Type'),
            'align' => 'left',
            'index' => 'action_type'
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

    public function getRowClass(\Magento\Framework\DataObject $row)
    {
        return $row->getId() ? '' : 'new';
    }

    public function getGridUrl()
    {
        return $this->_getData('grid_url') ? $this->_getData('grid_url') : $this->getUrl('*/*/elementsgrid', array('id' => $this->getRight()->getId(), '_current' => true));
    }

    public function getSelected()
    {
        if (empty($this->_selected) && $this->getRequest()->getParam('ajax') !== 'true') {
            $this->_selected = array();
            foreach ($this->getRight()->getLinkedElements() as $element) {
                $this->_selected[$element->getElementId()] = array('id' => $element->getElementId());
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
        if ($column->getId() == 'element_in_right') {
            $groupIds = $this->_getSelected();
            if (empty($groupIds)) {
                $groupIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('id', array('in' => $groupIds));
            } elseif (!empty($groupIds)) {
                $this->getCollection()->addFieldToFilter('id', array('nin' => $groupIds));
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
