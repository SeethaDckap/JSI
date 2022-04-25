<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Adminhtml\Customer\Salesrep\Edit\Tab;


use Magento\Config\Model\ResourceModel\Config\Data\Collection;

class Erpaccounts extends \Magento\Backend\Block\Widget\Grid\Extended  implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    private $_selected = array();

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory
     */
    protected $commResourceCustomerErpaccountCollectionFactory;

    private $_salesrep;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory
     */
    protected $configcollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $configcollectionFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $commResourceCustomerErpaccountCollectionFactory,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->configcollectionFactory = $configcollectionFactory;
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
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag

        if ($column->getId() == 'selected_erpaccounts') {
            $ids = $this->_getSelected();
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('main_table.entity_id', array('in' => $ids));
            } else {
                $this->getCollection()->addFieldToFilter('main_table.entity_id', array('nin' => $ids));
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    public function canShowTab()
    {
        return true;
    }

    public function getTabLabel()
    {
        return 'Erp Accounts';
    }

    public function getTabTitle()
    {
        return 'Erp Accounts';
    }

    public function isHidden()
    {
        return false;
    }

    /**
     * 
     * @return \Epicor\SalesRep\Model\Location
     */
    public function getSalesRepAccount()
    {
        if (!$this->_salesrep) {
            $this->_salesrep = $this->registry->registry('salesrep_account');
        }

        return $this->_salesrep;
    }


    protected function _prepareCollection()
    {
        $collection = $this->commResourceCustomerErpaccountCollectionFactory->create();
        /* @var $collection \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Collection */

        $collection->addFieldToFilter('account_type', array('neq' => 'Supplier'));
        //M1 > M2 Translation Begin (Rule p2-1)
        //$allStoresDefaultErpAccounts = Mage::getModel('core/config_data')->addFieldToFilter('path', array('eq' => 'customer/create_account/default_erpaccount')); // get default erp accounts for all stores
        $allStoresDefaultErpAccounts = $this->configcollectionFactory->create()->addFieldToFilter('path', array('eq' => 'customer/create_account/default_erpaccount'));
        //M1 > M2 Translation End

        $allDefaultErpValues = array();
        foreach ($allStoresDefaultErpAccounts as $eachStoreErpAccounts) {
            $allDefaultErpValues[] = $eachStoreErpAccounts->getValue();
        }

        // only include if not default erp account
        $collection->addFieldToFilter('entity_id', array('nin' => $allDefaultErpValues));

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
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
        return $this->getUrl('adminhtml/epicorsalesrep_customer_salesrep/erpaccountsgrid', $params);
    }

    public function getRowUrl($row)
    {
        return null;
    }

}
