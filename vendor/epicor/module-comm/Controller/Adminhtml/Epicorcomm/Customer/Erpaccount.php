<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ErpaccountController
 *
 * @author David.Wylie
 */
abstract class Erpaccount extends \Epicor\Comm\Controller\Adminhtml\Generic
{

//    protected $_aclId = 'customer/erpaccount';
    protected $_aclId = 'Epicor_Comm::erpaccount';

    /**
     * @var \Epicor\Comm\Model\Customer\ErpaccountFactory
     */
    protected $commCustomerErpaccountFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $backendJsHelper;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerResourceModelCustomerCollectionFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Store\CollectionFactory
     */
    protected $commResourceCustomerErpaccountStoreCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\Customer\Erpaccount\StoreFactory
     */
    protected $commCustomerErpaccountStoreFactory;

    /**
     * @var \Epicor\SalesRep\Model\ResourceModel\Erpaccount\CollectionFactory
     */
    protected $salesRepResourceErpaccountCollectionFactory;

    /**
     * @var \Epicor\SalesRep\Model\ErpaccountFactory
     */
    protected $salesRepErpaccountFactory;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;
    
    /*
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;

    protected $configData;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    
    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;
    
    protected $commResourceErpMappingShippingstatus;
    
    protected $accessroleErpAccountFactory;

    /**
     * @var \Epicor\Common\Model\CustomerErpaccountFactory
     */
    protected $erpAccountFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory,
        \Magento\Backend\Helper\Js $backendJsHelper,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Store\CollectionFactory $commResourceCustomerErpaccountStoreCollectionFactory,
        \Epicor\Comm\Model\Customer\Erpaccount\StoreFactory $commCustomerErpaccountStoreFactory,
        \Epicor\SalesRep\Model\ResourceModel\Erpaccount\CollectionFactory $salesRepResourceErpaccountCollectionFactory,
        \Epicor\SalesRep\Model\ErpaccountFactory $salesRepErpaccountFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Epicor\AccessRight\Model\RoleModel\Erp\AccountFactory $accessroleErpAccountFactory
    )
    {
        $this->resourceConfig = $resourceConfig;
        $this->commCustomerErpaccountFactory = $commCustomerErpaccountFactory;
        $this->backendSession = $context->getSession();
        $this->registry = $context->getRegistry();
        $this->backendJsHelper = $backendJsHelper;
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->commHelper = $commHelper;
        $this->scopeConfig = $scopeConfig;
        $this->commResourceCustomerErpaccountStoreCollectionFactory = $commResourceCustomerErpaccountStoreCollectionFactory;
        $this->commCustomerErpaccountStoreFactory = $commCustomerErpaccountStoreFactory;
        $this->salesRepResourceErpaccountCollectionFactory = $salesRepResourceErpaccountCollectionFactory;
        $this->salesRepErpaccountFactory = $salesRepErpaccountFactory;
        $this->configData = $context->getConfigData();
        $this->commonHelper = $commonHelper;
        $this->request = $context->getRequest();
        $this->response = $context->getResponse();
        $this->eventManager = $context->getEventManager();
        $this->commResourceErpMappingShippingstatus = $commHelper->getMappingShippingstatusFactory();
        $this->accessroleErpAccountFactory = $accessroleErpAccountFactory;
        $this->erpAccountFactory = $context->getErpAcctFactory();
        $this->customerRepository = $context->getCustomerRepository();
        parent::__construct($context, $backendAuthSession);
    }

    protected function _isAllowed()
    {

        if ($this->getRequest()->getActionName() == 'listerpaccounts') {
            return true;
        } else {
            return $this->backendAuthSession
                ->isAllowed($this->_aclId);
        }
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initPage()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Epicor_Comm::erpaccount');
        $resultPage->addBreadcrumb(__('Erp Accounts'), __('Erp Accounts'));

        return $resultPage;
    }

    protected function _initErpAccount()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->commCustomerErpaccountFactory->create();
        if ($id) {
            $model->load($id);
            if ($model->getId()) {
                $data = $this->backendSession->getFormData(true);
                if ($data) {
                    $model->setData($data)->setId($id);
                }
            }
        }

        $this->registry->register('customer_erp_account', $model);
    }

    protected function delete($id, $mass = false)
    {
        try {
            $model = $this->commCustomerErpaccountFactory->create()->load($id);

            if ($model->delete()) {
                if (!$mass) {
                    $this->messageManager->addSuccessMessage(__('The ERP Account has been deleted from the site.'));
                }
            } else {
                $this->messageManager->addErrorMessage('Could not delete ERP Account ' . $model->getErpCode());
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }

    protected function saveCustomers($erpAccount, $data)
    {
        $customers = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['customers']));
        $multipleErpCounts = 0;
        // load current and check if any need to be removed

        $collection = $this->customerResourceModelCustomerCollectionFactory->create();

        if ($erpAccount->isTypeSupplier()) {
            $collection->addFieldToFilter('ecc_supplier_erpaccount_id', $erpAccount->getId());
        } else if ($erpAccount->isTypeCustomer()) {
            $collection->addFieldToFilter('ecc_erpaccount_id', $erpAccount->getId());
        }

        $existing = array();
        foreach ($collection->getItems() as $customer) {
            if (!in_array($customer->getId(), $customers)) {

                if ($erpAccount->isTypeSupplier()) {
                    $customer->setEccSupplierErpaccountId(false);
                } else if ($erpAccount->isTypeCustomer()) {
                    $erpAcctCounts = $customer->getErpAcctCounts();
                    if(!empty($erpAcctCounts) && count($erpAcctCounts) == 1){
                        //update existing  ERP Account  > Guest/Salesrep/Supplier
                        //$customer->setEccErpaccountId(false);
                        $customer->setEccErpAccountType('guest');
                        $erpIdToDel = $erpAcctCounts[0]['erp_account_id'];
                        $customer->deleteErpAcctById($erpIdToDel);
						$this->registry->unregister('erp_acct_counts_'.$customer->getId());
                    }elseif(!empty($erpAcctCounts) && count($erpAcctCounts) > 1){
                        $multipleErpCounts++;
                    }
                    $customer->setData('ecc_erpaccount_id', $erpAccount->getId());
                }
                //this is a known magento220+ issue. The attribute set id must be set or custom attributes won't be updated 
                $customer->setAttributeSetId(\Magento\Customer\Api\CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER);    
                $customer->save();
                $this->eventManager->dispatch('ecc_cuco_save_after', ['customer' => $customer]);  
            } else {
                $existing[] = $customer->getId();
            }
        }

        // loop through passed values and only update customers who are new
        foreach ($customers as $customerId) {
            if (!in_array($customerId, $existing)) {
                $customerModel = $this->customerCustomerFactory->create()->load($customerId);
                if (!$customerModel->isObjectNew()) {
                    if ($erpAccount->isTypeSupplier()) {
                        $customerModel->setEccSupplierErpaccountId($erpAccount->getId());
                    } else if ($erpAccount->isTypeCustomer()) {
                        $erpAcctCounts = $customerModel->getErpAcctCounts();
                        if(!empty($erpAcctCounts) && count($erpAcctCounts) == 1){
                            //update existing ERP Account > ERP Account
//                            $customerModel->setEccErpaccountId($erpAccount->getId());
//                            $customerModel->setEccErpAccountType('customer');
                            $data = [
                                'erp_account_id' => $erpAccount->getId(),
                                'customer_id' => $customerModel->getId(),
                                'erp_account_type' => 'customer'];
                            $this->erpAccountFactory->create()->setData($data)->updateByCustomerId();
                        }elseif(empty($erpAcctCounts)){
                            $customerRepository = $this->customerRepository->getById($customerModel->getId());
                            //update existing Guest/Salesrep/Supplier > ERP Account
                            $extensionAttributes = $customerRepository->getExtensionAttributes(); /** get current extension attributes from entity **/
                            $extensionAttributes->setEccMultiErpId($erpAccount->getId());
                            $extensionAttributes->setEccMultiContactCode('');
                            $extensionAttributes->setEccMultiErpType('customer');
                            $customerRepository->setExtensionAttributes($extensionAttributes);
                            $this->customerRepository->save($customerRepository);
                        }elseif(!empty($erpAcctCounts) && count($erpAcctCounts) > 1){
                            $multipleErpCounts++;
                        }
                    }
                    //this is a known magento220+ issue. The attribute set id must be set or custom attributes won't be updated 
                    $customerModel->setAttributeSetId(\Magento\Customer\Api\CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER);
                    $customerModel->setData('ecc_erpaccount_id', $erpAccount->getId());
                    $customerModel->save();
                    $this->eventManager->dispatch('ecc_cuco_save_after', ['customer' => $customerModel]);
                    $this->registry->unregister('updating_erp_address');
                }
            }
        }

        if($multipleErpCounts > 0){
            $this->messageManager->addErrorMessage(
                __('Total of %1 record(s) could not be updated. Customer(s) selected are mapped to more than 1 ERP Account and this action is not permitted.', $multipleErpCounts)
            );
        }
    }

    /**
     * Saves locations for this erp account
     *
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpAccount
     * @param array $data
     */
    protected function saveLocations($erpAccount, $data)
    {
        if (isset($data['links']['locations'])) {
            $locations = array_keys($this->commHelper->decodeGridSerializedInput($data['links']['locations']));
            $erpAccount->updateLocations($locations);
        }
    }

    /**
     * Saves lists for this erp account
     *
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpAccount
     * @param array $data
     */
    protected function saveLists($erpAccount, $data)
    {
        if (isset($data['links']['lists'])) {
            $lists = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['lists']));
            $erpAccount->removeLists($erpAccount->getLists());
            $erpAccount->addLists($lists);
        }
    }

    /**
     * Saves master shoppers for this erp account
     *
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpAccount
     * @param array $data
     */
    public function saveMasterShoppers($erpAccount, $data)
    {
        if (array_key_exists('ecc_master_shopper', $data['links'])) {
            $masters = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['ecc_master_shopper']));
        } else {
            $masters = array();
        }
        $customers = $erpAccount->getCustomers();
        foreach ($customers as $customer) {
            if (in_array($customer->getId(), $masters)) {
                $customer->setEccMasterShopper(1);
            } else {
                $customer->setEccMasterShopper(0);
            }
            $customer->getResource()->saveAttribute($customer, 'ecc_master_shopper');
        }
    }

    protected function saveStores($erpAccount, $data)
    {
        if (!$this->scopeConfig->isSetFlag('Epicor_Comm/brands/erpaccount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $stores = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['stores']));

            // load current and check if any need to be removed

            $storeCollection = $this->commResourceCustomerErpaccountStoreCollectionFactory->create();
            $storeCollection->addFieldToFilter('erp_customer_group', $erpAccount->getId());

            $existing = array();

            foreach ($storeCollection->getItems() as $store) {
                if (!in_array($store->getStore(), $stores)) {
                    $store->delete();
                } else {
                    $existing[] = $store->getStore();
                }
            }

            if (!empty($stores)) {
                foreach ($stores as $store) {
                    if (!in_array($store, $existing)) {
                        $erp_group_store = $this->commCustomerErpaccountStoreFactory->create();
                        $erp_group_store->setErpCustomerGroup($erpAccount->getId());
                        $erp_group_store->setStore($store);
                        $erp_group_store->save();
                    }
                }
            }
        }
    }

    protected function saveSalesReps($erpAccount, $data)
    {

        if ($this->commHelper->isModuleEnabled('Epicor_SalesRep')) {//!Mage::getStoreConfigFlag('Epicor_Comm/brands/erpaccount')) {
            //echo "here1";die;
            $salesreps = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['salesreps']));
//echo '<pre>';print_r($data);
//print_r($salesreps);die;
            // load current and check if any need to be removed

            $salesrepsCollection = $this->salesRepResourceErpaccountCollectionFactory->create();
            $salesrepsCollection->addFieldToFilter('erp_account_id', $erpAccount->getId());

            $existing = array();

            foreach ($salesrepsCollection->getItems() as $salesrep) {
                if (!in_array($salesrep->getSalesRepAccountId(), $salesreps)) {
                    $salesrep->delete();
                } else {
                    $existing[] = $salesrep->getSalesRepAccountId();
                }
            }

            if (!empty($salesreps)) {
                foreach ($salesreps as $salesrep) {
                    if (!in_array($salesrep, $existing)) {
                        $erp_group_store = $this->salesRepErpaccountFactory->create();
                        $erp_group_store->setErpAccountId($erpAccount->getId());
                        $erp_group_store->setSalesRepAccountId($salesrep);
                        $erp_group_store->save();
                    }
                }
            }
        }
    }

    protected function processContracts($model, $data)
    {
        $contractArray = array('allowed_contract_type', 'required_contract_type', 'allow_non_contract_items', 'contract_shipto_default'
        , 'contract_shipto_date', 'contract_shipto_prompt', 'contract_header_selection', 'contract_header_prompt', 'contract_header_always'
        , 'contract_line_selection', 'contract_line_prompt', 'contract_line_always');

        foreach ($contractArray as $contract) {

            if (isset($data[$contract])) {
                $data[$contract] = $data[$contract] == '' ? null : $data[$contract];
                $model->setData($contract, $data[$contract]);
            }
        }
    }
    
    /**
     * Saves payments for this erp account
     * 
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpAccount
     * @param array $data
     */
    protected function savePaymentMethods($erpAccount, $data)
    {
        $payments = array_keys($this->commonHelper->decodeGridSerializedInput($data['links']['payments']));
        $null = new \Zend_Db_Expr("NULL");
        $emptyArr = array();
        //$setNull = 0;

        if (isset($data['exclude_selected_payments'])) {
            $exclude = $data['exclude_selected_payments'];
            if ($exclude == 1) {
                if (!empty($payments)) {
                    $erpAccount->setAllowedPaymentMethods($null);
                    $erpAccount->setAllowedPaymentMethodsExclude(serialize($payments));
                } else {
                    $erpAccount->setAllowedPaymentMethods($null);
                    $erpAccount->setAllowedPaymentMethodsExclude($null);
                    //$setNull = 1;
                }
            }
        } else {
            if (!empty($payments)) {
                $erpAccount->setAllowedPaymentMethodsExclude($null);
                $erpAccount->setAllowedPaymentMethods(serialize($payments));
            } else {
                $erpAccount->setAllowedPaymentMethods(serialize($emptyArr));
                $erpAccount->setAllowedPaymentMethodsExclude($null);
                //$setNull = 1;
            }
        }
        // if($setNull){
        //     $erpAccount->setAllowedPaymentMethods($null);
        //     $erpAccount->setAllowedPaymentMethodsExclude($null);
        // }
    }

    /**
     * Saves delivery methods for this erp account
     * 
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpAccount
     * @param array $data
     */
    protected function saveDeliveryMethods($erpAccount, $data)
    {

        $delivery = array_keys($this->commonHelper->decodeGridSerializedInput($data['links']['delivery']));
        $null = new \Zend_Db_Expr("NULL");
        $emptyArr = array();
        //$setNull = 0;

        if (isset($data['exclude_selected_delivery'])) {
            $exclude = $data['exclude_selected_delivery'];
            if ($exclude == 1) {
                if (!empty($delivery)) {
                    $erpAccount->setAllowedDeliveryMethods($null);
                    $erpAccount->setAllowedDeliveryMethodsExclude(serialize($delivery));
                } else {
                    $erpAccount->setAllowedDeliveryMethods($null);
                    $erpAccount->setAllowedDeliveryMethodsExclude($null);
                    //$setNull =1;
                }
                //M1 > M2 Translation Begin (Rule p2-1)
                $allQsStoresDefaultErpAccounts = $this->configData->getCollection()->addFieldToFilter('path', ['eq' => 'customer/create_account/qs_default_erpaccount']); // get default erp accounts for all stores
                //$allQsStoresDefaultErpAccounts = Mage::getModel('core/config_data')->addFieldToFilter('path', array('eq' => 'customer/create_account/qs_default_erpaccount')); // get default erp accounts for all stores
                //M1 > M2 Translation End
                foreach ($allQsStoresDefaultErpAccounts as $qsDefaultErpAccounts) {
                    if ($erpAccount->getShortCode() == $qsDefaultErpAccounts->getValue()) {
                        $config = $this->resourceConfig;
                        $config->deleteConfig('customer/create_account/qs_default_erpaccount', $qsDefaultErpAccounts->getScope(), $qsDefaultErpAccounts->getScopeId());
                    }
                }
            }
        } else {
            if (!empty($delivery)) {
                $erpAccount->setAllowedDeliveryMethodsExclude($null);
                $erpAccount->setAllowedDeliveryMethods(serialize($delivery));
            } else {
                //$setNull = 1;
                $erpAccount->setAllowedDeliveryMethods(serialize($emptyArr));
                $erpAccount->setAllowedDeliveryMethodsExclude($null);
            }
        }
        // if($setNull){
        //      $erpAccount->setAllowedDeliveryMethods($null);
        //      $erpAccount->setAllowedDeliveryMethodsExclude($null);
        // }

    }

    /**
     * Saves delivery methods for this erp account
     * 
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpAccount
     * @param array $data
     */
    protected function saveShipstatus($erpAccount, $data) {
        $shipstatuslist = $data['links']; //array_keys(Mage::helper('epicor_common')->decodeGridSerializedInput($data['links']));
        unset($shipstatuslist['shipstatus']);
        $countDefault = $this->commResourceErpMappingShippingstatus->getDefaultErpshipstatusCount();
        if ($countDefault == 0 && !$shipstatuslist) {
            throw new \Magento\Framework\Exception\LocalizedException(__("At least one ship status must be selected"));
        }
        $null = new \Zend_Db_Expr("NULL");
        if ($shipstatuslist) {
            $erpAccount->setData('allowed_shipstatus_methods', serialize($shipstatuslist));
        } else {
            $erpAccount->setData('allowed_shipstatus_methods', $null);
        }
    }

}
