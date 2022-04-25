<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Adminhtml\Customer\Salesrep\Edit\Tab;


class Messagelog extends \Magento\Backend\Block\Widget\Grid implements \Magento\Backend\Block\Widget\Tab\TabInterface
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

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        $this->registry = $registry;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('salesrepGrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_salesrep') {

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
            ->addFieldToFilter('salesrep_id', $this->getSalesRepAccount()->getId());
        /* @var $collection Mage_Customer_Model_Resource_Customer_Collection */
        $collection->addNameToSelect();
        return $collection->getItems();
    }

    public function canShowTab()
    {
        return true;
    }

    public function getTabLabel()
    {
        return 'Message Log';
    }

    public function getTabTitle()
    {
        return 'Message Log';
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
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('in_salesrep', array(
            'header' => __('Select'),
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'in_salesrep',
            'values' => $this->_getSelected(),
            'align' => 'center',
            'index' => 'entity_id',
            'sortable' => false,
            'field_name' => 'links[]'
        ));

        $this->addColumn('name', array(
            'header' => __('Customer'),
            'width' => '150',
            'index' => 'name',
            'filter_index' => 'name'
        ));

        $this->addColumn('email', array(
            'header' => __('Email'),
            'width' => '150',
            'index' => 'email',
            'filter_index' => 'email'
        ));

        $this->addColumn('action', array(
            'header' => __('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => __('Edit'),
                    'url' => array('base' => 'adminhtml/customer/edit',
                        'params' => array('id' => $this->getRequest()->getParam('id'))
                    ),
                    'field' => 'id'
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
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
            $collection = $this->customerResourceModelCustomerCollectionFactory->create();

            if ($this->getSalesRepAccount()->getId()) {
                $collection->addFieldToFilter('salesrep_id', $this->getSalesRepAccount()->getId());
            }

            /* @var $collection Mage_Customer_Model_Resource_Customer_Collection */
            foreach ($collection->getAllIds() as $id) {
                $this->_selected[$id] = array('id' => $id);
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
        return $this->getUrl('adminhtml/epicorsalesrep_customer_salesreps/salesrepsgrid', $params);
    }

}
