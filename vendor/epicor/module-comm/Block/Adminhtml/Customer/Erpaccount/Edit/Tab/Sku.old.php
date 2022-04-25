<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Customersku
 *
 * @author David.Wylie
 */
class Sku extends \Magento\Backend\Block\Widget\Grid implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Sku\CollectionFactory
     */
    protected $commResourceCustomerSkuCollectionFactory;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Model\ResourceModel\Customer\Sku\CollectionFactory $commResourceCustomerSkuCollectionFactory,
        $attributes = array())
    {
        $this->registry = $registry;
        $this->commResourceCustomerSkuCollectionFactory = $commResourceCustomerSkuCollectionFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        ,$attributes);
        $this->setId('erpaccount_sku');
        $this->setUseAjax(true);
        $this->setDefaultSort('product_sku');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
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
        $collection = $this->commResourceCustomerSkuCollectionFactory->create()
            ;

        /* @var $collection Epicor_Comm_Model_Resource_Erp_Customer_Sku_Collection */
        $collection->getProductSelect();
        $collection->addFieldToFilter('customer_group_id', $this->getErpCustomer()->getId());

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('product_sku', array(
            'header' => __('Product'),
            'width' => '150',
            'index' => 'product_sku',
            'filter_index' => 'product_table.sku'
        ));
        $this->addColumn('sku', array(
            'header' => __('Customer Sku'),
            'width' => '150',
            'index' => 'sku',
            'filter_index' => 'main_table.sku'
        ));
        $this->addColumn('description', array(
            'header' => __('Description'),
            'index' => 'description'
        ));

        $this->addColumn('edit', array(
            'header' => __('Edit'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => __('Edit'),
                    'url' => array('base' => 'adminhtml/epicorcomm_message_ajax/editcpncustomer',
                        'params' => array('customer' => $this->getRequest()->getParam('id'))
                    ),
                    'field' => 'id'
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'is_system' => true,
        ));

        $this->addColumn('delete', array(
            'header' => __('Delete'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => __('Delete'),
                    'url' => array('base' => 'adminhtml/epicorcomm_message_ajax/deletecpncustomer',
                        'params' => array('customer' => $this->getRequest()->getParam('id'))
                    ),
                    'field' => 'id'
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        $params = array(
            'id' => $this->getErpCustomer()->getId(),
            '_current' => true,
        );
        return $this->getUrl('adminhtml/epicorcomm_customer_erpaccount/skugrid', $params);
    }

}
