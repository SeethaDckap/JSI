<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Adminhtml\Budgets\ErpAccounts\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended as GridExtended;
use Epicor\OrderApproval\Model\ResourceModel\ErpAccountBudget\CollectionFactory as ErpAccountBudgetCollectionFactory;
use Epicor\OrderApproval\Model\ResourceModel\ErpAccountBudget\Collection as BudgetCollection;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Helper\Data;

class Budget extends GridExtended implements TabInterface
{
    /**
     * @var ErpAccountBudgetCollectionFactory
     */
    private $erpAccountBudgetCollectionFactory;

    /**
     * @var bool $_filterVisibility
     */
    protected $_filterVisibility = false;

    /**
     * Budget constructor.
     * @param Context $context
     * @param Data $backendHelper
     * @param ErpAccountBudgetCollectionFactory $erpAccountBudgetCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        ErpAccountBudgetCollectionFactory $erpAccountBudgetCollectionFactory,
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
        $this->setSaveParametersInSession(false);
        $this->erpAccountBudgetCollectionFactory = $erpAccountBudgetCollectionFactory;
    }

    /**
     * @return Budget
     */
    protected function _prepareCollection()
    {
        try {
            /** @var BudgetCollection $collection */
            $collection = $this->erpAccountBudgetCollectionFactory->create();
            $collection->addFieldToFilter('erp_id', $this->getErpId());
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
    private function getErpId()
    {
        return $this->getRequest()->getParam('erp_id');
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
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                ),
                array(
                    'caption' => __('Delete'),
                    'url' => array('base' => '*/*/delete'),
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
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowClass($row = null)
    {
        return '_clickable';
    }
}
