<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab;


/**
 * Erp account Customers grid
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Customers extends \Magento\Backend\Block\Widget\Grid\Extended implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    private $_selected = array();
    protected $_erp_customer;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerResourceModelCustomerCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    )
    {
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        $this->registry = $registry;
        $this->moduleManager = $moduleManager;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('customerGrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);
        $this->setDefaultFilter(array('in_customer' => 1));
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_customer') {
            $productIds = $this->_getSelected();
            if (empty($productIds)) {
                $productIds = array(0);
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $productIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    public function getGroupCustomers()
    {
        $collection = $this->customerResourceModelCustomerCollectionFactory->create()
            ->addFieldToFilter('ecc_erpaccount_id', $this->getErpCustomer()->getId());
        $collection->addNameToSelect();
        return $collection->getItems();
    }

    public function canShowTab()
    {
        return true;
    }

    public function getTabLabel()
    {
        return 'Customer SKU';
    }

    public function getTabTitle()
    {
        return 'Customer SKU';
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

    protected function _prepareCollection()
    {
        $collection = $this->customerResourceModelCustomerCollectionFactory->create();
        $collection->addNameToSelect();
        $collection->addAttributeToSelect('ecc_master_shopper');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('in_customer', array(
            'header' => __('Select'),
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'in_customer',
            'values' => $this->_getSelected(),
            'align' => 'center',
            'index' => 'entity_id',
            'sortable' => false,
            'field_name' => 'links[]'
        ));

        $this->addColumn('name', array(
            'header' => __('Customer'),
            'index' => 'name',
            'filter_index' => 'name'
        ));

        $this->addColumn('email', array(
            'header' => __('Email'),
            'width' => '150',
            'index' => 'email',
            'filter_index' => 'email'
        ));
        //Hide the Master Shopper Column (If the Account Type is Supplier)
        if (!$this->getErpCustomer()->isTypeSupplier()) {
            $this->addColumn('ecc_master_shopper', array(
                'header' => __('Master Shopper'),
                'width' => '50',
                'index' => 'ecc_master_shopper',
                'align' => 'center',
                'type' => 'options',
                'options' => array('1' => 'Yes', '0' => 'No')
            ));
        }
        $this->addColumn('action', array(
            'header' => __('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => __('Edit'),
                    'url' => array('base' => 'customer/index/edit',
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
            if ($this->moduleManager->isEnabled('Epicor_Supplierconnect')) {
                if ($this->getErpCustomer()->isTypeSupplier()) {
                    $collection->addFieldToFilter('ecc_supplier_erpaccount_id', $this->getErpCustomer()->getId());
                } else {
                    $collection->addFieldToFilter('ecc_erpaccount_id', $this->getErpCustomer()->getId());
                }
            } else {
                $collection->addFieldToFilter('ecc_erpaccount_id', $this->getErpCustomer()->getId());
            }

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
            'id' => $this->getErpCustomer()->getId(),
            '_current' => true,
        );
        return $this->getUrl('adminhtml/epicorcomm_customer_erpaccount/customersgrid', $params);
    }

}
