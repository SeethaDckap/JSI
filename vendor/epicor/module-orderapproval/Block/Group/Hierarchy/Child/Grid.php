<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\OrderApproval\Block\Group\Hierarchy\Child;

use Epicor\OrderApproval\Model\Groups as ApprovalGroups;
use Magento\Backend\Block\Widget\Grid as WidgetGrid;
use Epicor\OrderApproval\Model\ResourceModel\Groups\CollectionFactory as GroupCollectionFactory;
use Epicor\OrderApproval\Block\Group\AbstractGroupBlock as GroupAbstractBlock;
use Epicor\OrderApproval\Model\GroupSave\Customers as GroupCustomers;
use Epicor\OrderApproval\Model\GroupSave\ErpAccount as GroupErpAccount;

/**
 * Class Grid
 * @package Epicor\OrderApproval\Block\Group\Hierarchy\Child
 */
class Grid extends GroupAbstractBlock
{
    /**
     * @var \Magento\Framework\Session\Generic
     */
    private $generic;

    /**
     * @var GroupCollectionFactory
     */
    protected $groupCollectionFactory;

    /**
     * @var int[]
     */
    private $selected;
    /**
     * @var GroupCustomers
     */
    private $groupCustomers;

    /**
     * @var GroupErpAccount
     */
    private $groupErpCustomers;

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
     * @param GroupErpAccount $groupErpCustomers
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
        GroupErpAccount $groupErpCustomers,
        array $data = []
    ) {
        $this->generic = $context->getSession();
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
        $this->setId('child_grid');
        $this->setIdColumn('id');
        $this->setSaveParametersInSession(false);
        $this->setCustomColumns($this->_getColumns());
        $this->setCacheDisabled(true);
        $this->setUseAjax(true);
        $this->setFilterVisibility(true);
        $this->setDefaultFilter(array('selected_groups' => 1));
        $this->groupErpCustomers = $groupErpCustomers;
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
        $collection->addFieldToFilter(['agr.group_id', 'agr.parent_group_id'], [
            ['null' => true],
            ['eq' => $this->getGroupId()]
        ]);
        $selectedChildren = array_values($this->getSelected());
        $this->groupErpCustomers->addMasterShopperErpAccountFilter($collection, $selectedChildren);
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
                'type' => 'checkbox',
                'name' => 'selected_groups',
                'values' => $this->getSelected(),
                'align' => 'center',
                'filter_index' => 'main_table.group_id',
                'disabled_values' => $this->getDisabledFields(),
                'sortable' => false,
                'field_name' => 'child_groups[]'
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
                'type' => 'text'
            ),
        ];
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
     * Builds the array of selected groups based on selected group
     *
     * @return array
     */
    public function getSelected()
    {
        $groupId = $this->getGroupId();

        if (!$this->selected && $groupId) {
            /** @var \Epicor\OrderApproval\Model\ResourceModel\Groups\Collection $collection */
            $collection = $this->groupCollectionFactory->create();
            $collection->addFieldToSelect('group_id');
            $collection->getSelect()
                ->joinLeft(['agr' => 'ecc_approval_group_link'], 'agr.group_id = main_table.group_id', []);
            $collection->addFieldToFilter('agr.parent_group_id', ['eq' => $groupId]);
            $this->selected = [];
            foreach ($collection as $result) {
                $this->selected[] = $result['group_id'] ?? '';
            }
        }

        return $this->selected;
    }

    /**
     * @param WidgetGrid\Column $column
     * @return Grid|void
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'selected_groups') {
            $this->setChildGroupFilter();
        } else {
            parent::_addColumnFilterToCollection($column);
        }
    }

    /**
     * @return $this
     */
    private function setChildGroupFilter()
    {
        /** @var \Epicor\OrderApproval\Model\ResourceModel\Groups\Collection $collection */
        if ($collection = $this->getCollection()) {
            $collection->addFieldToFilter('agr.parent_group_id', ['eq' => $this->getGroupId()]);
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/childgrid', array('_current' => true));
    }
}
