<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Adminhtml\Customer\Salesrep\Edit\Tab;


class Salesreps extends \Magento\Backend\Block\Widget\Grid\Extended  implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    private $_selected = array();

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerResourceModelCustomerCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $storeSystemStore;

    private $_salesrep;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\System\Store $storeSystemStore,
        array $data = []
    )
    {
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        $this->registry = $registry;
        $this->storeSystemStore = $storeSystemStore;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('salesrepGrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);
        $this->setDefaultFilter(array('selected_salesreps' => 1));
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'selected_salesreps') {

            $productIds = $this->_getSelected();

            if (!empty($productIds)) {
                if ($column->getFilter()->getValue()) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
                } else {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $productIds));
                }
            } else {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => array(0)));
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    public function getGroupCustomers()
    {
        $collection = $this->customerResourceModelCustomerCollectionFactory->create()
            ->addFieldToFilter('ecc_sales_rep_account_id', $this->getSalesRepAccount()->getId());
        /* @var $collection \Magento\Customer\Model\ResourceModel\Customer\Collection */
        $collection->addNameToSelect();
        return $collection->getItems();
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

    /**
     * 
     * @return \Epicor\SalesRep\Model\Account
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
        $collection = $this->customerResourceModelCustomerCollectionFactory->create();
        $collection->addNameToSelect();
        $collection->addAttributeToFilter('ecc_erp_account_type', 'salesrep');

        $this->setCollection($collection);

        return parent::_prepareCollection();
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
            'index' => 'entity_id',
            'sortable' => false,
            'field_name' => 'links[]',
            'use_index' => true
        ));

        $this->addColumn('salesrep_name', array(
            'header' => __('Customer'),
            'width' => '150',
            'index' => 'name',
            'filter_index' => 'name'
        ));

        $this->addColumn('salesrep_email', array(
            'header' => __('Email'),
            'width' => '150',
            'index' => 'email',
            'filter_index' => 'email'
        ));

        //M1 > M2 Translation Begin (Rule P2-6.8)
        //if (!Mage::app()->isSingleStoreMode()) {
        if (!$this->_storeManager->isSingleStoreMode()) {
            //M1 > M2 Translation End
            $this->addColumn('website_id', array(
                'header' => __('Website'),
                'align' => 'center',
                'width' => '80px',
                'type' => 'options',
                'options' => $this->storeSystemStore->getWebsiteOptionHash(true),
                'index' => 'website_id',
            ));
        }

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
            if ($this->getSalesRepAccount()->getId()) {
                $collection = $this->customerResourceModelCustomerCollectionFactory->create();

                $collection->addFieldToFilter('ecc_sales_rep_account_id', $this->getSalesRepAccount()->getId());

                /* @var $collection \Magento\Customer\Model\ResourceModel\Customer\Collection */
                foreach ($collection->getAllIds() as $id) {
                    $this->_selected[$id] = array('id' => $id);
                }
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
        return $this->getUrl('adminhtml/epicorsalesrep_customer_salesrep/salesrepsgrid', $params);
    }

}
