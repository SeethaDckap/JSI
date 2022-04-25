<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Adminhtml\Groups\Edit\Tab\Hierarchy;

use Epicor\OrderApproval\Model\GroupsFactory as GroupsFactory;
use Epicor\OrderApproval\Model\Groups as Groups;
use Epicor\OrderApproval\Model\HierarchyManagementFactory as HierarchyManagementFactory;
use Epicor\OrderApproval\Model\ResourceModel\Groups\CollectionFactory as GroupCollectionFactory;

/**
 * Adminhtml hierarchy parent grid block
 *
 * @api
 * @since 100.0.2
 */
class ParentGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var array
     */
    private $selected = array();

    /**
     * @var GroupCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var GroupsFactory
     */
    private $groupsFactory;

    /**
     * Grid constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data            $backendHelper
     * @param GroupCollectionFactory                  $collectionFactory
     * @param GroupsFactory                           $groupsFactory
     * @param HierarchyManagementFactory              $hierarchyManagementFactory
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        GroupCollectionFactory $collectionFactory,
        GroupsFactory $groupsFactory,
        HierarchyManagementFactory $hierarchyManagementFactory,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->groupsFactory = $groupsFactory;
        $this->hierarchyManagementFactory = $hierarchyManagementFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('parentGrid');
        $this->setDefaultSort('name');
        $this->setSaveParametersInSession(false);
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setDefaultFilter(['parent_hierarchy' => 1]);

        $this->setEmptyText(__('No Parent Group Found'));
    }

    /**
     * Gets grid url for ajax reloading
     *
     * @return string
     */
    public function getGridUrl()
    {
        $params = array(
            'id'       => $this->getGroup()->getGroupId(),
            '_current' => true,
        );

        return $this->getUrl('*/*/hierarchyParentGrid', $params);
    }

    /**
     * Gets the Groups for this tab
     *
     * @return Groups
     */
    public function getGroup()
    {
        if ( ! isset($this->group)) {
            $this->group = $this->groupsFactory->create()->load(
                $this->getRequest()->getParam('group_id')
            );
        }

        return $this->group;
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        /** @var $collection \Epicor\OrderApproval\Model\ResourceModel\Groups\Collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('group_id',
            array('neq' => $this->getRequest()->getParam('group_id')));

        /** @var \Epicor\OrderApproval\Model\HierarchyManagement $hierarchyManagement */
        $hierarchyManagement = $this->hierarchyManagementFactory->create();
        $collectionChild
            = $hierarchyManagement->getChildrenCollection($this->getRequest()
            ->getParam('group_id'));
        $child = [];
        foreach ($collectionChild->getItems() as $item) {
            $child[] = $item->getGroupId();
        }
        if (count($child) > 0) {
            $collection->addFieldToFilter('group_id',
                array('nin' => $child));
        }
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     *
     * @return $this|ParentGrid
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in salesreps flag
        if ($column->getId() == 'parent_hierarchy') {
            $salesrepIds = $this->_getSelected();

            if (empty($salesrepIds)) {
                $salesrepIds = array(0);
            }

            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('group_id', array('in' => $salesrepIds));
            } else {
                if ($salesrepIds) {
                    $this->getCollection()->addFieldToFilter('group_id', array('nin' => $salesrepIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }


    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'parent_hierarchy',
            [
                'header_css_class' => 'data-grid-actions-cell',
                'header'           => __('Select'),
                'type'             => 'radio',
                'html_name'        => 'hierarchy[parent]',
                'values'           => $this->_getSelected(),
                'align'            => 'center',
                'index'            => 'group_id',
                'filter_index'     => 'main_table.group_id',
                'sortable'         => false,
                'field_name'       => 'hierarchy[parent]',
            ]
        );

        $this->addColumn('group_id', array(
            'header' => __('Group Id'),
            'align' => 'left',
            'index' => 'group_id',
            'filter_index' => 'main_table.group_id',
        ));

        $this->addColumn(
            'name', array(
                'header' => __('Group Name'),
                'index'  => 'name',
                'type'   => 'text',
            )
        );

        $this->addColumn(
            'row_id', array(
                'header'           => __('Select'),
                'name'             => 'row_id',
                'type'             => 'number',
                'validate_class'   => 'validate-number',
                'index'            => 'group_id',
                'width'            => 0,
                'editable'         => true,
                'column_css_class' => 'no-display',
                'header_css_class' => 'no-display',
            )
        );

        return parent::_prepareColumns();
    }

    /**
     * Used in grid to return selected ERP Accounts values.
     *
     * @return array
     */
    protected function _getSelected()
    {
        return array_keys($this->getSelected());
    }

    /**
     * Builds the array of selected ERP Accounts
     *
     * @return array
     */
    public function getSelected()
    {
        if (empty($this->selected)) {
            /* @var $group Groups */
            $group = $this->getGroup();
            /** @var \Epicor\OrderApproval\Model\HierarchyManagement $hierarchyManagement */
            $hierarchyManagement = $this->hierarchyManagementFactory->create();
            $collection
                = $hierarchyManagement->getParentCollection($group->getGroupId());
            foreach ($collection->getItems() as $item) {
                $this->selected[$item->getParentGroupId()]
                    = array('id' => $item->getParentGroupId());
            }
        }

        return $this->selected;
    }
}
