<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Checkout\Type;

/**
 * One page checkout processing model
 */
class Onepage extends \Magento\Checkout\Model\Type\Onepage
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $customerAddressFactory;

    /**
     * @var \Epicor\Comm\Model\Customer\Erpaccount\AddressFactory
     */
    protected $commCustomerErpaccountAddressFactory;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Checkout\Helper\Data $helper,
        \Magento\Customer\Model\Url $customerUrl,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Customer\Model\AddressFactory $customrAddrFactory,
        \Magento\Customer\Model\FormFactory $customerFormFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\DataObject\Copy $objectCopyService,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\Metadata\FormFactory $formFactory,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        \Magento\Quote\Api\CartManagementInterface $quoteManagement,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Customer\Model\AddressFactory $customerAddressFactory,
        \Epicor\Comm\Model\Customer\Erpaccount\AddressFactory $commCustomerErpaccountAddressFactory,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->customerAddressFactory = $customerAddressFactory;
        $this->commCustomerErpaccountAddressFactory = $commCustomerErpaccountAddressFactory;
        $this->commHelper = $commHelper;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        parent::__construct(
            $eventManager,
            $helper,
            $customerUrl,
            $logger,
            $checkoutSession,
            $customerSession,
            $storeManager,
            $request,
            $customrAddrFactory,
            $customerFormFactory,
            $customerFactory,
            $orderFactory,
            $objectCopyService,
            $messageManager,
            $formFactory,
            $customerDataFactory,
            $mathRandom,
            $encryptor,
            $addressRepository,
            $accountManagement,
            $orderSender,
            $customerRepository,
            $quoteRepository,
            $extensibleDataObjectConverter,
            $quoteManagement,
            $dataObjectHelper,
            $totalsCollector
        );
    }


    /**
     * Specify quote shipping method
     *
     * @param   string $shippingMethod
     * @return  array
     */
    public function saveShippingMethod($shippingMethod)
    {
        if (empty($shippingMethod)) {
            return array('error' => -1, 'message' => __('Invalid shipping method.'));
        }
        $rate = $this->getQuote()->getShippingAddress()->getShippingRateByCode($shippingMethod);
        if (!$rate) {
            return array('error' => -1, 'message' => __('Invalid shipping method.'));
        }
        $this->getQuote()->getShippingAddress()
            ->setShippingMethod($shippingMethod);

        $this->getCheckout()->setStepData('shipping_method', 'complete', true);
        if ($this->scopeConfig->isSetFlag('epicor_comm_enabled_messages/dda_request/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE))
            $this->getCheckout()->setStepData('shipping_dates', 'allow', true);
        else
            $this->getCheckout()->setStepData('payment', 'allow', true);

        return array();
    }

    public function saveShippingDates($shippingdates)
    {
        // Save the data here
        // Mage::getSingleton('customer/session')->setShippingDate($shippingdates['shipby']);
        $this->getQuote()->setEccRequiredDate($shippingdates['shipby']);
        $this->getQuote()->setEccIsDdaDate(true);
        $this->getQuote()->collectTotals();
        $this->getQuote()->save();

        $this->getCheckout()
            ->setStepData('shipping_dates', 'complete', true)
            ->setStepData('payment', 'allow', true);

        return array();
    }

    public function saveCustomerOrderRef($customerOrderRef)
    {
        // Save the data here
        $this->getQuote()->setEccCustomerOrderRef($customerOrderRef);
        $this->getQuote()->save();

        $this->checkoutSession->setEccCustomerOrderRef($customerOrderRef);

        return array();
    }

    protected function _getMasqAddressData($customerAddressId)
    {
        $customerSession = $this->getCustomerSession();
        $customer = $customerSession->getCustomer();
        /* @var $customer Epicor_Comm_Model_Customer */

        if ($customer->isSalesRep()) {
            $customerId = $this->getQuote()->getEccSalesrepChosenCustomerId();
            if ($customerId) {
                $salesRepCustomer = $this->customerCustomerFactory->create()->load($customerId);
                /* @var $salesRepCustomer Epicor_Comm_Model_Customer */
            } else {

                $customerInfo = unserialize($this->getQuote()->getEccSalesrepChosenCustomerInfo());

                if ($customerInfo['name']) {
                    $salesRepCustomer = $this->customerCustomerFactory->create();
                    /* @var $salesRepCustomer Epicor_Comm_Model_Customer */

                    $nameParts = explode(' ', $customerInfo['name'], 3);

                    $salesRepCustomer->setFirstname($nameParts[0]);

                    if (count($nameParts) == 3) {
                        $salesRepCustomer->setMiddlename($nameParts[1]);
                        $salesRepCustomer->setLastname($nameParts[2]);
                    } else {
                        $salesRepCustomer->setLastname($nameParts[1]);
                    }
                    $salesRepCustomer->setEmail($customerInfo['email']);
                } else {
                    $salesRepCustomer = $customer;
                    /* @var $salesRepCustomer Epicor_Comm_Model_Customer */
                }
            }
        }

        if (strpos($customerAddressId, 'customeraddress_') !== false) {
            $addressId = str_replace('customeraddress_', '', $customerAddressId);
            $address = $this->customerAddressFactory->create()->load($addressId);
            /* @var $address Mage_Customer_Model_Address */
            $address->explodeStreetAddress();

            $street = array(
                $address->getStreet1(),
                $address->getStreet2(),
                $address->getStreet3(),
                $address->getStreet4()
            );

            $address->setData('street', $street);
            $addressData = $address->getData();
        } else if (strpos($customerAddressId, 'erpaddress_') !== false) {
            $addressId = str_replace('erpaddress_', '', $customerAddressId);

            $address = $this->commCustomerErpaccountAddressFactory->create()->load($addressId);
            /* @var $address Epicor_Comm_Model_Customer_Erpaccount_Address */

            if ($customer->isSalesRep()) {
                $addressData = $address->toCustomerAddress($salesRepCustomer)->getData();
            } else {
                $addressData = $address->toCustomerAddress($customer)->getData();
            }
        } else {
            $addressData = json_decode($customerAddressId, true);
        }

        if ($customer->isSalesRep()) {
            $addressData['email'] = $salesRepCustomer->getEmail();
        }

        return $addressData;
    }

    public function saveBilling($data, $customerAddressId)
    {
        $commHelper = $this->commHelper;
        /* @var $commHelper Epicor_Comm_Helper_Data */
        if ($commHelper->isMasquerading()) {
            if (!empty($customerAddressId)) {
                $addressData = $this->_getMasqAddressData($customerAddressId);
                $data = array_merge($data, $addressData);
                $customerAddressId = 0;
            }

            $data['is_default_billing'] = false;
            $data['save_in_address_book'] = 0;
            $data['customer_id'] = $this->getQuote()->getCustomerId();
        }

        return parent::saveBilling($data, $customerAddressId);
    }

    public function saveShipping($data, $customerAddressId)
    {
        $commHelper = $this->commHelper;
        /* @var $commHelper Epicor_Comm_Helper_Data */
        if ($commHelper->isMasquerading()) {
            if (!empty($customerAddressId)) {
                $addressData = $this->_getMasqAddressData($customerAddressId);
                $customerAddressId = 0;
                $data = array_merge($data, $addressData);
            }
            $data['is_default_shipping'] = false;
            $data['save_in_address_book'] = 0;
            $data['customer_id'] = $this->getQuote()->getCustomerId();
        }

        return parent::saveShipping($data, $customerAddressId);
    }

    /**
     * Overloaded to handle contracts with selected shipto
     *
     * @return \Magento\Checkout\Model\Type\Onepage
     */
    public function initCheckout()
    {
        parent::initCheckout();
        $contractHelper = $this->listsFrontendContractHelper;
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */

        if ($contractHelper->contractsEnabled() && $contractHelper->getSelectedContractShipto()) {
            $contractHelper->selectContractShipto($contractHelper->getSelectedContractShipto());
        }

        return $this;
    }

}
