<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Block\Adminhtml\Sales\Returns;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended {

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\CollectionFactory
     */
    protected $commResourceCustomerReturnModelCollectionFactory;

    /**
     * @var \Epicor\Comm\Block\Adminhtml\Sales\Returns\Renderer\ErpaccountFactory
     */
    protected $commAdminhtmlSalesReturnsRendererErpaccountFactory;

    /**
     * @var \Epicor\Comm\Block\Adminhtml\Sales\Returns\Renderer\StatusFactory
     */
    protected $commAdminhtmlSalesReturnsRendererStatusFactory;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backendHelper, \Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\CollectionFactory $commResourceCustomerReturnModelCollectionFactory, array $data = []
    ) {
        $this->commResourceCustomerReturnModelCollectionFactory = $commResourceCustomerReturnModelCollectionFactory;
        parent::__construct(
                $context, $backendHelper, $data
        );
        $this->setId('returnsgrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection() {
        $collection = $this->commResourceCustomerReturnModelCollectionFactory->create();
        /* @var $collection Epicor_Comm_Model_Resource_Customer_Return_Collection */
        $table = $collection->getTable('ecc_erp_account');

        $collection->getSelect()->joinLeft(array('cc' => $table), 'entity_id=erp_account_id', array('name'), null, 'left');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    protected function _filterStatusCondition($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if(!$value) {
            return;
        }
        $value_low = strtolower($value);
        if ($value_low == 'awaiting submission') {           
            $this->getCollection()->addFieldToFilter('returns_status', array('null' => true));
            $this->getCollection()->addFieldToFilter('submitted', array('eq' => 1));
         }elseif ($value_low == 'not submitted') {           
            $this->getCollection()->addFieldToFilter('returns_status', array('null' => true));
            $this->getCollection()->addFieldToFilter('submitted', array('eq' => 0));
         }else{
             $this->getCollection()->addFieldToFilter('returns_status', array('like' => '%'.$value.'%'));
         }
    }
    protected function _prepareColumns() {

        $this->addColumn(
            'id', array(
            'header' => __('Web Returns Number'),
            'align' => 'center',
            'index' => 'id',
            'renderer' =>'\Epicor\Comm\Block\Adminhtml\Sales\Returns\Renderer\WebReference',
            //'width' => '70px',            
            'filter_condition_callback' => array($this, '_filterReference')
                )
        );

        $this->addColumn(
                'erp_returns_number', array(
            'header' => __('Erp Returns Number'),
            'index' => 'erp_returns_number',
                )
        );

        $this->addColumn(
                'customer_reference', array(
            'header' => __('Customer Ref'),
            'index' => 'customer_reference',
                )
        );

        $this->addColumn(
                'name', array(
            'header' => __('Erp Account'),
            'index' => 'name',
            'renderer' => '\Epicor\Comm\Block\Adminhtml\Sales\Returns\Renderer\Erpaccount'
                )
        );

        $this->addColumn(
                'email_address', array(
            'header' => __('Customer Email'),
            'index' => 'email_address',
                )
        );

        $this->addColumn(
                'customer_name', array(
            'header' => __('Customer Name'),
            'index' => 'customer_name',
                )
        );

        $this->addColumn(
                'rma_case_number', array(
            'header' => __('Case Number'),
            'index' => 'rma_case_number',
                )
        );

        $this->addColumn(
                'rma_contact', array(
            'header' => __('Contact'),
            'index' => 'rma_contact',
                )
        );

        $this->addColumn(
                'returns_status', array(
            'header' => __('Status'),
            'index' => 'returns_status',
            'filter_condition_callback' => array($this, '_filterStatusCondition'),
            'renderer' => '\Epicor\Comm\Block\Adminhtml\Sales\Returns\Renderer\Status'
                )
        );

        $this->addColumn(
                'rma_date', array(
            'header' => __('Created'),
            'index' => 'rma_date',
            'align' => 'center',
            'type' => 'date',
                )
        );

        $this->addColumn(
                'action', array(
            'header' => __('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => __('View'),
                    'url' => array('base' => '*/*/view'),
                    'field' => 'id'
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
                )
        );

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/view', array('id' => $row->getId()));
    }

    /**
     * Filter Id by reference or entity id
     */
    protected function _filterReference($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        
        $collection->getSelect()->where(
                "main_table.id like ? OR main_table.web_returns_number like ?", 
                "%$value%"
        );
        
        return $this;
    }
}
