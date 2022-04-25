<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Sales\Order;


class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory
     */
    protected $commResourceCustomerErpaccountCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory
     */
    protected $collectionFactory;

    public function __construct(
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $commResourceCustomerErpaccountCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory $collectionFactory
    ) {
        $this->commResourceCustomerErpaccountCollectionFactory = $commResourceCustomerErpaccountCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->collectionFactory = $collectionFactory;
    }
    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();

        $accounts = $this->commResourceCustomerErpaccountCollectionFactory->create()->toOptionArray();

        array_unshift($accounts, array('label' => '', 'value' => ''));
        $this->getMassactionBlock()->addItem('assign_account', array(
            'label' => __('Change ERP Order Status'),
            'url' => $this->getUrl('adminhtml/epicorcomm_sales_order/massAssignErpstatus'),
            'additional' => array(
                'erp_status' => array(
                    'name' => 'erp_status',
                    'type' => 'select',
                    'label' => __('Order Sent to Erp'),
                    'values' => array(
                        '0' => 'Order Not Sent',
                        '1' => 'Order Sent',
                        '3' => 'Erp Error',
                        '4' => 'Error - Retry Attempt Failure',
                        '5' => 'Order Never Send',
                    )
                )
            )
        ));

        return $this;
    }

    protected function _prepareCollection()
    {

        //M1 > M2 Translation Begin (Rule p2-5.2)
        //$collection = Mage::getResourceModel($this->_getCollectionClass());
        $collection = $this->collectionFactory->create();
        //M1 > M2 Translation End

        /* @var $collection Mage_Sales_Model_Resource_Order_Collection */
        $collection->getSelect()->join(array('sfo' => $collection->getTable('sales_flat_order')), 'main_table.entity_id = sfo.entity_id', array('ecc_gor_message' => 'ecc_gor_message', 'gor_status' => 'sfo.status', 'erp_order_number' => 'sfo.erp_order_number', 'ecc_device_used' => 'sfo.ecc_device_used'));
        $collection->getSelect()->join(array('osh' => $collection->getTable('sales_flat_order_status_history')), 'osh.entity_id = (SELECT entity_id FROM ' . $collection->getTable('sales_flat_order_status_history') . ' WHERE parent_id=main_table.entity_id LIMIT 1)', array('ordercomment' => 'osh.comment',));

        $this->setCollection($collection);
        return \Magento\Backend\Block\Widget\Grid::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        parent::_prepareColumns();
        $this->getColumn('real_order_id')->setFilterIndex('main_table.increment_id');
        $this->getColumn('created_at')->setFilterIndex('main_table.created_at');

        //M1 > M2 Translation Begin (Rule P2-6.8)
        //if (!Mage::app()->isSingleStoreMode()) {
        if (!$this->_storeManager->isSingleStoreMode()) {
            //M1 > M2 Translation End
            $this->getColumn('store_id')->setFilterIndex('main_table.store_id');
        }

        $this->getColumn('base_grand_total')->setFilterIndex('main_table.base_grand_total');
        $this->getColumn('grand_total')->setFilterIndex('main_table.grand_total');
        $this->getColumn('status')->setFilterIndex('main_table.status');

        $this->addColumnAfter('erp_order_number', array(
            'header' => __('ERP Order #'),
            'index' => 'erp_order_number',
            'filter_index' => 'sfo.erp_order_number',
            ), 'real_order_id');

        $this->addColumnAfter('ecc_gor_message', array(
            'header' => __('Sent to ERP'),
            'index' => 'ecc_gor_message',
            'width' => '100px',
            'filter_index' => 'sfo.ecc_gor_message'
            ), 'status');

        // Add order comment to grid
        $this->addColumnAfter('ordercomment', array(
            'header' => __('Order Comment'),
            'index' => 'ordercomment',
            'filter_index' => 'osh.comment',
            ), 'ecc_gor_message');

        if ($this->scopeConfig->getValue('sales/general/display_device', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            // Add device used to grid
            $this->addColumnAfter('ecc_device_used', array(
                'header' => __('Device Used'),
                'index' => 'ecc_device_used',
                'filter_index' => 'sfo.ecc_device_used',
                ), 'ordercomment');
        }


        $this->sortColumnsByOrder();
        return $this;
    }

}
