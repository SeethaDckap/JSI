<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Account;

class DeleteContact extends \Epicor\Customerconnect\Controller\Account {

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cuau
     */
    protected $customerconnectMessageRequestCuau;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    protected $_stores;
    protected $_storeIds;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Epicor\Common\Model\Access\Group\CustomerFactory $commonAccessGroupCustomerFactory,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\App\CacheInterface $cache,
        \Epicor\Customerconnect\Model\Message\Request\Cuau $customerconnectMessageRequestCuau,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    )
    {
        $this->customerconnectMessageRequestCuau = $customerconnectMessageRequestCuau;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->scopeConfig = $scopeConfig;
        $this->eventManager = $eventManager;
        $this->customerRepository = $customerRepository;
        parent::__construct(
                $context, $customerSession, $localeResolver, $resultPageFactory, $resultLayoutFactory, $commHelper, $customerResourceModelCustomerCollectionFactory, $commonAccessGroupCustomerFactory, $customerconnectHelper, $generic, $cache
        );
    }

    public function execute() {
        $helper = $this->customerconnectHelper;
        $data = $this->getRequest()->getPost();

        $error = false;

        if ($data) {

            $form_data = json_decode($data['json_form_data'], true);

            $message = $this->customerconnectMessageRequestCuau;
            /* @var $message Epicor_Customerconnect_Model_Message_Request_Cuau */

            $storeId = $this->storeManager->getStore()->getId();
            $brand = $this->commHelper->getStoreBranding($storeId);
            $company = $brand->getCompany();
            $erpCustomer = $this->commHelper->getErpAccountInfo();
            $currencies = array_keys($erpCustomer->getAllCurrencyData());
            $websites = $this->_getWebsitesForCurrenciesAndBranding($currencies);
            if ($form_data['source'] == 0 || $form_data['source'] == 2) {
                $message->deleteContact($form_data);
            }
            if ($form_data['source'] == 1 || $form_data['source'] == 2) {

                foreach ($websites as $website) {
                    $customer = $this->customerCustomerFactory->create()->setWebsiteId($website);
                    /* @var $customer Epicor_Comm_Model_Customer */
                    $customer->loadByEmail($form_data['email_address']);
                    $email =$customer->getEmail();
                    $this->registry->register('isSecureArea', true);

                    $linkedErpCount = $customer->getErpAcctCounts();
                    if(!empty($linkedErpCount) && count($linkedErpCount) > 1){

                        $customer->deleteErpAcctById($erpCustomer->getId());
                        $this->eventManager->dispatch(
                            'ecc_cuco_del_addresses', ['customer' => $customer, 'erp_account_Id' => $erpCustomer->getId()]
                        );
                        $this->registry->unregister('erp_acct_counts_'.$customer->getId());
                        $linkedErpCount = $customer->getErpAcctCounts();
                        if (!empty($linkedErpCount) && count($linkedErpCount) == 1) {

                            $this->setDefaultAddress($linkedErpCount[0]['erp_account_id'],$customer);
                        }
                        if($email == $this->customerSession->getCustomer()->getEmail()){

                            $this->registry->unregister('logout_forcefully');
                            $this->registry->register('logout_forcefully', true);
                        }

                    }else {
                        $customer->delete();
                    }
                    $this->registry->unregister('isSecureArea');
                }
            }
            $this->_successMsg = __('Contact deleted successfully');
            $this->_errorMsg = __('Failed to delete Contact');
            if ($form_data['source'] == 0 || $form_data['source'] == 2) {
                $resultData = $this->sendUpdate($message);
            } else {
                $this->messageManager->addSuccessMessage($this->_successMsg);
                //M1 > M2 Translation Begin (Rule p2-4)
                //echo json_encode(array('redirect' => Mage::getUrl('customerconnect/account/'), 'type' => 'success'));
                $resultData = json_encode(array('redirect' => $this->_url->getUrl('customerconnect/account/'), 'type' => 'success'));
                //M1 > M2 Translation End
            }
        } else {
            $error = true;
        }

        if ($error) {
            //M1 > M2 Translation Begin (Rule p2-4)
            //echo json_encode(array('redirect' => Mage::getUrl('customerconnect/account/'), 'type' => 'success'));
            $resultData = json_encode(array('redirect' => $this->_url->getUrl('customerconnect/account/'), 'type' => 'success'));
            //M1 > M2 Translation End
        }


        if ($this->registry->registry('logout_forcefully')) {
            $this->customerSession->destroy();
        }

        $result = $this->resultJsonFactory->create();
        $result->setData($resultData);

        return $result;
    }

    protected function _getWebsitesForCurrenciesAndBranding($currencies) {

        if (!is_array($currencies)) {
            $currencies = array($currencies);
        }
        if ($this->scopeConfig->isSetFlag('customer/account_share/scope', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $stores = $this->_loadStores();
            $websites = array();
            foreach ($stores as $store) {
                /* @var $store Mage_Core_Model_Store */
                if (!in_array($store->getWebsiteId(), $websites) && in_array($store->getWebsite()->getBaseCurrencyCode(), $currencies)) {
                    $websites[] = $store->getWebsiteId();
                }
            }
        } else {
            $websites = array($this->storeManager->getDefaultStoreView()->getWebsiteId());
        }
        return $websites;
    }

    protected function _loadStores() {

        if (is_null($this->_stores)) {
            $this->_stores = array();
            $this->_storeIds = array();
            $brandStores = $this->commHelper->getStoreFromBranding(null);
            $this->_stores = $this->_stores + $brandStores;

            foreach ($this->_stores as $store) {
                $this->_storeIds[] = $store->getId();
            }
        }
        return $this->_stores;
    }

    protected function setDefaultAddress($erp_account_id, $customer) {

        $erpAccount = $this->commHelper->getErpAccountInfo($erp_account_id);
        $deladdressCode = $erpAccount->getDefaultDeliveryAddressCode();
        $delinvCode = $erpAccount->getDefaultInvoiceAddressCode();

        $addresscollection = $customer->getAddressesCollection();
        $erpCustomerGroupCode = $erpAccount->getErpCode();
        $gcattributes = [
            ['attribute' => 'ecc_erp_group_code', 'eq' => $erpCustomerGroupCode],
            ['attribute' => 'ecc_erp_group_code', 'null' => true],
        ];
        $addresscollection->addAttributeToFilter($gcattributes, null, 'left');
        $addresscollection->addAttributeToFilter('ecc_erp_address_code', array('in' => [$deladdressCode, $delinvCode]));
        $addresscollection->load();
        $items = $addresscollection->getItems();
        $customerRepository = $this->customerRepository->getById($customer->getId());
        foreach($items as $item){
            if($deladdressCode == $item->getData('ecc_erp_address_code')){
                $customerRepository->setDefaultShipping($item->getId());
            }
            if($delinvCode == $item->getData('ecc_erp_address_code')){
                $customerRepository->setDefaultBilling($item->getId());
            }
        }
        $this->customerRepository->save($customerRepository);
    }

}
