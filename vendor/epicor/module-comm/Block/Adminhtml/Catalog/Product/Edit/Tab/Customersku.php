<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Catalog\Product\Edit\Tab;


/**
 * Product Customer SKU Grid
 *
 * @author David.Wylie
 */
class Customersku extends \Magento\Backend\Block\Widget\Grid implements \Magento\Backend\Block\Widget\Tab\TabInterface
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
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Model\ResourceModel\Customer\Sku\CollectionFactory $commResourceCustomerSkuCollectionFactory,
        $data = array())
    {
        $this->registry = $registry;
        $this->commResourceCustomerSkuCollectionFactory = $commResourceCustomerSkuCollectionFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data);
        $this->setId('product_customersku');
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

    protected function _getProduct()
    {
        return $this->registry->registry('current_product');
    }

    protected function _prepareCollection()
    {
        $collection = $this->commResourceCustomerSkuCollectionFactory->create();
        /* @var $collection Epicor_Comm_Model_Resource_Customer_Sku_Collection */
        $collection->getProductSelect();
        $collection->addFieldToFilter('product_id', $this->_getProduct()->getId());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('erp_code', array(
            'header' => __('Customer'),
            'width' => '150',
            'index' => 'erp_code'
        ));
        $this->addColumn('sku', array(
            'header' => __('Sku'),
            'width' => '150',
            'index' => 'sku',
            'filter_index' => 'main_table.sku'
        ));
        $this->addColumn('description', array(
            'header' => __('Description'),
            'index' => 'description'
        ));
        $this->addColumn('action', array(
            'header' => __('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => __('Delete'),
                    'url' => array('base' => 'adminhtml/epicorcomm_message_ajax/deletecpnproduct',
                        'params' => array('product' => $this->getRequest()->getParam('id'))
                    ),
                    'field' => 'id'
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));
//        $this->addExportType('*/*/exportCsv', Mage::helper('epicor_comm')->__('CSV')); export temporarily commented out  
//        $this->addExportType('*/*/exportXml', Mage::helper('epicor_comm')->__('XML')); related actions not removed from controller

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        $params = array(
            'id' => $this->_getProduct()->getId(),
            '_current' => true,
        );
        return $this->getUrl('adminhtml/epicorcomm_catalog_product/skugrid', $params);
    }

}
