<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\OrderApproval\Block\Group\Listing;

use Epicor\OrderApproval\Model\GroupSave\Customers as GroupCustomers;
use Epicor\OrderApproval\Model\GroupSave\Rules as GroupRules;
use Epicor\OrderApproval\Model\GroupSave\ErpAccount as GroupErpAccount;

/**
 * Class Grid
 * @package Epicor\OrderApproval\Block\Group\Listing
 */
class Grid extends \Epicor\Common\Block\Widget\Grid\Extended
{
    /**
     * @var \Epicor\Comm\Helper\Data
     */
    private $commHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    private $commonHelper;

    /**
     * @var string
     */
    protected $_template = 'Epicor_Common::widget/grid/extended.phtml';

    /**
     * @var \Epicor\OrderApproval\Model\ResourceModel\Groups\CollectionFactory
     */
    private $approvalGroupsCollectionFactory;

    /**
     * @var GroupCustomers
     */
    private $groupCustomers;

    /**
     * @var GroupRules
     */
    private $groupRules;

    /**
     * @var GroupErpAccount
     */
    private $groupErpAccount;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    private $accessauthorization;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Epicor\Comm\Helper\Data $commHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Epicor\Common\Helper\Data $commonHelper
     * @param \Epicor\OrderApproval\Model\ResourceModel\Groups\CollectionFactory $approvalGroupsCollectionFactory
     * @param GroupCustomers $groupCustomers
     * @param GroupRules $groupRules
     * @param GroupErpAccount $groupErpAccount
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Common\Helper\Data $commonHelper,
        \Epicor\OrderApproval\Model\ResourceModel\Groups\CollectionFactory $approvalGroupsCollectionFactory,
        GroupCustomers $groupCustomers,
        GroupRules $groupRules,
        GroupErpAccount $groupErpAccount,
        array $data = []
    ) {
        $this->commHelper = $commHelper;
        $this->customerSession = $customerSession;
        $this->storeManager = $context->getStoreManager();
        $this->commonHelper = $commonHelper;
        $this->accessauthorization = $context->getAccessAuthorization();

        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('listgrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setFilterVisibility(true);
        $this->setNoFilterMassactionColumn(true);

        $this->approvalGroupsCollectionFactory = $approvalGroupsCollectionFactory;
        $this->groupCustomers = $groupCustomers;
        $this->groupRules = $groupRules;
        $this->groupErpAccount = $groupErpAccount;
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        /** @var \Epicor\OrderApproval\Model\ResourceModel\Groups\Collection $collection */
        $collection = $this->approvalGroupsCollectionFactory->create();
        if ($this->isGroupsAvailable()) {
            $this->joinApprovalValuesTable($collection);
            $this->groupErpAccount->addMasterShopperErpAccountFilter($collection);
        }
        $collection->clear();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return bool
     */
    private function isGroupsAvailable()
    {
        return !empty($this->getGroupIds());
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return '';
    }

    /**
     * @return array
     */
    private function getGroupIds()
    {
        $collection = $this->approvalGroupsCollectionFactory->create();
        return $collection->getAllIds();
    }

    /**
     * @param $collection
     */
    private function joinApprovalValuesTable($collection)
    {
        $this->setTempTableOfApprovalAmounts($collection);
        $collection->getSelect()
            ->joinLeft(
                ['apl' => 'tmp_tbl_approval_limits'],
                'apl.group_id = main_table.group_id',
                ['apl.approval_limit']
            );
    }

    /**
     * @param $collection
     */
    private function setTempTableOfApprovalAmounts($collection)
    {
        $approvalInsertData = $this->buildInsertApprovalValues($collection);
        $this->createTemporaryTable($collection);

        $insertApprovalSql = "INSERT INTO tmp_tbl_approval_limits VALUES $approvalInsertData;";
        $collection->getResource()->getConnection()->query($insertApprovalSql);
    }

    /**
     * @param $collection
     */
    private function createTemporaryTable($collection)
    {
        $createTempTableSql = "CREATE TEMPORARY TABLE tmp_tbl_approval_limits (
                                  `group_id` int(10) unsigned NOT NULL,
                                  `approval_limit` float DEFAULT NULL
                               )";
        $collection->getResource()->getConnection()->query($createTempTableSql);
    }

    /**
     * @param $collection
     * @return string
     */
    private function buildInsertApprovalValues($collection)
    {
        $approvalData = '';
        /** @var \Epicor\OrderApproval\Model\ResourceModel\Groups\Collection $collection */
        $first = true;
        foreach ($collection as $group) {
            $rules = $group->getRules();
            $approvalLimit = $this->groupRules->getApprovalLimitFromRule($rules);
            if (!$approvalLimit) {
                $approvalLimit = 0;
            }
            if ($first) {
                $approvalData .= '(' . $group->getGroupId() . ',' . $approvalLimit . ')';
                $first = false;
            } else {
                $approvalData .= ',(' . $group->getGroupId() . ',' . $approvalLimit . ')';
            }
        }

        return $approvalData;
    }

    /**
     * @return Grid
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        if ($this->accessauthorization->isAllowed('Epicor_Customer::my_account_group_create')) {
            $urlRedirect = $this->getUrl('*/*/new', array(
                '_current' => true,
                'contract' => $this->getRequest()->getParam('contract'),
            ));
            $this->setChild('add_button', $this->getLayout()
                ->createBlock('Magento\Backend\Block\Widget\Button')
                ->setData(array(
                    'label'   => __('Add New Group'),
                    'onclick' => "location.href='$urlRedirect';",
                    'class'   => 'task',
                )));
        }

        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * @return string
     */
    public function getMainButtonsHtml()
    {
        $html = parent::getMainButtonsHtml();
        $html .= $this->getAddButtonHtml();
        return $html;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        return parent::_toHtml();
    }

    /**
     * @return Grid
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('name', [
            'header' => __('Group Name'),
            'index' => 'name',
            'type' => 'text'
        ]);
        $this->addColumn('is_multi_level', [
            'header' => __('Multi Level'),
            'index' => 'is_multi_level',
            'renderer' => '\Epicor\OrderApproval\Block\Group\Grid\Renderer\MultiLevel',
            'type' => 'text'
        ]);
        $this->addColumn('approval_limit', [
            'header' => __('Order Amount'),
            'index' => 'approval_limit',
            'sortable' => true,
            'type' => 'text'
        ]);
        $this->addColumn('is_active', [
            'header' => __('Active'),
            'renderer' => '\Epicor\OrderApproval\Block\Group\Grid\Renderer\Active',
            'index' => 'is_active',
            'type' => 'text'
        ]);
        $this->addColumn('created_by', [
            'header' => __('Created By'),
            'index' => 'created_by',
            'type' => 'text'
        ]);

        $actions = [];
        $actions[]=[
            'caption' => __('Edit'),
            'type' => 'edit',
            'url' => ['base' => '*/*/edit'],
            'field' => 'id'
        ];

        $actions[]=  [
            'caption' => __('view'),
            'type' => 'view',
            'url' => ['base' => '*/*/view'],
            'field' => 'id'
        ];

        if ($this->accessauthorization->isAllowed('Epicor_Customer::my_account_group_delete')) {
            $actions[]=  [
                'caption' => __('Delete'),
                'type' => 'delete',
                'url' => ['base' => '*/*/delete'],
                'field' => 'id'
            ];
        }




        $this->addColumn('action', [
            'header' => __('Action'),
            'width' => '100',
            'renderer' => '\Epicor\OrderApproval\Block\Group\Grid\Renderer\Action',
            'links' => true,
            'getter' => 'getId',
            'actions' => $actions,
            'filter' => false,
            'sortable' => false,
            'index' => 'id',
            'is_system' => true,
        ]);


        return parent::_prepareColumns();
    }

    /**
     * Overcomes issue magento setOrder results in sql error when using field 'order'
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return Grid
     */
    protected function _setCollectionOrder($column)
    {
        $columnIndex = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
        $dir = $column->getDir();
        /** @var \Epicor\OrderApproval\Model\ResourceModel\Groups\Collection $collection */
        $collection = $this->getCollection();
        if ($columnIndex === 'order' && $collection && $dir) {
            $collection->getSelect()->order("main_table.order $dir");
        } else {
            return parent::_setCollectionOrder($column);
        }
    }

    /**
     * @return $this|Grid
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _prepareMassaction()
    {
        if (!$this->accessauthorization->isAllowed('Epicor_Customer::my_account_group_delete')) {
            return $this;
        }

        $this->setMassactionIdField('group_id');
        $this->getMassactionBlock()->setFormFieldName('groupid');
        $this->getMassactionBlock()->setData('use_select_all', false);

        if ($this->accessauthorization->isAllowed('Epicor_Customer::my_account_group_delete')) {
            $this->getMassactionBlock()->addItem('delete', array(
                'label' => __('Delete'),
                'url' => $this->getUrl('*/*/massDelete'),
                'confirm' => __('Delete selected Groups?')
            ));
        }

        return $this;
    }

    /**
     * Gets grid url for ajax reloading
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/groupgrid', array(
            '_current' => true
        ));
    }

    /**
     * @param $collection
     * @param $column
     * @return $this
     */
    public function _prepareMassactionBlock()
    {
        $result = parent::_prepareMassactionBlock();
        $massActionColumn = $this->getColumn('massaction');
        if($massActionColumn) {
            $massActionColumn->setData('renderer', '\Epicor\OrderApproval\Block\Group\Listing\Renderer\Massaction');
            $massActionColumn->setData('disabled_values', $this->getDisabledValues());
        }


        return $result;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getDisabledValues()
    {
        $groupsCollection = $this->approvalGroupsCollectionFactory->create();
        $ids = $groupsCollection->getAllIds();
        $disabled = [];
        foreach ($ids as $id) {
            if (!$this->groupCustomers->isEditableByCustomer('id', $id)) {
                $disabled[] = $id;
            }
        }

        return $disabled;
    }

    /**
     * @param $collection
     * @param $column
     * @return $this
     */
    public function _statusFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        return $this;
    }

}
