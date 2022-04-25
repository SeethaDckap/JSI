<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Group\Budgets;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended as GridExtended;
use Epicor\OrderApproval\Model\ResourceModel\Groups\Budget\CollectionFactory as BudgetCollectionFactory;
use Epicor\OrderApproval\Model\ResourceModel\Groups\Budget\Collection as BudgetCollection;
use Epicor\OrderApproval\Model\Groups\Budget;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Helper\Data;
use Epicor\OrderApproval\Model\GroupSave\Customers as GroupCustomers;

class BudgetGrid extends GridExtended implements TabInterface
{
    /**
     * @var BudgetCollectionFactory
     */
    private $budgetCollectionFactory;

    /**
     * @var bool $_filterVisibility
     */
    protected $_filterVisibility = false;

    /**
     * @var GroupCustomers
     */
    private $groupCustomers;

    /**
     * BudgetGrid constructor.
     * @param GroupCustomers $groupCustomers
     * @param Context $context
     * @param Data $backendHelper
     * @param BudgetCollectionFactory $budgetCollectionFactory
     * @param array $data
     */
    public function __construct(
        GroupCustomers $groupCustomers,
        Context $context,
        Data $backendHelper,
        BudgetCollectionFactory $budgetCollectionFactory,
        array $data = []
    ) {

        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('erpaccount_budget');
        $this->setUseAjax(true);
        $this->_pagerVisibility = false;
        $this->setDefaultSort('start_datestamp');
        $this->setDefaultDir('desc');
        $this->setTemplate('Epicor_Common::widget/grid/extended.phtml');
        $this->setSaveParametersInSession(false);
        $this->budgetCollectionFactory = $budgetCollectionFactory;
        $this->groupCustomers = $groupCustomers;
    }

    /**
     * @return BudgetGrid
     */
    protected function _prepareCollection()
    {
        try {
            /** @var BudgetCollection $collection */
            $collection = $this->budgetCollectionFactory->create();
            $collection->addFieldToFilter('group_id', $this->getGroupId());
        } catch (\Exception $e) {
             $e->getMessage();
        }


        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return mixed|null
     * @throws \Magento\Framework\Exception\SessionException
     */
    private function getGroupId()
    {
        return $this->getRequest()->getParam('id');
    }

    /**
     * @return Budget
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('id', [
            'header' => __('Budget ID'),
            'align' => 'left',
            'index' => 'id',
            'sortable' => false,
            'filter' => false,
        ]);

        $this->addColumn('type', [
            'header' => __('Type'),
            'align' => 'left',
            'index' => 'type',
            'sortable' => false,
            'filter' => false,
            'renderer' => 'Epicor\OrderApproval\Block\Adminhtml\Budgets\ErpAccounts\Edit\Tab\Renderer\Type'
        ]);

        $this->addColumn('start_date', [
            'header' => __('Start Date'),
            'align' => 'left',
            'index' => 'start_date',
            'type' => 'datetime',
            'sortable' => false,
            'filter' => false,
            'renderer' => 'Epicor\OrderApproval\Block\Adminhtml\Budgets\ErpAccounts\Edit\Tab\Renderer\StartDate'
        ]);

        $this->addColumn('end_date', [
            'header' => __('End Date'),
            'align' => 'left',
            'type' => 'datetime',
            'sortable' => false,
            'filter' => false,
            'renderer' => 'Epicor\OrderApproval\Block\Adminhtml\Budgets\ErpAccounts\Edit\Tab\Renderer\EndDate',
        ]);

        $this->addColumn('amount', [
            'header' => __('Amount'),
            'align' => 'left',
            'index' => 'amount',
            'sortable' => false,
            'filter' => false,
            'renderer' => 'Epicor\OrderApproval\Block\Adminhtml\Budgets\ErpAccounts\Edit\Tab\Renderer\Amount',
        ]);

        $this->addColumn('action', array(
            'header' => __('Action'),
            'width' => '100',
            'renderer' => 'Epicor\OrderApproval\Block\Adminhtml\Budgets\ErpAccounts\Edit\Tab\Renderer\Action',
            'links' => 'true',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => __('Edit'),
                    'url' => array('base' => '*/budgets/edit'),
                    'field' => 'id'
                ),
                array(
                    'caption' => __('Delete'),
                    'url' => array('base' => '*/budgets/delete'),
                    'field' => 'id',
                    'confirm' => __('Are you sure you want to delete this Role? This cannot be undone')
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'id',
        ));


        return parent::_prepareColumns();
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Return row url for js event handlers
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $item
     * @return string
     */
    public function getRowUrl($item)
    {
        return '';
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return 'Get Tab Label Budget Information';
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return 'get Tab Title';
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('orderapproval/budgets_erpaccounts/budgetgrid', ['grid_only' =>  1]);
    }

    /**
     * @param null $row
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRowClass($row = null)
    {
        if ($this->groupCustomers->isEditableByCustomer()) {
            return '_clickable';
        }
    }
}
