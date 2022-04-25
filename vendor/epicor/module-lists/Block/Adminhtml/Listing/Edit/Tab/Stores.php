<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab;


/**
 * List Stores Serialized Grid
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Stores extends \Magento\Backend\Block\Widget\Grid\Extended implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    private $_selected = array();

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    /**
     * @var \Epicor\Lists\Model\ListModel\TypeFactory
     */
    protected $listsListModelTypeFactory;

    /**
     * @var \Magento\Store\Model\GroupFactory
     */
    protected $groupFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Epicor\Lists\Helper\Data $listsHelper,
        \Epicor\Lists\Model\ListModel\TypeFactory $listsListModelTypeFactory,
        \Magento\Store\Model\GroupFactory $groupFactory,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->listsListModelFactory = $listsListModelFactory;
        $this->listsHelper = $listsHelper;
        $this->listsListModelTypeFactory = $listsListModelTypeFactory;
        $this->groupFactory = $groupFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('storesGrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);

        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setCacheDisabled(true);
        $this->setFilterVisibility(true);
        $this->setDefaultFilter(array('selected_stores' => 1));
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag

        if ($column->getId() == 'selected_stores') {
            $ids = $this->_getSelected();
            if (empty($ids)) {
                $ids = array(0);
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('main_table.group_id', array('in' => $ids));
            } else {
                $this->getCollection()->addFieldToFilter('main_table.group_id', array('nin' => $ids));
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
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
        return 'Stores';
    }

    /**
     * Tab Title
     *
     * @return boolean
     */
    public function getTabTitle()
    {
        return 'Stores';
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
     * @return boolean
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
     * Build data for List Stores
     *
     * @return \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Stores
     */
    protected function _prepareCollection()
    {

        //M1 > M2 Translation Begin (Rule p2-1)
        //$collection = Mage::getModel('core/store_group');
        $collection = $this->groupFactory->create()->getCollection();
        //M1 > M2 Translation End
        /* @var $collection Mage_Core_Model_Resource_Store_Group_Collection */
        $collection->getSelect()->join(
            array('website' => $collection->getTable('store_website')), 'main_table.website_id = website.website_id', array('website_name' => 'website.name')
        );

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Build columns for List Stores
     *
     * @return \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Stores
     */
    protected function _prepareColumns()
    {
        $helper = $this->listsHelper;
        /* @var $helper Epicor_Lists_Helper_Data */

        $this->addColumn('selected_stores', array(
            'header' => __('Select'),
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'selected_stores',
            'values' => $this->_getSelected(),
            'align' => 'center',
            'index' => 'group_id',
            'filter_index' => 'main_table.group_id',
            'sortable' => false,
            'field_name' => 'links[]'
        ));


        $this->addColumn(
            'website_title', array(
            'header' => __('Website Name'),
            'index' => 'website_name',
            'filter_index' => 'website.name',
            'type' => 'text'
            )
        );

        $this->addColumn(
            'group_title', array(
            'header' => __('Store Name'),
            'index' => 'name',
            'filter_index' => 'main_table.name',
            'type' => 'text'
            )
        );


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

    /**
     * Used in grid to return selected Stores values.
     * 
     * @return array
     */
    protected function _getSelected()
    {
        return array_keys($this->getSelected());
    }

    /**
     * Builds the array of selected Stores
     * 
     * @return array
     */
    public function getSelected()
    {
        if (empty($this->_selected) && $this->getRequest()->getParam('ajax') !== 'true') {
            $list = $this->getList();
            /* @var $list \Epicor\Lists\Model\ListModel */

            foreach ($list->getStoreGroups() as $storeGroup) {
                $this->_selected[$storeGroup->getId()] = array('id' => $storeGroup->getId());
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
        return $this->getUrl('epicor_lists/epicorlists_lists/storesgrid', $params);
    }

    /**
     * Row Click URL
     *
     * @param \Magento\Store\Model\Group $row
     * 
     * @return null
     */
    public function getRowUrl($row)
    {
        return null;
    }

    public function getEmptyText()
    {
        $type = $this->listsListModelTypeFactory->create()->getListLabel($this->getList()->getType());
        //M1 > M2 Translation Begin (Rule 55)
        //return $this->__('No Stores Selected.%s not restricted by Store', $type);
        return __('No Stores Selected.%1 not restricted by Store', $type);
        //M1 > M2 Translation End
    }

}
