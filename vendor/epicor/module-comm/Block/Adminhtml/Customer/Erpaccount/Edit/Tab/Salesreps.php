<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab;


class Salesreps extends \Magento\Backend\Block\Widget\Grid\Extended implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    private $_selected = array();
    protected $_erp_customer;

    /**
     * @var \Epicor\SalesRep\Model\ResourceModel\Account\CollectionFactory
     */
    protected $salesRepResourceAccountCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\SalesRep\Model\ResourceModel\Erpaccount\CollectionFactory
     */
    protected $salesRepResourceErpaccountCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\SalesRep\Model\ResourceModel\Account\CollectionFactory $salesRepResourceAccountCollectionFactory,
        \Magento\Framework\Registry $registry,
        \Epicor\SalesRep\Model\ResourceModel\Erpaccount\CollectionFactory $salesRepResourceErpaccountCollectionFactory,
        array $data = []
    )
    {
        $this->salesRepResourceAccountCollectionFactory = $salesRepResourceAccountCollectionFactory;
        $this->registry = $registry;
        $this->salesRepResourceErpaccountCollectionFactory = $salesRepResourceErpaccountCollectionFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('salesRepGrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);
        $this->setDefaultFilter(array('selected_salesreps' => 1));
    }

    protected function _prepareCollection()
    {
        $collection = $this->salesRepResourceAccountCollectionFactory->create();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in salesreps flag
        if ($column->getId() == 'selected_salesreps') {
            $salesrepIds = $this->_getSelected();

            if (empty($salesrepIds)) {
                $salesrepIds = array(0);
            }

            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('id', array('in' => $salesrepIds));
            } else {
                if ($salesrepIds) {
                    $this->getCollection()->addFieldToFilter('id', array('nin' => $salesrepIds));
                }
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
        return 'Sales Reps';
    }

    public function getTabTitle()
    {
        return 'Sales Reps';
    }

    public function isHidden()
    {
        return false;
    }

    public function getErpCustomer()
    {
        if (!$this->_erp_customer) {
            $this->_erp_customer = $this->registry->registry('customer_erp_account');
        }
        return $this->_erp_customer;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('selected_salesreps', array(
            'header' => __('Select'),
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'selected_salesreps',
            'values' => $this->_getSelected(),
            'align' => 'center',
            'index' => 'id',
            'filter_index' => 'id',
            'sortable' => false,
            'field_name' => 'links[]'
        ));

        $this->addColumn('sales_rep_id', array(
            'header' => __('Sales Rep Account Number'),
            'align' => 'left',
            'index' => 'sales_rep_id',
            'filter_index' => 'sales_rep_id',
        ));

        $this->addColumn('name', array(
            'header' => __('Name'),
            'align' => 'left',
            'index' => 'name',
            'filter_index' => 'name',
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

    protected function _getSelected()
    {   // Used in grid to return selected customers values.
        return array_keys($this->getSelected());
    }

    public function getSelected()
    {
        if (empty($this->_selected) && $this->getRequest()->getParam('ajax') !== 'true') {

            $collection = $this->salesRepResourceErpaccountCollectionFactory->create()->getSalesRepAccountsByErpAccount($this->getErpCustomer()->getId());

            foreach ($collection as $salesRep) {
                $this->_selected[$salesRep->getSalesRepAccountId()] = array('id' => $salesRep->getSalesRepAccountId());
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
            'id' => $this->getErpCustomer()->getId(),
            '_current' => true,
        );
        return $this->getUrl('adminhtml/epicorcomm_customer_erpaccount/salesrepsgrid', $params);
    }

    public function getRowUrl($row)
    {
        return null;
    }

}
