<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Account;

class SyncContact extends \Epicor\Customerconnect\Controller\Account
{
    
    protected $_stores;
    
    protected $_storeIds;

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cuau
     */
    protected $customerconnectMessageRequestCuau;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var \Epicor\Common\Helper\Xml
     */
    protected $commonXmlHelper;

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
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Common\Helper\Xml $commonXmlHelper
    )
    {
        $this->customerconnectMessageRequestCuau = $customerconnectMessageRequestCuau;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->scopeConfig = $scopeConfig;
        $this->commonXmlHelper = $commonXmlHelper;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $commHelper,
            $customerResourceModelCustomerCollectionFactory,
            $commonAccessGroupCustomerFactory,
            $customerconnectHelper,
            $generic,
            $cache
        );
    }

    public function execute()
    {
        $helper = $this->customerconnectHelper;

        $data = $this->getRequest()->getPost();
        $error = false;

        if ($data) {

            $form_data = json_decode($data['json_form_data'], true);
            $old_form_data = false;
            unset($form_data['old_data']);
            // add this otherwise the difference check will always be true and always send a message
            $form_data['contact_code'] = isset($old_form_data['contact_code']) ? $old_form_data['contact_code'] : "";
            switch ($form_data["source"]) {
                case $helper::SYNC_OPTION_ONLY_ECC:
                    $form_data['login_id'] = 'true';                        // must be true, or sync will never be web enabled
                    $message = $this->customerconnectMessageRequestCuau;
                    $message->addContact('A', $form_data, $old_form_data);
                    $this->_successMsg = __('Contact added successfully');
                    $this->_errorMsg = __('Failed to add Contact');
                    $resultData = $this->sendUpdate($message);
                    if (!$resultData['error']) {
                        $erpAccount = $this->commHelper->getErpAccountInfo();
                        /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount */
                        if ($erpAccount) {
                            $accountNumber = $erpAccount->getErpCode();
                        }
                        $erpCustomer = $this->commHelper->getErpAccountByAccountNumber($accountNumber, 'Customer');
                        $currencies = array_keys($erpCustomer->getAllCurrencyData());
                        $websites = $this->_getWebsitesForCurrenciesAndBranding($currencies);
                        foreach ($websites as $website) {
                            $customer = $this->customerCustomerFactory->create();
                            $customer->setWebsiteId($website);
                            $customer->loadByEmail($form_data['email_address']);
                            if ($customer->getId()) {
                                $this->processContactResp($resultData, $form_data['email_address'], $customer);
                            }
                        }
                    }
                    break;
                default:
                    $this->messageManager->addNoticeMessage(__('Sync is not necessary'));
                    $error = true;
                    break;
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
    
    protected function processContactResp($resultData, $currentEmail, $customer) {

        if (!$resultData['error']) {
            $response = $resultData['message']->getResponse();
            $xmlHelper = $this->commonXmlHelper;
            /* @var $helper Epicor_Common_Helper_Xml */
            $contacts = $xmlHelper->varienToArray($response->getCustomer()->getContacts());
            $filteredContact = array_values(array_filter($contacts['contact'], function($arrayValue) use($currentEmail) {
                        return $arrayValue['email_address'] == $currentEmail;
                    }));
            if (empty($filteredContact[0]['contact_code'])) {
                $customer->setEccCucoPending('1');
            } else {
                $customer->setEccContactCode($filteredContact[0]['contact_code']);
                $customer->setEccCucoPending('0');
            }
        } else {
            $customer->setEccCucoPending('1');
        }
        $this->commHelper->saveCustomerInfo($customer, $customer->getEccErpaccountId());
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

}
