<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Plugin\Checkout;

/**
 * One page checkout processing model
 */
class ShippingInformationManagementPlugin
{

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerResourceModelCustomerCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\SalesRep\Model\Checkout\Contact
     */
    protected $contactModel;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

      /*
     * @var \Epicor\SalesRep\Helper\Checkout
     */
    protected $salesrepHelper;

    /**
    * @var \Magento\Framework\Stdlib\CookieManagerInterface
    */
    protected $_cookieManager;

    /**
     * ShippingInformationManagementPlugin constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface               $scopeConfig
     * @param \Magento\Quote\Model\QuoteRepository                             $quoteRepository
     * @param \Epicor\Comm\Helper\Data                                         $commHelper
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface                       $storeManager
     * @param \Epicor\SalesRep\Model\Checkout\Contact                          $contact
     * @param \Magento\Customer\Model\CustomerFactory                          $customerCustomerFactory
     * @param \Magento\Customer\Model\Session                                  $customerSession
     * @param \Epicor\SalesRep\Helper\Checkout                                 $salesrepHelper
     * @param \Magento\Framework\Stdlib\CookieManagerInterface                 $cookieManager
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\SalesRep\Model\Checkout\Contact $contact,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\SalesRep\Helper\Checkout  $salesrepHelper,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->scopeConfig = $scopeConfig;
        $this->customerCustomerFactory = $customerCustomerFactory;

        $this->commHelper = $commHelper;
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->contactModel = $contact;
        $this->salesrepHelper = $salesrepHelper;
        $this->_cookieManager = $cookieManager;

    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $customer = $this->customerSession->getCustomer();
        if($customer->isSalesRep()){
            $address       = $addressInformation->getShippingAddress();
            $quote         = $this->quoteRepository->getActive($cartId);
            $extAttributes = $address->getExtensionAttributes();
            if ($this->salesrepHelper->isEnabled()
                && $this->scopeConfig->isSetFlag('epicor_salesrep/checkout/choose_contact_enabled',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ) {
                $salesrep_contact   = $extAttributes->getSalesrepContact();
                $salesRepInfo       = '';
                $salesRepCustomerId = '';

                if($salesrep_contact) {
                    $salesRepInfo = base64_decode($salesrep_contact);
                    $salesRepData = unserialize($salesRepInfo);
                    $helper       = $this->commHelper;
                    $erpAccount   = $helper->getErpAccountInfo();
                    if (!empty($salesRepData['ecc_login_id'])) {
                        $collection = $this->customerResourceModelCustomerCollectionFactory->create();
                        $collection->addAttributeToFilter('ecc_contact_code', $salesRepData['contact_code']);
                        $collection->addAttributeToFilter('ecc_erpaccount_id', $erpAccount->getId());
                        $collection->addFieldToFilter('website_id', $this->storeManager->getStore()->getWebsiteId());
                        $customer = $collection->getFirstItem();
                        $salesRepCustomerId = $customer->getId();
                    }
                }

                $customerSession = $this->customerSession;
                /* @var $customerSession \Magento\Customer\Model\Session */

                $customer = $customerSession->getCustomer();
                /* @var $customer Epicor_Comm_Model_Customer */

                $quote->setEccSalesrepCustomerId($customer->getId());
                $quote->setEccSalesrepChosenCustomerId($salesRepCustomerId);
                $quote->setEccSalesrepChosenCustomerInfo($salesRepInfo);

            } else {
                $quote->setEccSalesrepCustomerId($customer->getId());
                $quote->setEccSalesrepChosenCustomerId('');
                $quote->setEccSalesrepChosenCustomerInfo('');
            }

            if ( ! $address->getCustomerAddressId()
                && $extAttributes->getSalesrepNewaddress() != 'new-address'
            ) {
                $cusAddressId
                    = $this->_cookieManager->getCookie('erp_shipping_customer_addressId');

                if ($cusAddressId != null) {
                    $erpAddress = $this->salesrepHelper->_getMasqAddressData(
                        $cusAddressId,
                        $quote
                    );

                    if (isset($erpAddress['erp_code'])) {
                        $address->setEccErpAddressCode($erpAddress['erp_code']);
                    }

                    $address->addData($erpAddress);
                }
                $addressInformation->setShippingAddress($address);
            }
        }

        //set ecc erp address code for BSV when B2B is Masquerade to children
        if ($this->customerSession->getB2BHierarchyMasquerade()) {
            $address = $addressInformation->getShippingAddress();
            $quote = $this->quoteRepository->getActive($cartId);
            $extAttributes = $address->getExtensionAttributes();

            if ( ! $address->getCustomerAddressId()
                && $extAttributes->getSalesrepNewaddress() != 'new-address'
            ) {
                $cusAddressId
                    = $this->_cookieManager->getCookie('erp_shipping_customer_addressId');

                if ($cusAddressId != null) {
                    $erpAddress
                        = $this->salesrepHelper->_getMasqAddressData($cusAddressId,
                        $quote);
                    if (isset($erpAddress['erp_code'])) {
                        $address->setEccErpAddressCode($erpAddress['erp_code']);
                    }
                    $address->addData($erpAddress);
                }
                $addressInformation->setShippingAddress($address);
            }
        }
    }
}