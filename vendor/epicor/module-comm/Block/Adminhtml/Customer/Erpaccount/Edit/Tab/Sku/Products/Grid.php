<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab\Sku\Products;


/**
 * 
 * ERP Account grid for erp account selector input
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $catalogResourceModelProductCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogResourceModelProductCollectionFactory,
        array $data = []
    )
    {
        $this->catalogResourceModelProductCollectionFactory = $catalogResourceModelProductCollectionFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('sku_products_grid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setRowClickCallback('productSelector.selectErpAccount.bind(productSelector)');
        $this->setRowInitCallback('productSelector.updateWrapper.bind(productSelector)');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->catalogResourceModelProductCollectionFactory->create();

//        if(Mage::getStoreConfigFlag('customer/account_share/scope')){  
//            $websiteCode = $this->getRequest()->getParam('website');
//            if($websiteCode){                                                                 // if not admin    
//                $defaultStoreId =  Mage::getModel( "core/website" )->load($websiteCode)->getDefaultGroup()->getDefaultStoreId();
//       
//                $validSitesForStore = Mage::getModel('epicor_comm/customer_erpaccount_store')->getCollection()
//                                        ->addFieldToFilter('store', array('eq'=>$defaultStoreId));
//                
//                $defaultStoreCurrencyCode = Mage::app()->getStore($defaultStoreId)->getDefaultCurrencyCode();
//                If($defaultStoreCurrencyCode){
//                    $collection->addFieldToFilter('currency_code', array('eq'=>$defaultStoreCurrencyCode));         // in case default currency code has changed since erp was loaded
//                }
//                $validSites = array();
//                foreach( $validSitesForStore as $valid){                                                            // create list of valid ids for store
//                    $validSites[$valid->getErpCustomerGroup()] = $valid->getErpCustomerGroup();
//                }
//                $collection->addFieldToFilter('entity_id', array('in'=>$validSites));                               // apply valid erp ids to collection
//            }
//        }
        /* @var $collection Epicor_Comm_Model_Resource_Customer_Erpaccount_Collection */

//        if (Mage::helper('epicor_comm')->isModuleEnabled('Epicor_Supplierconnect')) {
//            $data = $this->getRequest()->getParams();
//
//            if (isset($data['field_id']) && strpos($data['field_id'], 'supplier') !== false) {
//                $collection->addFieldToFilter('account_type', 'Supplier');
//            } else if (!isset($data['field_id']) || $data['field_id'] != 'erp_account_id') {
//                $types = array('B2B','B2C');
//                $collection->addFieldToFilter('account_type', array('in' => $types));
//            }
//        }
//        
//        $allStoresDefaultErpAccounts = Mage::getModel('core/config_data')->getCollection()
//				->addFieldToFilter('path',array('eq'=>'customer/create_account/default_erpaccount')); // get default erp accounts for all stores
//        $allDefaultErpValues = array();
//        foreach($allStoresDefaultErpAccounts as $eachStoreErpAccounts){
//           $allDefaultErpValues[] = $eachStoreErpAccounts->getValue();
//        }
//       
//        if (isset($data['field_id']) && in_array($data['field_id'], array('_accounterpaccount_id', 'visibility'))) {
//            $collection->addFieldToFilter('entity_id', array('nin' => $allDefaultErpValues));                           // only include if not default erp account
//        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();

//        $this->addColumn('erp_code', array(
//            'header' => Mage::helper('epicor_comm')->__('ERP Customer Code'),
//            'index' => 'erp_code',
//            'width' => '20px',
//            'filter_index' => 'erp_code'
//        ));
        $this->addColumn('sku', array(
            'header' => __('Product SKU'),
            'index' => 'sku',
        ));
//        $this->addColumn('short_code', array(
//            'header' => Mage::helper('epicor_comm')->__('Short Code'),
//            'index' => 'short_code',
//            'width' => '20px',
//            'filter_index' => 'short_code'
//        ));
//        $this->addColumn('account_number', array(
//            'header' => Mage::helper('epicor_comm')->__('Account Number'),
//            'index' => 'account_number',
//            'width' => '20px',
//            'filter_index' => 'account_number'
//        ));

        $this->addColumn('name', array(
            'header' => __('Name'),
            'index' => 'name',
        ));
        return $this;
    }

    public function getRowUrl($row)
    {
        return $row->getId();
    }

    public function getGridUrl()
    {
        $data = $this->getRequest()->getParams();
        return $this->getUrl('*/*/*', array('grid' => true, 'field_id' => $data['field_id']));
    }

}
