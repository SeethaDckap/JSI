<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Account\Manage\Erpaccounts;


/**
 * Sales Rep Account ERP Accounts List
 * 
 * @category   Epicor
 * @package    Epicor_SalesRep
 * @author     Epicor Websales Team
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    private $_selected = array();
    protected $_salesrepChildrenIds;

    /**
     * @var \Epicor\SalesRep\Helper\Account\Manage
     */
    protected $salesRepAccountManageHelper;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory
     */
    protected $commResourceCustomerErpaccountCollectionFactory;

    /**
     * @var \Epicor\SalesRep\Block\Account\Manage\Erpaccounts\Renderer\SalesrepaccountFactory
     */
    protected $salesRepAccountManageErpaccountsRendererSalesrepaccountFactory;

    private $_salesrep;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\SalesRep\Helper\Account\Manage $salesRepAccountManageHelper,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $commResourceCustomerErpaccountCollectionFactory,
        \Epicor\SalesRep\Block\Account\Manage\Erpaccounts\Renderer\SalesrepaccountFactory $salesRepAccountManageErpaccountsRendererSalesrepaccountFactory,
        array $data = []
    )
    {
        $this->salesRepAccountManageErpaccountsRendererSalesrepaccountFactory = $salesRepAccountManageErpaccountsRendererSalesrepaccountFactory;
        $this->salesRepAccountManageHelper = $salesRepAccountManageHelper;
        $this->commResourceCustomerErpaccountCollectionFactory = $commResourceCustomerErpaccountCollectionFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('erpaccountGrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);

        $this->setDefaultSort('account_number');
        $this->setDefaultDir('DESC');
        $this->setDefaultFilter(array('selected_erpaccounts' => 1));
        $this->setTemplate('Epicor_Common::widget/grid/extended.phtml');

    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag

        if ($column->getId() == 'selected_erpaccounts') {
            $ids = $this->_getSelected();
            if (!empty($ids)) {
                if ($column->getFilter()->getValue()) {
                    $this->getCollection()->addFieldToFilter('main_table.entity_id', array('in' => $ids));
                } else {
                    $this->getCollection()->addFieldToFilter('main_table.entity_id', array('nin' => $ids));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * 
     * @return \Epicor\SalesRep\Model\Location
     */
    public function getSalesRepAccount()
    {
        if (!$this->_salesrep) {
            $helper = $this->salesRepAccountManageHelper;
            /* @var $helper \Epicor\SalesRep\Helper\Account\Manage */

            $salesRep = $helper->getManagedSalesRepAccount();
            $this->_salesrep = $salesRep;
        }

        return $this->_salesrep;
    }

    public function getSalesRepAccountChildrenIds()
    {
        if (!$this->_salesrepChildrenIds) {

            $salesRep = $this->getSalesRepAccount();
            $this->_salesrepChildrenIds = $salesRep->getHierarchyChildAccountsIds();
        }

        return $this->_salesrepChildrenIds;
    }

    /**
     * 
     * @return type
     */
    protected function _prepareCollection()
    {
        $collection = $this->commResourceCustomerErpaccountCollectionFactory->create();
        /* @var $collection Epicor_Comm_Model_Resource_Customer_Erpaccount_Collection */

        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper \Epicor\SalesRep\Helper\Account\Manage */

        $salesRep = $helper->getBaseSalesRepAccount();

        $erpAccounts = $salesRep->getMasqueradeAccountIds();
        $collection->addFieldToFilter('entity_id', array('in' => $erpAccounts));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper \Epicor\SalesRep\Helper\Account\Manage */

        if ($helper->canEdit()) {
            $this->addColumn('selected_erpaccounts', array(
                'header' => __('Select'),
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'selected_erpaccounts',
                'values' => $this->_getSelected(),
                'align' => 'center',
                'index' => 'entity_id',
                'sortable' => false,
                'field_name' => 'links[]',
                'use_index' => true
            ));
        }

        $this->addColumn('account_number', array(
            'header' => __('ERP Account Number'),
            'align' => 'left',
            'index' => 'account_number'
        ));

        $this->addColumn('erp_account_name', array(
            'header' => __('Name'),
            'align' => 'left',
            'index' => 'name'
        ));

        $this->addColumn('sales_rep_account', array(
            'header' => __('Sales Rep Account'),
            'current_account' => $this->getSalesRepAccount(),
            'account_children_ids' => $this->getSalesRepAccountChildrenIds(),
            //'messages' => $this->_messages,
            'messages' => '',
            'renderer' => 'Epicor\SalesRep\Block\Account\Manage\Erpaccounts\Renderer\Salesrepaccount',
            'filter_condition_callback' => array($this, '_salesRepAccountFilter'),
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

    protected function _getSelected()
    {   // Used in grid to return selected customers values.
        return array_keys($this->getSelected());
    }

    public function getSelected()
    {
        if (empty($this->_selected) && $this->getRequest()->getParam('ajax') !== 'true') {

            $salesRep = $this->getSalesRepAccount();

            $erpAccountIds = $salesRep->getErpAccountIds();

            foreach ($erpAccountIds as $erpAccountId) {
                $this->_selected[$erpAccountId] = array('entity_id' => $erpAccountId);
            }
        }
        return $this->_selected;
    }

    public function setSelected($selected)
    {
        if (!empty($selected)) {
            foreach ($selected as $id) {
                $this->_selected[$id] = array('id' => $id);
            }
        }
    }

    public function getGridUrl()
    {
        $params = array(
            'id' => $this->getSalesRepAccount()->getId(),
            '_current' => true,
        );
        return $this->getUrl('*/*/erpaccountsgrid', $params);
    }

    public function getRowUrl($row)
    {
        return null;
    }

    protected function _toHtml()
    {

        $html = parent::_toHtml();

        $html .= '<script>
        var FORM_KEY = "'.$this->getFormKey().'";
</script>';

        return $html;
    }

    protected function _salesRepAccountFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        $salesRepIds = $this->getSalesRepAccountChildrenIds();

        $this->getCollection()
            ->join(array('salesrep_erp' => 'ecc_salesrep_erp_account'), 'main_table.entity_id = salesrep_erp.erp_account_id', '')
            ->join(array('salesrep' => 'ecc_salesrep_account'), 'salesrep.id = salesrep_erp.sales_rep_account_id', '');
        $this->getCollection()->getSelect()->group('main_table.entity_id');

        if (strtolower($value) == strtolower(__('This account'))) {
            $salesRepAccount = $this->getSalesRepAccount();
            /* @var $salesRepAccount Epicor_SalesRep_Model_Account */
            $salesRepAccountId = $salesRepAccount->getId();
            $this->getCollection()->addFieldtoFilter('salesrep.id', $salesRepAccountId);
        } elseif (strtolower($value) == strtolower(__('Child account'))) {
            $childrenSalesRepAccountsIds = $this->getSalesRepAccountChildrenIds();
            $this->getCollection()->addFieldtoFilter('salesrep.id', array('in' => $childrenSalesRepAccountsIds));
        } elseif (strtolower($value) == strtolower(__('Multiple accounts'))) {
            $childrenSalesRepAccountsIds = $this->getSalesRepAccountChildrenIds();
            $this->getCollection()->addFieldtoFilter('salesrep.id', array('in' => $childrenSalesRepAccountsIds));
            $this->getCollection()->getSelect()->having('COUNT(*) > 1');
        } else {
            $this->getCollection()->addFieldtoFilter('salesrep.name', array('like' => "%$value%"));
        }

        return $this;
    }

}
