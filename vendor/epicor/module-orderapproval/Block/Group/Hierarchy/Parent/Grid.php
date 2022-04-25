<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Group\Hierarchy\Parent;

use Epicor\OrderApproval\Model\Groups as ApprovalGroups;
use Magento\Backend\Block\Widget\Grid as WidgetGrid;
use Epicor\OrderApproval\Model\ResourceModel\Groups\CollectionFactory as GroupCollectionFactory;
use Epicor\OrderApproval\Block\Group\AbstractGroupBlock as GroupAbstractBlock;
use Epicor\OrderApproval\Model\GroupSave\Customers as GroupCustomers;
use Epicor\OrderApproval\Model\GroupSave\ErpAccount as GroupErpAccount;

/**
 * Class Grid
 * @package Epicor\OrderApproval\Block\Group\Hierarchy\Parent
 */
class Grid extends GroupAbstractBlock
{
    /**
     * @var array
     */
    private $selectedIds = array();

    /**
     * @var GroupCollectionFactory
     */
    protected $groupCollectionFactory;

    /**
     * @var array
     */
    private $selected;

    /**
     * @var GroupCustomers
     */
    private $groupCustomers;

    /**
     * @var GroupErpAccount
     */
    private $groupErpAccount;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory
     * @param \Epicor\Common\Helper\Data $commonHelper
     * @param \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper
     * @param \Epicor\OrderApproval\Model\GroupsRepository $groupsRepository
     * @param \Magento\Customer\Model\Session $customerSession
     * @param GroupCollectionFactory $groupCollectionFactory
     * @param ApprovalGroups $groups
     * @param GroupCustomers $groupCustomers
     * @param GroupErpAccount $groupErpAccount
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\OrderApproval\Model\GroupsRepository $groupsRepository,
        \Magento\Customer\Model\Session $customerSession,
        GroupCollectionFactory $groupCollectionFactory,
        ApprovalGroups $groups,
        GroupCustomers $groupCustomers,
        GroupErpAccount $groupErpAccount,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $groupsRepository,
            $customerSession,
            $groupCollectionFactory,
            $groups,
            $data
        );
        $this->groupCustomers = $groupCustomers;
        $this->setId('parent_grid');
        $this->setIdColumn('id');
        $this->setSaveParametersInSession(false);
        $this->setMessageBase('epicor_comm');
        $this->setCustomColumns($this->_getColumns());
        $this->setCacheDisabled(true);
        $this->setUseAjax(true);
        $this->setFilterVisibility(true);
        $this->setDefaultFilter(array('selected_groups' => 1));
        $this->groupErpAccount = $groupErpAccount;
    }

    /**
     * @return \Epicor\Common\Block\Generic\Listing\Grid|void
     */
    protected function _prepareCollection()
    {
        $erpId = $this->customerSession->getCustomer()->getData('ecc_erpaccount_id');

        /** @var \Epicor\OrderApproval\Model\ResourceModel\Groups\Collection $collection */
        $collection = $this->groupCollectionFactory->create();
        $collection->addFieldToSelect('*');
        $collection->getSelect()
            ->joinLeft(['agr' => 'ecc_approval_group_link'], 'agr.group_id = main_table.group_id', []);
        $selectedParents = array_values($this->getSelected());

        $this->groupErpAccount->addMasterShopperErpAccountFilter($collection, $selectedParents);
        if ($groupId = $this->getGroupId()) {
            $collection->addFieldToFilter(['main_table.group_id'], [['neq' => $groupId]]);
        }

        $this->setCollection($collection);
        WidgetGrid::_prepareCollection();
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        return parent::_toHtml();
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string|void
     */
    public function getRowUrl($row)
    {
        return '';
    }

    /**
     * @return array[]
     */
    protected function _getColumns()
    {
        return [
            'selected_groups' => array(
                'header' => 'Select',
                'header_css_class' => 'a-center',
                'index' => 'group_id',
                'type' => 'radio',
                'name' => 'selected_groups',
                'html_name' => 'groupselect',
                'values' => $this->getSelected(),
                'align' => 'center',
                'filter_index' => 'main_table.entity_id',
                'renderer' => '\Epicor\OrderApproval\Block\Group\Hierarchy\Renderer\Radio',
                'disabled_values' => $this->getDisabledFields(),
                'sortable' => false,
                'field_name' => 'links[]'
            ),
            'group_id' => array(
                'header' => __('Group Id'),
                'name' => 'group_id',
                'type' => 'number',
                'index' => 'group_id'
            ),
            'name' => array(
                'header' => __('Group Name'),
                'index' => 'name',
                'type' => 'text',
                'condition' => 'LIKE',
            ),
        ];
    }

    /**
     * Used in grid to return selected Products values.
     * 
     * @return array
     */
    protected function _getSelected()
    {
        return array_keys($this->getSelected());
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getDisabledFields()
    {
        if (!$this->groupCustomers->isEditableByCustomer()) {
            return $this->getAllGroups();
        }
        parent::getDisabledFields();
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
                $this->selectedIds[$id] = array('id' => $id);
            }
        }
    }

    /**
     * Builds the array of selected customers
     * 
     * @return array
     */
    public function getSelected()
    {
        if (!$this->selected) {
            /** @var \Epicor\OrderApproval\Model\ResourceModel\Groups\Collection $collection */
            $collection = $this->groupCollectionFactory->create();
            $collection->addFieldToSelect('*');
            $collection->getSelect()
                ->joinLeft(['agr' => 'ecc_approval_group_link'], 'agr.group_id = main_table.group_id', ['*']);
            $collection->addFieldToFilter('agr.group_id', ['eq' => $this->getGroupId()]);
            $this->selected = [];
            foreach ($collection as $result) {
                $this->selected[$result->getData('parent_group_id')] = $result->getData('parent_group_id') ?? '';
            }
        }

        return $this->selected;
    }

    /**
     * @param WidgetGrid\Column $column
     * @return $this|Grid
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addColumnFilterToCollection($column)
    {

        if ($column->getId() == 'selected_groups') {
            $ids = $this->_getSelected();
            if (empty($ids)) {
                $ids = array(0);
            }

            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter(['main_table.group_id'], [['in' => $ids]]);
            } else if ($ids) {
                $this->getCollection()->addFieldToFilter(['main_table.group_id'], [['nin' => $ids]]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/parentgrid', array('_current' => true));
    }

}
