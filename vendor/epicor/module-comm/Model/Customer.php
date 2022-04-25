<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model;


use Epicor\Comm\Helper\Customer\Address;
use Magento\Framework\Exception\LocalizedException;
use Magento\Setup\Exception;
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Customer model override
 *
 * Overrides customer address functionality
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 *
 * @method string getEccLocationLinkType()
 * @method string getEccDefaultLocationCode()
 * @method setEccLocationLinkType(string $type)
 * @method setEccDefaultLocationCode(string $type)
 * @method string getEccSalesrepCatalogAccess()
 * @method setEccSalesrepCatalogAccess(string $type)
 *
 * @method string getEccContractShiptoDefault()
 * @method string getEccContractShiptoDate()
 * @method string getEccContractShiptoPrompt()
 * @method string getEccContractHeaderSelection()
 * @method string getEccContractHeaderPrompt()
 * @method string getEccContractHeaderAlways()
 * @method string getEccContractLineSelection()
 * @method string getEccContractLinePrompt()
 * @method string getEccContractLineAlways()
 *
 * @method setEccContractShiptoDefault(string $value)
 * @method setEccContractShiptoDate(string $value)
 * @method setEccContractShiptoPrompt(string $value)
 * @method setEccContractHeaderSelection(string $value)
 * @method setEccContractHeaderPrompt(string $value)
 * @method setEccContractHeaderAlways(string $value)
 * @method setEccContractLineSelection(string $value)
 * @method setEccContractLinePrompt(string $value)
 * @method setEccContractLineAlways(string $value)
 *
 */
class Customer extends \Magento\Customer\Model\Backend\Customer
{
    const LIMIT_SHIPPING_ADDRESS_CONFIG = 'customer/address/limit_shipping_addresses';
    private $_erpAccount;
    private $_salesRepAccount;
    protected $_locations;
    protected $_optimizedLocations = false;
    protected $_updatedLocations = array();
    protected $_delLocations = array();
    protected $_newLocations = array();
    protected $_allowedLocations;
    protected $_allowedLocationCodes;
    protected $_lists = false;
    protected $_delLists = array();
    protected $_newLists = array();
    protected $_addressesByType;
    protected $_addresses = null;

    /**
     * @var \Epicor\Comm\Helper\DataFactory
     */
    protected $commHelper;

    /**
     * @var Address
     */
    protected $commCustomerAddressHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\Link\CollectionFactory
     */
    protected $commResourceLocationLinkCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\CollectionFactory
     */
    protected $commResourceLocationCollectionFactory;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Epicor\SalesRep\Model\AccountFactory
     */
    protected $salesRepAccountFactory;

    /**
     * @var \Epicor\Common\Helper\Account\Selector
     */
    protected $commonAccountSelectorHelper;

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory
     */
    protected $listsResourceListModelCollectionFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\CustomerFactory
     */
    protected $listsListModelCustomerFactory;

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\Customer\CollectionFactory
     */
    protected $listsResourceListModelCustomerCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    protected $_defaultBilling;
    protected $_defaultShipping;

    /**
     * @var \Epicor\Common\Model\CustomerErpaccountFactory
     */
    protected $erpAccountFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    protected $_erpAcctCounts = false;

    protected $_allErpAcctids = false;

    protected $_favErpids = false;

    protected $addressesFactory;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $config,
        \Magento\Customer\Model\ResourceModel\Customer $resource,
        \Magento\Customer\Model\Config\Share $configShare,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $addressesFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Customer\Api\CustomerMetadataInterface $metadataService,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Epicor\Comm\Helper\DataFactory $commHelper,
        \Epicor\Comm\Helper\Customer\AddressFactory $commCustomerAddressHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Comm\Helper\MessagingFactory $commMessagingHelper,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Epicor\Comm\Model\ResourceModel\Location\Link\CollectionFactory $commResourceLocationLinkCollectionFactory,
        \Epicor\Comm\Model\ResourceModel\Location\CollectionFactory $commResourceLocationCollectionFactory,
        \Epicor\Comm\Helper\LocationsFactory $commLocationsHelper,
        \Epicor\SalesRep\Model\AccountFactory $salesRepAccountFactory,
        \Epicor\Common\Helper\Account\SelectorFactory $commonAccountSelectorHelper,
        \Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory $listsResourceListModelCollectionFactory,
        \Epicor\Lists\Model\ListModel\CustomerFactory $listsListModelCustomerFactory,
        \Epicor\Lists\Model\ResourceModel\ListModel\Customer\CollectionFactory $listsResourceListModelCustomerCollectionFactory,
        \Epicor\Lists\Helper\Frontend\ContractFactory $listsFrontendContractHelper,
        CheckoutSession $checkoutSession,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Epicor\Common\Model\CustomerErpaccountFactory $erpAccountFactory,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->commHelper = $commHelper;
        $this->commCustomerAddressHelper = $commCustomerAddressHelper->create();
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->resourceConnection = $resourceConnection;
        $this->eventManager = $context->getEventDispatcher();
        $this->commResourceLocationLinkCollectionFactory = $commResourceLocationLinkCollectionFactory;
        $this->commResourceLocationCollectionFactory = $commResourceLocationCollectionFactory;
        $this->commLocationsHelper = $commLocationsHelper;
        $this->salesRepAccountFactory = $salesRepAccountFactory;
        $this->commonAccountSelectorHelper = $commonAccountSelectorHelper;
        $this->listsResourceListModelCollectionFactory = $listsResourceListModelCollectionFactory;
        $this->listsListModelCustomerFactory = $listsListModelCustomerFactory;
        $this->listsResourceListModelCustomerCollectionFactory = $listsResourceListModelCustomerCollectionFactory;
        $this->registry = $registry;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->erpAccountFactory = $erpAccountFactory;
        $this->customerSession = $customerSession;
        $this->addressesFactory = $addressesFactory;
        $this->checkoutSession = $checkoutSession;
        parent::__construct(
            $context,
            $registry,
            $storeManager,
            $config,
            $scopeConfig,
            $resource,
            $configShare,
            $addressFactory,
            $addressesFactory,
            $transportBuilder,
            $groupRepository,
            $encryptor,
            $dateTime,
            $customerDataFactory,
            $dataObjectProcessor,
            $dataObjectHelper,
            $metadataService,
            $indexerRegistry,
            $resourceCollection,
            $data
        );
    }


    /**
     * get Customer Erp Account for type
     *
     * @param string $type
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    private function _getErpAccount($type = 'customer')
    {

        if (empty($this->_erpAccount)) {

            $helper = $this->commHelper->create();
            /* @var $helper \Epicor\Comm\Helper\Data */

            if ($type == 'customer') {
                $erpAccountId = $this->getEccErpaccountId();
            } else {
                $erpAccountId = $this->getEccSupplierErpaccountId();
            }

            $this->_erpAccount = $helper->getErpAccountInfo($erpAccountId, $type);
        }

        return $this->_erpAccount;
    }

    /**
     * Checks if ErpAccount is valid for passed store or if null passed the current store
     *
     * @param \Epicor\Comm\Model\Store $store
     * @return bool
     */
    public function isValidForStore($store = null, $type = 'customer')
    {
        $helper = $this->commHelper->create();
        /* @var $helper \Epicor\Comm\Helper\Data */

        $erpAccount = $helper->getErpAccountInfo(null, $type);

        return $erpAccount ? $erpAccount->isValidForStore($store) : false;
    }

    /**
     * get the ErpAccount of the login customer else return default ERP store config
     *
     * @param \Epicor\Comm\Model\Store $store
     * @return bool
     */
    public function isValidForStoreLogin($store = null, $type = 'customer')
    {
        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */

        $erpAccount = $this->_getErpAccount($type);

        return $erpAccount ? $erpAccount->isValidForStore($store) : false;
    }

    /**
     * get Customer Erp Account
     *
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function getCustomerErpAccount()
    {
        return $this->_getErpAccount();
    }

    /**
     * get Supplier Erp Account
     *
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function getSupplierErpAccount()
    {
        return $this->_getErpAccount('supplier');
    }

    /**
     * Get customer default billing address
     *
     * @return \Magento\Customer\Model\Address
     */
    public function getPrimaryBillingAddress()
    {
        if ($this->_appState->getAreaCode() === 'adminhtml') {
            parent::getPrimaryBillingAddress();
        }

        $helper = $this->commCustomerAddressHelper;
        /* @var $helper Address */

        if (is_null($this->_defaultBilling)) {
            $address = $this->getPrimaryAddress('default_billing');

            if (($this->getEccErpaccountId() || $this->isSalesRep()) && (empty($address) || $helper->isMasquerading())) {
                $type = $this->isSupplier() ? 'supplier' : 'customer';
                //$erpAccount = $helper->getErpAccountInfo(null, $type);
                $erpAccount = $helper->getErpAccountInfo($this->getEccErpaccountId(), $type);

                if (!empty($erpAccount) && $erpAccount->getId() != $this->scopeConfig->getValue('customer/create_account/default_erpaccount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {

                    $address = $helper->getCustomerDefaultAddress($erpAccount, 'invoice', $this);
                    if (!empty($address)) {
                        if (!$address->getErpCode() || !$address->getErpCustomerGroupCode()) {
                            $erpcode = $erpAccount->getDefaultInvoiceAddressCode();
                            $store = $this->storeManager->getStore()->getId();
                            $erp_customer_group_code = $helper->getErpAccountNumber($erpAccount->getId(), $store);
                            $address->setErpCode($erpcode);
                            $address->setErpCustomerGroupCode($erp_customer_group_code);
                        }

                        $address = $address->toCustomerAddress($this, $erpAccount->getId());
                    }
                }
            }

            $this->_defaultBilling = empty($address) ? false : $address;
        }

        return $this->_defaultBilling;
    }

    /**
     * Get default customer shipping address
     *
     * @return \Magento\Customer\Model\Address
     */
    public function getPrimaryShippingAddress()
    {
        if ($this->_appState->getAreaCode() === 'adminhtml') {
            return parent::getPrimaryShippingAddress();
        }

        $helper = $this->commCustomerAddressHelper;
        /* @var $helper Address */

        if (is_null($this->_defaultShipping)) {
            $address = $this->getPrimaryAddress('default_shipping');

            if (empty($address) || $helper->isMasquerading()) {

                $type = $this->isSupplier() ? 'supplier' : 'customer';
                //$erpAccount = $helper->getErpAccountInfo(null, $type);
                $erpAccount = $helper->getErpAccountInfo($this->getEccErpaccountId(), $type);

                if (!empty($erpAccount)) {
                    $address = $helper->getCustomerDefaultAddress($erpAccount, 'delivery', $this);
                    if (!empty($address)) {
                        if (!$address->getErpCode() || !$address->getErpCustomerGroupCode()) {
                            $erpcode = $erpAccount->getDefaultDeliveryAddressCode();
                            $store = $this->storeManager->getStore()->getId();
                            $erp_customer_group_code = $helper->getErpAccountNumber($erpAccount->getId(), $store);
                            $address->setErpCode($erpcode);
                            $address->setErpCustomerGroupCode($erp_customer_group_code);
                        }
                        $address = $address->toCustomerAddress($this, $erpAccount->getId());
                    }
                }
            }

            $this->_defaultShipping = empty($address) ? false : $address;
        }

        return $this->_defaultShipping;
    }

    /**
     * Retrieve not default addresses
     *
     * @return array
     */
    public function getAdditionalAddresses()
    {
        if ($this->_appState->getAreaCode() === 'adminhtml') {
            return parent::getAdditionalAddresses();
        }

        $helper = $this->commCustomerAddressHelper;
        /* @var $helper Address */

        $addressItems = parent::getAdditionalAddresses();

        $addresses = array();
        $type = $this->isSupplier() ? 'supplier' : 'customer';

        $helper2 = $this->commHelper->create();
        /* @var $helper Epicor_Comm_Helper_Data */

        $erpAccount = $helper2->getErpAccountInfo(null, $type);
        $erpAddresses = array();

        if ($erpAccount && $erpAccount->getId() != $this->scopeConfig->getValue('customer/create_account/default_erpaccount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {

            $erpAddressItems = $helper->getCustomerAddresses($this);

            foreach ($erpAddressItems as $address) {
                /* @var $address Epicor_Comm_Model_Customer_Erpaccount_Address */
                if ($address->getErpCode() != $erpAccount->getDefaultDeliveryAddressCode() && $address->getErpCode() != $erpAccount->getDefaultInvoiceAddressCode()) {
                    $erpAddresses[$address->getErpCode()] = $address->toCustomerAddress($this);
                }
            }
        }

        foreach ($addressItems as $x => $address) {
            $erpCode = $address->getEccErpAddressCode();

            if (empty($erpCode) || isset($erpAddresses[$erpCode]) || empty($erpAddresses)) {
                $addresses[$x] = $address;
            }
        }

        return $addresses;
    }

    /**
     * Gets the customer addresses by type
     *
     * @param string $type Address type Address type delivery|invoice|registered.
     * @param boolean $isCheckout Is call from checkout.
     * @param boolean $countOnly Return count only.
     *
     * @return array
     * @throws LocalizedException localized Exception.
     */
    public function getAddressesByType($type, bool $isCheckout = false, bool $countOnly = false)
    {
        if (isset($this->_addressesByType[$type])) {
            return $this->_addressesByType[$type];
        }

        $helper = $this->commCustomerAddressHelper;
        /* @var $helper Address */

        $addresses = array();
        $b2bHierarchyMasquerade = $this->customerSession->getB2BHierarchyMasquerade();
        if (!$this->isSalesRep() && !$b2bHierarchyMasquerade) {
            $collection = $this->getAddressCollection()
                ->setCustomerFilter($this)
                ->addAttributeToSelect('*')
                ->addAttributeToSelect('ecc_is_invoice', 'left')
                ->addAttributeToSelect('ecc_is_delivery', 'left')
                ->addAttributeToSelect('ecc_is_registered', 'left')
                ->addExpressionAttributeToSelect(
                    'is_custom', 'IF((NOT(`at_ecc_is_invoice`.value <=> 1) AND NOT(`at_ecc_is_delivery`.value <=> 1) AND NOT(`at_ecc_is_registered`.value <=> 1)), 1 , 0)', array('ecc_is_invoice', 'ecc_is_delivery', 'ecc_is_registered')
                );

            $collection->getSelect()
                ->where(
                    '(`at_ecc_is_' . $type . '`.value = 1) ' .
                    'OR (NOT(`at_ecc_is_invoice`.value <=> 1) AND NOT(`at_ecc_is_delivery`.value <=> 1) AND NOT(`at_ecc_is_registered`.value <=> 1))'
                );

            $erpInfo = $this->getCustomerErpAccount();
            $erpCustomerGroupCode = $erpInfo->getData("erp_code");
            $attributes = [
                ['attribute' => 'ecc_erp_group_code', 'eq' => $erpCustomerGroupCode],
                ['attribute' => 'ecc_erp_group_code', 'null' => true],
            ];
            $collection->addAttributeToFilter($attributes, null, 'left');

            $addresses = $collection->getItems();
            $erpCount = $this->getErpAcctCounts();
            if ($helper->isMasquerading() && (is_array($erpCount) && count($erpCount) > 1)) {
                if ($this->getPrimaryBillingAddress()) {
                    $billingaddressid = $this->getPrimaryBillingAddress()->getId();
                    if (isset($addresses[$billingaddressid])) {
                        $addresses[$billingaddressid]->setIsDefaultBilling(true);
                    }
                }
                if ($this->getPrimaryShippingAddress()) {
                    $shippingaddressid = $this->getPrimaryShippingAddress()->getId();
                    if (isset($addresses[$shippingaddressid])) {
                        $addresses[$shippingaddressid]->setIsDefaultShipping(true);
                    }
                }
            }
        } else {
            $addresses = $this->getCustomAddresses($type, $isCheckout, $countOnly);
        }//end if

        $this->_addressesByType[$type] = $addresses;

        return $addresses;

    }//end getAddressByType()


    /**
     * Retrieve customer address array
     *
     * @return array
     */
    public function getAddresses()
    {
        if ($this->callParentGetAddresses()) {
            return parent::getAddresses();
        }

        if ($this->_addresses) {
            //  return $this->_addresses;
        }
        $helper = $this->commCustomerAddressHelper;
        /* @var $helper Address */
        //$this->_addresses = array();

        if (($this->isSalesRep() && !$helper->isMasquerading())
            || (!$this->isSalesRep() && $helper->isMasquerading())
            || (!$this->isSalesRep() && !$helper->isMasquerading())
            || $this->_giveRealAddresses()
        ) {
            $erpAcctCounts = $this->getErpAcctCounts();
            if (count($erpAcctCounts) > 1) {

                $addressCollections = $this->_addressesFactory->create();
                $addressCollections->setCustomerFilter(
                    $this
                )->addAttributeToSelect(
                    '*'
                );
                $addressCollections->addAttributeToSelect('ecc_erp_address_code');
                $erpInfo = $this->getCustomerErpAccount();
                $erpCustomerGroupCode = $erpInfo->getData("erp_code");
                $attributes = [
                    ['attribute' => 'ecc_erp_group_code', 'eq' => $erpCustomerGroupCode],
                    ['attribute' => 'ecc_erp_group_code', 'null' => true],
                ];
                $addressCollections->addAttributeToFilter($attributes, null, 'left');
            } else {
                $addressCollections = $this->getAddressesCollection();
            }
            $this->_addresses = $addressCollections->getItems();
            $erpCount = $this->getErpAcctCounts();
            if ($helper->isMasquerading() && (is_array($erpCount) && count($erpCount) > 1)) {

                if ($this->getPrimaryBillingAddress()) {
                    $billingaddressid = $this->getPrimaryBillingAddress()->getId();
                    if (isset($this->_addresses[$billingaddressid])) {
                        $this->_addresses[$billingaddressid]->setIsDefaultBilling(true);
                    }
                }
                if ($this->getPrimaryShippingAddress()) {
                    $shippingaddressid = $this->getPrimaryShippingAddress()->getId();
                    if (isset($this->_addresses[$shippingaddressid])) {
                        $this->_addresses[$shippingaddressid]->setIsDefaultShipping(true);
                    }
                }
            }
            elseif(!$this->isSalesRep() && $helper->isMasquerading()) {
                $this->_addresses = [];
                $erpAccount = $helper->getErpAccountInfo();
                if ($erpAccount) {
                    $erpAddresses = $erpAccount->getAddresses();

                    foreach ($erpAddresses as $erpAddress) {
                        /* @var $address \Epicor\Comm\Model\Customer\Erpaccount\Address */
                        $address = $erpAddress->toCustomerAddress($this, $erpAccount->getId());

                        $address->setId('erpaddress_' . $erpAddress->getId());

                        $this->_addresses[$address->getId()] = $address;
                    }
                }
            }

        } else {
            return parent::getAddresses();
        }
        return $this->_addresses;
    }

    /**
     * Retrieve customer model with customer data
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getDataModel()
    {
        $helper = $this->commCustomerAddressHelper;
        if (!$helper->isMasquerading()) {
            return parent::getDataModel();

        } else {
            $customerData = $this->getData();
            $addressesData = [];
            /** @var \Magento\Customer\Model\Address $address */
            foreach ($this->getAddresses() as $address) {
                $addressModel = $address->getDataModel();
                if ($address->getData('is_default_shipping')) {
                    $addressModel->setIsDefaultShipping(true);
                }
                if ($address->getData('is_default_billing')) {
                    $addressModel->setIsDefaultBilling(true);
                }
                $addressesData[] = $addressModel;
            }
            $customerDataObject = $this->customerDataFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $customerDataObject,
                $customerData,
                '\Magento\Customer\Api\Data\CustomerInterface'
            );
            $customerDataObject->setAddresses($addressesData)
                ->setId($this->getId());
            return $customerDataObject;
        }
    }

    /**
     * Determines whether to use the getAddresses function from parent
     *
     * @return boolean
     */
    public function callParentGetAddresses()
    {
        $isParentAddress = false;
        $controller = $this->request->getControllerName();
        $action = $this->request->getActionName();

        if ($this->_appState->getAreaCode() === 'adminhtml') {
            $isParentAddress = true;
        } else if (($controller == 'address' && $action == 'delete')) {
            $isParentAddress = true;
        }
        return $isParentAddress;
    }

    /**
     * Determines whether this url has to give the real cusotmer addresses
     * or can allow masqueraded ones
     *
     * @return boolean
     */
    private function _giveRealAddresses()
    {
        $avoid = false;
        $controller = $this->request->getControllerName();
        $action = $this->request->getActionName();

        if (($controller == 'onepage' && $action == 'saveOrder')) {
            $avoid = true;
        }

        return $avoid;
    }

    /**
     * Returns whether this customer is an erp customer
     *
     * @return boolean
     */
    public function isCustomer($session = true)
    {
        $erpAccount = false;
        $customer = false;
        $helper = $this->commHelper->create();

        if ($this->isSalesRep() && $helper->isMasquerading()) {
            $erpAccount = $helper->getErpAccountInfo(null, 'customer');
        } else if ($this->getEccErpaccountId()) {
            if ($session == true) {
                $erpAccount = $helper->getErpAccountInfo(null, 'customer');
            } else {
                $erpAccount = $this->getCustomerErpAccount();
            }
        }

        if ($erpAccount) {
            $customer = $erpAccount->isTypeB2b() ? true : false;
        }

        return $customer;
    }

    /**
     * Returns whether this customer is a supplier
     *
     * @return boolean
     */
    public function isSupplier()
    {
        return ($this->getEccSupplierErpaccountId()) ? true : false;
    }

    /**
     * Returns whether this customer is a sales rep
     *
     * @return boolean
     */
    public function isSalesRep()
    {
        return ($this->getEccErpAccountType() == 'salesrep') ? true : false;
    }

    /**
     * Returns whether this customer is a guest account
     *
     * @return boolean
     */
    public function isGuest($session = true)
    {
        $guest = false;

        if ($erpAccountId = $this->getEccErpaccountId()) {
            if ($session == true) {
                $helper = $this->commHelper->create();
                /* @var $helper Epicor_Comm_Helper_Data */
                $erpAccount = $helper->getErpAccountInfo($erpAccountId, 'customer');
            } else {
                $erpAccount = $this->getCustomerErpAccount();
            }

            $guest = $erpAccount->isTypeB2c() ? true : false;
        } else {
            $guest = (!$this->getEccErpaccountId() && !$this->getEccSupplierErpaccountId() && !$this->getEccSalesRepAccountId()) ? true : false;
        }

        return $guest;
    }

    /**
     * Returns whether this customer can create new addresses
     *
     * @param string $type Address type..
     *
     * @return mixed
     */
    public function isCustomAddressAllowed($type="shipping")
    {
        $typeAllowed     = $this->getData('ecc_allow_'.$type.'_address_create');
        $typeAllowedEval = $typeAllowed == null || $typeAllowed == 2;

        if ($typeAllowedEval) {
            return null;
        } else {
            return $typeAllowed;
        }

    }//end isCustomAddressAllowed()


    public function getReturnReasonCodes($storeId = null)
    {
        $erpAccountReasons = '';
        $storeId = ($storeId) ?: $this->storeManager->getStore()->getId();

        $type = $this->isSupplier() ? 'supplier' : 'customer';
        $codeType = $this->isCustomer() ? 'B' : 'C';

        $type = $this->isSupplier() ? 'supplier' : 'customer';

        $helper = $this->commHelper->create();
        /* @var $helper Epicor_Comm_Helper_Data */

        $erpAccount = $helper->getErpAccountInfo(null, $type);

        if ($erpAccount) {
            $erpAccountReasons = $erpAccount->getCompany();
            $erpAccountReasons .= $this->commMessagingHelper->create()->getUOMSeparator();
            $erpAccountReasons .= $erpAccount->getAccountNumber();
        }

        $resource = $this->resourceConnection;
        $reasonCodeTableName = $resource->getTableName('ecc_erp_mapping_reasoncode');
        $reasonCodeAccountsTableName = $resource->getTableName('ecc_erp_mapping_reasoncode_accounts');

        $sql = "
            SELECT
                Reason.code,
                coalesce(ReasonStore.description, Reason.description) AS description
            FROM
                " . $reasonCodeTableName . " Reason
            LEFT JOIN
                " . $reasonCodeTableName . " ReasonStore
            ON
                Reason.code = ReasonStore.code AND ReasonStore.store_id = '" . $storeId . "'
            LEFT JOIN
                " . $reasonCodeAccountsTableName . " ReasonAccount
            ON
                Reason.code = ReasonAccount.code
            WHERE
                Reason.store_id = 0
            AND
                (ReasonAccount.erp_account = '" . $erpAccountReasons . "' OR ReasonAccount.erp_account is null)
            AND
                (Reason.type = '" . $codeType . "' OR Reason.type is null)
        UNION
            SELECT
                ReasonOnlyStore.code, ReasonOnlyStore.description
            FROM
                " . $reasonCodeTableName . " ReasonOnlyStore
            LEFT JOIN
                " . $reasonCodeAccountsTableName . " ReasonOnlyStoreAccount
            ON
                ReasonOnlyStore.code = ReasonOnlyStoreAccount.code
            WHERE
                ReasonOnlyStore.store_id = '" . $storeId . "'
            AND
                NOT EXISTS(
                    SELECT
                        1
                    FROM
                        " . $reasonCodeTableName . "
                    WHERE
                        code = ReasonOnlyStore.code
                    AND
                        store_id = 0
                )
            AND
                (ReasonOnlyStoreAccount.erp_account = '" . $erpAccountReasons . "' OR ReasonOnlyStoreAccount.erp_account is null)
            AND
                (ReasonOnlyStore.type = '" . $codeType . "' OR ReasonOnlyStore.type is null)
        ";

        $results = $resource->getConnection('core_read')->fetchAll($sql);
        $reasonCodes = array();
        foreach ($results as $result) {
            $reasonCodes[$result['code']] = $result['description'];
        }
        return $reasonCodes;
    }

    // [Start Force Masqurade]

    /**
     * Check Customers Children for vaild erpacount for store
     *
     * @param string $type
     * @param int $store
     */
    public function setValidMasquradeAccountForStore($type = '', $store = null)
    {
        $children = $this->getCustomerErpAccount()->getChildAccounts($type, true);
        $stopMasqurading = true;
        $wipeCart = false;
        $helper = $this->commHelper->create();
        /* @var $helper Epicor_Comm_Helper_Data */
        foreach ($children as $childErpAccount) {
            /* @var $childErpAccount Epicor_Comm_Model_Customer_Erpaccount */
            if ($childErpAccount->isBrandingValidOnStore($store)) {
                $helper->startMasquerade($childErpAccount->getId());
                $stopMasqurading = false;
                $wipeCart = true;
                break;
            }
        }
        if ($stopMasqurading && count($children) > 0) {
            $helper->stopMasquerade();
            $wipeCart = true;
        }

        if ($wipeCart) {
            $helper->wipeCart();
        }
    }

    public function isForcedToMasqurade()
    {
        return $this->getCustomerErpAccount() && count($this->getCustomerErpAccount()->getChildAccounts('T', true)) > 0;
    }

    public function canMasqueradeAs($erpAccountId)
    {
        $helper = $this->commHelper->create();
        /* @var $helper \Epicor\Comm\Helper\Data */

        $canMasquerade = false;
        $customerErpAccount = $helper->getErpAccountInfo(null, 'customer', null, false);
        /* @var $customerErpAccount \Epicor\Comm\Model\Customer\Erpaccount */

        if ($customerErpAccount && $customerErpAccount->isMasqueradeAllowed()) {
            $childAccounts = $customerErpAccount->getAllChildAccountIds();
            if (in_array($erpAccountId, $childAccounts)) {
                $canMasquerade = true;
            }
        }

        $transportObject = $this->dataObjectFactory->create();
        $transportObject->setMasqueradeAs($canMasquerade);
        $this->eventManager->dispatch('epicor_comm_customer_can_masquerade_as', array('customer' => $this, 'erp_account_id' => $erpAccountId, 'transport' => $transportObject));
        $canMasquerade = $transportObject->getMasqueradeAs();

        return $canMasquerade;
    }

    // [End Force Masqurade]

    /**
     * Returns whether this customer can masquerade as a child account
     *
     * @return boolean
     */
    public function isMasqueradeAllowed()
    {
        return $this->checkConfig('epicor_comm_erp_accounts/masquerade/allow', 'ecc_allow_masquerade', 'isMasqueradeAllowed');
    }

    /**
     * Returns whether this customer can clear cart on masquerade
     *
     * @return boolean
     */
    public function isMasqueradeCartClearAllowed()
    {
        return $this->checkConfig('epicor_comm_erp_accounts/masquerade/allow_cart_clear', 'ecc_allow_masquerade_cart_clear', 'isMasqueradeCartClearAllowed');
    }

    /**
     * Returns whether this customer can save cart on masquerade
     *
     * @return boolean
     */
    public function isMasqueradeCartSaveAllowed()
    {
        return $this->checkConfig('epicor_comm_erp_accounts/masquerade/allow_cart_save', 'ecc_allow_masquerade_cart_save', 'isMasqueradeCartSaveAllowed');
    }

    /**
     * Returns whether this customer can reprice cart on masquerade
     *
     * @return boolean
     */
    public function isMasqueradeCartRepriceAllowed()
    {
        return $this->checkConfig('epicor_comm_erp_accounts/masquerade/allow_cart_reprice', 'ecc_allow_masquerade_cart_reprice', 'isMasqueradeCartRepriceAllowed');
    }

    /**
     * Returns whether this customer can create new addresses
     *
     * @return boolean
     */
    public function checkConfig($globalPath, $dataPath, $function, $realAccount = true)
    {
        $allowed = false;

        $globalAllow = $this->scopeConfig->getValue($globalPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $customerAllow = $this->getData($dataPath);
        $commHelper = $this->commHelper->create();
        /* @var $commHelper Epicor_Comm_Helper_Data */
        if ($realAccount) {
            $erpAccount = $commHelper->getErpAccountInfo($this->getEccErpaccountId());
        } else {
            $erpAccount = $commHelper->getErpAccountInfo();
        }
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
        if ($globalAllow == 'forceyes') {
            $allowed = true;
        } else if ($globalAllow == 'forceno') {
            $allowed = false;
        } else if ($erpAccount && ($customerAllow == null || $customerAllow == 2)) {
            $allowed = $erpAccount->$function();
        } else if ($customerAllow == 1) {
            $allowed = true;
        }

        return $allowed;
    }

    public function beforeSave()
    {
        if (!$this->_optimizedLocations && (!empty($this->_updatedLocations) || !empty($this->_newLocations) || !empty($this->_delLocations))) {
            $this->_optimizeLocations();
        }
        $this->processContracts();
        return parent::beforeSave();
    }

    public function afterSave()
    {
        $this->locationSave();
        $this->saveLists();

        return parent::afterSave();
    }

    public function locationSave()
    {
        foreach ($this->_delLocations as $locationCode) {

            $links = $this->commResourceLocationLinkCollectionFactory->create();
            /* @var $links Epicor_Comm_Model_Resource_Location_Link_Collection */
            $links->addFieldToFilter('entity_id', $this->getId());
            $links->addFieldToFilter('entity_type', \Epicor\Comm\Model\Location\Link::ENTITY_TYPE_CUSTOMER);
            $links->addFieldToFilter('location_code', $locationCode);

            $link = $links->getFirstItem();
            /* @var $link Epicor_Comm_Model_Location_Link */

            if (!$link->isObjectNew()) {
                $link->delete();
            }
        }

        foreach ($this->_updatedLocations as $locationCode => $linkType) {

            $links = $this->commResourceLocationLinkCollectionFactory->create();
            /* @var $links Epicor_Comm_Model_Resource_Location_Link_Collection */
            $links->addFieldToFilter('entity_id', $this->getId());
            $links->addFieldToFilter('entity_type', \Epicor\Comm\Model\Location\Link::ENTITY_TYPE_CUSTOMER);
            $links->addFieldToFilter('location_code', $locationCode);

            $link = $links->getFirstItem();
            /* @var $link Epicor_Comm_Model_Location_Link */

            $link->setEntityId($this->getId());
            $link->setEntityType(\Epicor\Comm\Model\Location\Link::ENTITY_TYPE_CUSTOMER);
            $link->setLocationCode($locationCode);
            $link->setLinkType($linkType);

            $link->save();
        }

        foreach ($this->_newLocations as $locationCode => $linkType) {

            $links = $this->commResourceLocationLinkCollectionFactory->create();
            /* @var $links Epicor_Comm_Model_Resource_Location_Link_Collection */
            $links->addFieldToFilter('entity_id', $this->getId());
            $links->addFieldToFilter('entity_type', \Epicor\Comm\Model\Location\Link::ENTITY_TYPE_CUSTOMER);
            $links->addFieldToFilter('location_code', $locationCode);

            $link = $links->getFirstItem();
            /* @var $link Epicor_Comm_Model_Location_Link */

            $link->setEntityId($this->getId());
            $link->setEntityType(\Epicor\Comm\Model\Location\Link::ENTITY_TYPE_CUSTOMER);
            $link->setLocationCode($locationCode);
            $link->setLinkType($linkType);

            $link->save();
        }


    }

    /**
     * Loads locations for this ERP Account
     *
     * @return array
     */
    protected function _loadLocationLinks()
    {
        if (is_null($this->_locations)) {
            $this->_locations = array();
            if (!$this->isObjectNew() && !$this->isSupplier()) {
                $linkType = $this->getEccLocationLinkType();
                if (!empty($linkType)) {
                    $links = $this->commResourceLocationLinkCollectionFactory->create();
                    /* @var $links Epicor_Comm_Model_Resource_Location_Link_Collection */
                    $links->addFieldToFilter('entity_id', $this->getId());
                    $links->addFieldToFilter('entity_type', \Epicor\Comm\Model\Location\Link::ENTITY_TYPE_CUSTOMER);

                    foreach ($links as $link) {
                        /* @var $link Epicor_Comm_Model_Location_Link */
                        $this->_locations[$link->getLocationCode()] = $link->getLinkType();
                    }
                }
            }
        }

        return $this->_locations;
    }

    public function getAllowedLocationCodes()
    {
        return $this->getAllowedLocations(true);
    }

    /**
     * Get Allowed Locations
     * @param boolean $session
     * @return array
     */
    public function getAllowedLocations($codes = false)
    {
        if (empty($this->_allowedLocations)) {
            $this->_allowedLocations = array();
            $this->_allowedLocationCodes = array();

            $helper = $this->commHelper->create();
            /* @var $helper Epicor_Comm_Helper_Data */

            if ($helper->isMasquerading()) {
                $erpAccount = $helper->getErpAccountInfo(null, 'customer');
            } else {
                $erpAccount = $this->getCustomerErpAccount();
            }

            $locations = array();

            if ($erpAccount) {
                if ($this->getEccLocationLinkType()) {
                    $locations = array_keys($this->_loadLocationLinks($this->getEccLocationLinkType()));
                    if ($this->getEccLocationLinkType() == \Epicor\Comm\Model\Location\Link::LINK_TYPE_EXCLUDE) {
                        $erpLocations = $erpAccount->getAllowedLocationCodes();
                        $locations = array_diff($erpLocations, $locations);
                    }
                } else {
                    $locations = $erpAccount->getAllowedLocationCodes();
                }
            }
            sort($locations);

            $collection = $this->commResourceLocationCollectionFactory->create();
            /* @var $collection Epicor_Comm_Model_Resource_Location_Collection */
            $collection->addFieldToFilter('code', array('in' => array_map('strval', $locations)));
            $collection->setOrder('sort_order', 'asc');
            $collectionData = is_null($collection->getItems()) ? $collection->getData() : $collection->getItems();
            foreach ($collectionData as $location) {
                if (is_array($location)) {
                    $this->_allowedLocations[$location['code']] = $location['code'];
                } else {
                    $this->_allowedLocations[$location->getCode()] = $location;
                }
            }
            $this->_allowedLocationCodes = array_keys($this->_allowedLocations);
        }

        return ($codes) ? $this->_allowedLocationCodes : $this->_allowedLocations;
    }

    public function isLocationAllowed($locationCode)
    {
        $locations = $this->getAllowedLocationCodes();
        return in_array($locationCode, $locations);
    }

    private function _getCurrentLocations()
    {
        $links = $this->commResourceLocationLinkCollectionFactory->create();
        /* @var $links Epicor_Comm_Model_Resource_Location_Link_Collection */
        $links->addFieldToFilter('entity_id', $this->getId());
        $links->addFieldToFilter('entity_type', \Epicor\Comm\Model\Location\Link::ENTITY_TYPE_CUSTOMER);

        $ret = array();
        foreach ($links->getData() as $data) {
            $ret[$data['location_code']] = $data;
        }

        return $ret;
    }

    private function _deleteAllLocations($allowedLocationsCodes, $currentLinks, $linkType)
    {

        $this->setEccLocationLinkType($linkType);

        if (is_array($allowedLocationsCodes)) {
            foreach ($allowedLocationsCodes as $code) {
                if (isset($currentLinks[$code])) {
                    $this->_delLocations[$code] = $code;
                }
            }
        }
    }

    private function _includeExcludeLocations($allowedLocationsCodes, $locations, $currentLinks, $inverse, $eccLinkType, $linkType)
    {

        $this->setEccLocationLinkType($eccLinkType);

        foreach ($allowedLocationsCodes as $code) {
            if ((isset($locations[$code]) && !$inverse) || (!isset($locations[$code]) && $inverse)) {
                if (isset($currentLinks[$code])) {
                    $this->_updatedLocations[$code] = $linkType;
                } else {
                    $this->_newLocations[$code] = $linkType;
                }
            } else {
                $this->_delLocations[$code] = $code;
            }
        }
    }

    public function updateLocationsFromXML($locations, $linkType = \Epicor\Comm\Model\Location\Link::LINK_TYPE_INCLUDE)
    {

        $currentLinks = $this->_getCurrentLocations();

        $allowedLocationsCodes = $this->getAllowedLocationCodes();
        $allowedLocationsCodesValues = array_values($allowedLocationsCodes);
        $totalLocations = count($locations);
        $totalAllowedLocationsCodes = count($allowedLocationsCodes);

        if ($totalLocations > 0) {

            if ($totalAllowedLocationsCodes == $totalLocations && !array_diff($allowedLocationsCodesValues, $locations)) {
                $this->_deleteAllLocations($allowedLocationsCodes, $currentLinks, '');
            } else {
                switch ($linkType) {
                    case \Epicor\Comm\Model\Location\Link::LINK_TYPE_INCLUDE:

                        $subs = $totalAllowedLocationsCodes - $totalLocations;
                        $diff = array_diff($allowedLocationsCodesValues, $locations);

                        if ($subs == 1 && count($diff) == 1) {

                            $this->_includeExcludeLocations($allowedLocationsCodes, $locations, $currentLinks, true, \Epicor\Comm\Model\Location\Link::LINK_TYPE_EXCLUDE, \Epicor\Comm\Model\Location\Link::LINK_TYPE_EXCLUDE);
                        } else {
                            $this->_includeExcludeLocationsCuco($locations);
                        }

                        break;
                    case \Epicor\Comm\Model\Location\Link::LINK_TYPE_EXCLUDE:

                        if ($totalLocations == 1) {

                            $this->_includeExcludeLocations($allowedLocationsCodes, $locations, $currentLinks, false, $linkType, $linkType);
                        } else {

                            $this->_includeExcludeLocations($allowedLocationsCodes, $locations, $currentLinks, true, \Epicor\Comm\Model\Location\Link::LINK_TYPE_INCLUDE, \Epicor\Comm\Model\Location\Link::LINK_TYPE_INCLUDE);
                        }

                        break;
                }
            }
        } else {
            $this->_deleteAllLocations($allowedLocationsCodes, $currentLinks, '');
        }

        $this->_optimizedLocations = true;
        $this->_hasDataChanges = true;
    }

    /**
     * Updates a Customers locations based on the array provided
     *
     * @param array $locations
     */
    public function updateLocations($locations, $linkType = \Epicor\Comm\Model\Location\Link::LINK_TYPE_INCLUDE)
    {
        $currentLinks = $this->_loadLocationLinks();
        $custLinkType = $this->getEccLocationLinkType();

        if ($linkType != $custLinkType) {
            foreach ($currentLinks as $locationCode => $type) {
                $this->_deleteLocationLink($locationCode);
            }
            $this->setEccLocationLinkType($linkType);
            $custLinkType = $linkType;
        } else {
            foreach ($currentLinks as $locationCode => $type) {
                if (!in_array($locationCode, $locations)) {
                    $this->_deleteLocationLink($locationCode);
                }
            }
        }

        foreach ($locations as $locationCode) {
            $this->_addLocationLink($locationCode, $linkType);
        }

        $this->_hasDataChanges = true;

        if (!$this->_optimizedLocations && (!empty($this->_updatedLocations) || !empty($this->_newLocations) || !empty($this->_delLocations))) {
            $this->_optimizeLocations();
        }
        $this->locationSave();
    }

    public function excludeLocation($locationCode)
    {
        $linkType = $this->getEccLocationLinkType();

        if (is_null($linkType)) {
            $linkType = \Epicor\Comm\Model\Location\Link::LINK_TYPE_EXCLUDE;
            $this->setEccLocationLinkType(\Epicor\Comm\Model\Location\Link::LINK_TYPE_EXCLUDE);
        }

        if ($linkType == \Epicor\Comm\Model\Location\Link::LINK_TYPE_EXCLUDE) {
            $this->_addLocationLink($locationCode, $linkType);
        } else {
            $this->_deleteLocationLink($locationCode);
        }

        $this->_hasDataChanges = true;
    }

    public function includeLocation($locationCode)
    {
        $linkType = $this->getEccLocationLinkType();

        if (is_null($linkType)) {
            $linkType = \Epicor\Comm\Model\Location\Link::LINK_TYPE_INCLUDE;
            $this->setEccLocationLinkType(\Epicor\Comm\Model\Location\Link::LINK_TYPE_INCLUDE);
        }

        if ($linkType == \Epicor\Comm\Model\Location\Link::LINK_TYPE_EXCLUDE) {
            $this->_deleteLocationLink($locationCode);
        } else {
            $this->_addLocationLink($locationCode, $linkType);
        }

        $this->_hasDataChanges = true;
    }

    /**
     * Adds a location link to the Customer
     *
     * @param string $locationCode
     * @param string $linkType
     */
    protected function _addLocationLink($locationCode, $linkType)
    {
        $this->_loadLocationLinks();

        if (isset($this->_locations[$locationCode])) {
// only update the location if it's different
            if ($this->_locations[$locationCode] != $linkType) {
                $this->_updatedLocations[$locationCode] = $linkType;
            }
        } else {
            $this->_newLocations[$locationCode] = $linkType;
        }

        if (isset($this->_delLocations[$locationCode])) {
            unset($this->_delLocations[$locationCode]);
        }

        $this->_hasDataChanges = true;
    }

    /**
     * Deletes a location code from the ERP account
     *
     * @param string $locationCode
     */
    protected function _deleteLocationLink($locationCode)
    {
        if (!in_array($locationCode, $this->_delLocations)) {
            $this->_delLocations[$locationCode] = $locationCode;
        }

        $this->_hasDataChanges = true;
    }

    protected function _optimizeLocations()
    {
        $this->_locations = null;
        $origCurrentLinks = $this->_loadLocationLinks();
        $currentLinks = $origCurrentLinks;

        foreach ($this->_updatedLocations as $locationCode => $linkType) {
            $currentLinks[$locationCode] = $linkType;
        }

        foreach ($this->_newLocations as $locationCode => $linkType) {
            $currentLinks[$locationCode] = $linkType;
        }

        foreach ($this->_delLocations as $locationCode) {
            if (isset($currentLinks[$locationCode])) {
                unset($currentLinks[$locationCode]);
            }
        }

        $erpAllowed = $this->getCustomerErpAccount()->getAllowedLocationCodes();

        if ($this->getEccLocationLinkType() == \Epicor\Comm\Model\Location\Link::LINK_TYPE_INCLUDE) {
            $customerAllowed = array_keys($currentLinks);
        } else {
            if (!empty($currentLinks)) {
                $collection = $this->commResourceLocationCollectionFactory->create();
                /* @var $collection Epicor_Comm_Model_Resource_Location_Collection */
                $collection->addFieldToFilter('code', array('nin' => array_keys($currentLinks)));
                $collection->addFieldToFilter('code', array('in' => $erpAllowed));
                $customerAllowed = array();
                foreach ($collection->getItems() as $location) {
                    $customerAllowed[] = $location->getCode();
                }
            } else {
                $customerAllowed = $erpAllowed;
            }
        }

        $diff = array_diff($erpAllowed, $customerAllowed);

        if (!empty($diff)) {
            $helper = $this->commLocationsHelper->create();
            /* @var $helper \Epicor\Comm\Helper\Locations */
            $optimized = $helper->optimizeLocations(array_keys($currentLinks), $diff, $this->getEccLocationLinkType());

            $this->_newLocations = array();
            $this->_updatedLocations = array();
            $this->_delLocations = array();
            $this->setEccLocationLinkType($optimized['link_type']);

            foreach ($origCurrentLinks as $locationCode => $type) {
                $this->_deleteLocationLink($locationCode);
            }

            foreach ($optimized['locations'] as $locationCode) {
                $this->_addLocationLink($locationCode, $optimized['link_type']);
            }
        } else {
            $this->setEccLocationLinkType('');
            $this->_newLocations = array();
            $this->_updatedLocations = array();
            $this->_delLocations = array();
            foreach ($origCurrentLinks as $locationCode => $type) {
                $this->_deleteLocationLink($locationCode);
            }
        }

        $this->_optimizedLocations = true;
    }

    /**
     * get Sales Rep Account
     * @return \Epicor\SalesRep\Model\Account
     */
    public function getSalesRepAccount()
    {
        if (empty($this->_salesrepAccount) && $this->isSalesRep()) {
            $this->_salesRepAccount = $this->salesRepAccountFactory->create()->load($this->getEccSalesRepAccountId());
            /* @var $this ->_salesRepAccount Epicor_SalesRep_Model_Account */
        }

        return $this->_salesRepAccount;
    }

    public function getEccErpAccountType()
    {
        $erpAccountType = $this->getData('ecc_erp_account_type');
        if (empty($erpAccountType)) {
            $helper = $this->commonAccountSelectorHelper->create();
            /* @var $helper \Epicor\Common\Helper\Account\Selector */
            $erpAccountType = $helper->getAccountTypeForCustomer($this);
        }

        return $erpAccountType;
    }

    /**
     * Retrives Lists from the Customer
     *
     * @return array
     */
    public function getLists()
    {
        if (!$this->_lists) {
            $collection = $this->listsResourceListModelCollectionFactory->create();
            /* @var $collection Epicor_Lists_Model_Resource_List_Collection */

            $collection->filterByCustomer($this->getId());

            $lists = array();

            foreach ($collection->getItems() as $item) {
                /* @var $item Epicor_Lists_Model_ListModel */
                $lists[$item->getId()] = $item;
            }

            $this->_lists = $lists;
        }

        return $this->_lists;
    }

    /**
     * Add Lists to the Customer
     *
     * @param array|int|object $lists
     */
    public function addLists($lists)
    {
        if (!is_array($lists)) {
            $lists = array($lists);
        }

        foreach ($lists as $list) {
            if ($list) {
                $listId = (is_object($list) ? $list->getId() : $list);
                $this->_newLists[$listId] = $list;
                if (isset($this->_delLists[$listId])) {
                    unset($this->_delLists[$listId]);
                }

                $this->_hasDataChanges = true;
            }
        }
    }

    /**
     * Removes Lists from the Customer
     *
     * @param array|int|object $lists
     */
    public function removeLists($lists)
    {
        if (!is_array($lists)) {
            $lists = array($lists);
        }

        foreach ($lists as $list) {
            $listId = (is_object($list) ? $list->getId() : $list);
            $this->_delLists[$listId] = $list;
            if (isset($this->_newLists[$listId])) {
                unset($this->_newLists[$listId]);
            }
        }

        $this->_hasDataChanges = true;
    }

    public function processLists($postdata)
    {
        $existingLists = array_keys($this->getLists());
        $addLists = array_diff($postdata, $existingLists);
        $removeLists = array_diff($existingLists, $postdata);

        $existingLists = $this->getLists();
        foreach ($addLists as $key => $listId) {
            if (!array_key_exists($listId, $existingLists)) {
                $list = $this->listsListModelCustomerFactory->create();
                /* @var $list Epicor_Lists_Model_ListModelModel_Customer */
                $list->setCustomerId($this->getId());
                $list->setListId($listId);
                $list->save();
            }
        }
        $listIds = [];
        foreach ($removeLists as $key => $listId) {
            if (array_key_exists($listId, $existingLists)) {
                $listIds[] = $listId;
            }
        }
        if (count($listIds) > 0) {
            $listsCollection = $this->listsResourceListModelCustomerCollectionFactory->create();
            /* @var $listsCollection Epicor_Lists_Model_Resource_List_Customer_Collection */
            $listsCollection->addFieldtoFilter('list_id', array('in' => $listIds));
            $listsCollection->addFieldtoFilter('customer_id', $this->getId());

            foreach ($listsCollection->getItems() as $item) {
                $item->delete();
            }
        }


    }

    public function saveLists()
    {
        $existingLists = $this->getLists();
        foreach ($this->_newLists as $listId => $list) {
            if (!array_key_exists($listId, $existingLists)) {
                $list = $this->listsListModelCustomerFactory->create();
                /* @var $list Epicor_Lists_Model_ListModel_Customer */
                $list->setCustomerId($this->getId());
                $list->setListId($listId);
                $list->save();
            }
        }
        $this->_newLists = array();


        $listIds = array();
        foreach ($this->_delLists as $listId => $list) {
            if (array_key_exists($listId, $existingLists)) {
                $listIds[] = $listId;
            }
        }

        if (count($listIds) > 0) {
            $listsCollection = $this->listsResourceListModelCustomerCollectionFactory->create();
            /* @var $listsCollection Epicor_Lists_Model_Resource_List_Customer_Collection */
            $listsCollection->addFieldtoFilter('list_id', array('in' => $listIds));
            $listsCollection->addFieldtoFilter('customer_id', $this->getId());

            foreach ($listsCollection->getItems() as $item) {
                $item->delete();
            }
        }
        $this->_delLists = array();
    }

    /**
     * Processes contract data
     */
    protected function processContracts()
    {
        $data = $this->request->getParams();
        $model = $this;
        $contractArray = array(
            'ecc_contract_shipto_default', 'ecc_contract_shipto_date',
            'ecc_contract_shipto_prompt', 'ecc_contract_header_selection',
            'ecc_contract_header_prompt', 'ecc_contract_header_always',
            'ecc_contract_line_selection', 'ecc_contract_line_prompt', 'ecc_contract_line_always',
            'ecc_contracts_filter', 'ecc_default_contract', 'ecc_default_contract_address'
        );
        foreach ($contractArray as $contract) {
            if (isset($data[$contract])) {
                if ($contract == 'ecc_contracts_filter') {
                    if (is_array($data['ecc_contracts_filter'])) {
                        $data['ecc_contracts_filter'] = implode(',', $data['ecc_contracts_filter']);
                    }
                }
                $data[$contract] = $data[$contract] == '' ? null : $data[$contract];
                $model->setData($contract, $data[$contract]);
            }
        }
    }

    /**
     * Gets Contract ship to settings for this customer
     *
     * @return array
     */
    public function getContractShipToSettings()
    {
        $settings = array(
            'shipto_default' => $this->getEccContractShiptoDate(),
            'shipto_date' => $this->getEccContractShiptoDefault(),
            'shipto_prompt' => $this->getEccContractShiptoPrompt()
        );

        if (
            is_null($settings['shipto_default']) ||
            is_null($settings['shipto_date']) ||
            is_null($settings['shipto_prompt'])
        ) {
            $erpSettings = $this->getErpAccountContractShipToSettings();
            foreach ($settings as $key => $value) {
                if (is_null($value)) {
                    $settings[$key] = $erpSettings[$key];
                }
            }
        }

        return $settings;
    }

    /**
     * Gets Contract Ship to settings from ERP Account
     *
     * @return array
     */
    protected function getErpAccountContractShipToSettings()
    {
        $helper = $this->listsFrontendContractHelper->create();
        /* @var $helper Epicor_Lists_Helper_Frontend_Contract */
        $erpAccount = $helper->getSessionErpAccount();
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */

        $settings = array(
            'shipto_default' => '',
            'shipto_date' => '',
            'shipto_prompt' => ''
        );

        if ($erpAccount) {
            $settings = $erpAccount->getContractShipToSettings();
        }

        return $settings;
    }

    /**
     * Gets Contract header settings for this customer
     *
     * @return array
     */
    public function getContractHeaderSettings()
    {
        $settings = array(
            'header_selection' => $this->getEccContractHeaderSelection(),
            'header_prompt' => $this->getEccContractHeaderPrompt(),
            'header_always' => $this->getEccContractHeaderAlways()
        );

        if (
            is_null($settings['header_selection']) ||
            is_null($settings['header_prompt']) ||
            is_null($settings['header_always'])
        ) {
            $erpSettings = $this->getErpAccountContractHeaderSettings();

            foreach ($settings as $key => $value) {
                if (is_null($value)) {
                    $settings[$key] = $erpSettings[$key];
                }
            }
        }

        return $settings;
    }

    /**
     * Gets Contract Header settings from ERP Account
     *
     * @return array
     */
    protected function getErpAccountContractHeaderSettings()
    {
        $helper = $this->listsFrontendContractHelper->create();
        /* @var $helper Epicor_Lists_Helper_Frontend_Contract */
        $erpAccount = $helper->getSessionErpAccount();
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */

        $settings = array(
            'header_selection' => '',
            'header_prompt' => '',
            'header_always' => ''
        );

        if ($erpAccount) {
            $settings = $erpAccount->getContractHeaderSettings();
        }

        return $settings;
    }

    /**
     * Gets Contract line settings for this customer
     *
     * @return array
     */
    public function getContractLineSettings()
    {

        $settings = array(
            'line_selection' => $this->getEccContractLineSelection(),
            'line_prompt' => $this->getEccContractLinePrompt(),
            'line_always' => $this->getEccContractLineAlways()
        );

        if (
            is_null($settings['line_selection']) ||
            is_null($settings['line_prompt']) ||
            is_null($settings['line_always'])
        ) {
            $erpSettings = $this->getErpAccountContractLineSettings();

            foreach ($settings as $key => $value) {
                if (is_null($value)) {
                    $settings[$key] = $erpSettings[$key];
                }
            }
        }

        return $settings;
    }

    /**
     * Gets Contract Line settings from ERP Account
     *
     * @return array
     */
    protected function getErpAccountContractLineSettings()
    {
        $helper = $this->listsFrontendContractHelper->create();
        /* @var $helper \Epicor\Lists\Helper\Frontend\Contract */
        $erpAccount = $helper->getSessionErpAccount();
        /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount */

        $settings = array(
            'line_selection' => '',
            'line_prompt' => '',
            'line_always' => ''
        );

        if ($erpAccount) {
            $settings = $erpAccount->getContractLineSettings();
        }

        return $settings;
    }

    /* Retrieve attribute set id for customer.
     *
     * @return int
     *
     */
    public function getAttributeSetId()
    {
        return parent::getAttributeSetId() ?: $this->metadataService::ATTRIBUTE_SET_ID_CUSTOMER;
    }

    /**
     * Loads locations for this ERP Account
     *
     * @return array
     */
    protected function _loadLocationLinksCuco()
    {
        if (is_null($this->_locations) && !$this->isObjectNew()) {
            $this->_locations = array();
            $links = $this->commResourceLocationLinkCollectionFactory->create();
            /* @var $links \Epicor\Comm\Model\ResourceModel\Location\Link\Collection */
            $links->addFieldToFilter('entity_id', $this->getId());
            $links->addFieldToFilter('entity_type', \Epicor\Comm\Model\Location\Link::ENTITY_TYPE_CUSTOMER);

            foreach ($links as $link) {
                /* @var $link \Epicor\Comm\Model\Location\Link */
                $this->_locations[$link->getLocationCode()] = $link->getLinkType();
            }
        }

        return $this->_locations;
    }

    public function getAllowedLocationCodesCuco()
    {
        return $this->getAllowedLocationsCuco(true);
    }

    /**
     * Get Allowed Locations
     * @param boolean $session
     * @return array
     */
    public function getAllowedLocationsCuco($codes = false)
    {
        if (empty($this->_allowedLocations)) {
            $this->_allowedLocations = array();
            $this->_allowedLocationCodes = array();

            $helper = $this->commHelper->create();
            /* @var $helper Epicor_Comm_Helper_Data */

            if ($helper->isMasquerading()) {
                $erpAccount = $helper->getErpAccountInfo(null, 'customer');
            } else {
                $erpAccount = $this->getCustomerErpAccount();
            }

            $locations = array();

            if ($erpAccount) {
                $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
                $object_Manager = $bootstrap->getObjectManager();

                $app_state = $object_Manager->get('Epicor\Comm\Model\Customer\Erpaccount');
                if ($this->getEccLocationLinkType()) {
                    $locations = array_keys($this->_loadLocationLinks($this->getEccLocationLinkType()));
                    if ($this->getEccLocationLinkType() == \Epicor\Comm\Model\Location\Link::LINK_TYPE_EXCLUDE) {
                        $erpLocations = $app_state->getAllowedLocationCodes();
                        $locations = array_diff($erpLocations, $locations);
                    } else {
                        $erpLocations = $app_state->getAllowedLocationCodes();
                        $locations = array_diff($erpLocations, $locations);
                    }
                } else {
                    $locations = $erpAccount->getAllowedLocationCodes();
                }
            }

            sort($locations);
            $collection = $this->commResourceLocationCollectionFactory->create();
            /* @var $collection Epicor_Comm_Model_Resource_Location_Collection */

            $collection->addFieldToFilter('code', array('in' => $locations));
            $collection->setOrder('sort_order', 'asc');
            foreach ($collection->getData() as $location) {
                $this->_allowedLocations[$location['code']] = $location['code'];
            }

            $this->_allowedLocationCodes = array_keys($this->_allowedLocations);
        }

        return ($codes) ? $this->_allowedLocationCodes : $this->_allowedLocations;
    }

    /**
     * Deletes a location code from the ERP account
     *
     * @param string $locationCode
     */
    public function deleteLocationLink($locationCode)
    {
        if (!in_array($locationCode, $this->_delLocations)) {
            $this->_delLocations[] = $locationCode;
            /**
             * When CUCO update only location then
             * Customer is modified returns false and
             * Location is not going to update.
             * So adding/update only location via CUCO,
             * Customer object should require to modified.
             */
            $this->setData("updated_at","");
        }
    }

    /**
     * Adds a location code to the customer account
     *
     * @param string $locationCode
     * @param string $linkType
     */
    public function addLocationLink($locationCode, $linkType)
    {
        $this->_loadLocationLinks();

        if (isset($this->_locations[$locationCode])) {
            // only update the location if it's different
            if ($this->_locations[$locationCode] != $linkType) {
                $this->_updatedLocations[$locationCode] = $linkType;
                /**
                 * When CUCO update only location then
                 * Customer is modified returns false and
                 * Location is not going to update.
                 * So adding/update only location via CUCO,
                 * Customer object should require to modified.
                 */
                $this->setData("updated_at","");
            }
        } else {
            $this->_newLocations[$locationCode] = $linkType;
            /**
             * When CUCO update only location then
             * Customer is modified returns false and
             * Location is not going to update.
             * So adding/update only location via CUCO,
             * Customer object should require to modified.
             */
            $this->setData("updated_at","");
        }

        if (isset($this->_delLocations[$locationCode])) {
            unset($this->_delLocations[$locationCode]);
        }
    }

    private function _includeExcludeLocationsCuco($locations, $linkType = \Epicor\Comm\Model\Location\Link::LINK_TYPE_INCLUDE)
    {
        $currentLinks = $this->_loadLocationLinksCuco();
        $this->setLocationLinkType($linkType);
        if (is_array($currentLinks)) {
            foreach ($currentLinks as $locationCode => $type) {
                if (!in_array($locationCode, $locations)) {
                    $this->deleteLocationLink($locationCode);
                }
            }
        }

        if (is_array($locations)) {
            foreach ($locations as $locationCode) {
                $this->addLocationLink($locationCode, $linkType);
            }
        }
    }

    /**
     * Returns
     * @return boolean
     */
    public function getErpAcctCounts()
    {
        if (!$this->registry->registry('erp_acct_counts_' . $this->getEntityId())) {
            $this->_erpAcctCounts = $this->erpAccountFactory->create()->setData(['customer_id' => $this->getEntityId()])->getErpAcctCounts();
            $this->registry->unregister('erp_acct_counts_' . $this->getEntityId());
            $this->registry->register('erp_acct_counts_' . $this->getEntityId(), $this->_erpAcctCounts);
        }
        return $this->registry->registry('erp_acct_counts_' . $this->getEntityId());
    }

    /**
     * Returns all Erp Account Ids
     * @return boolean
     */
    public function getAllErpAcctids()
    {
        if ($this->_allErpAcctids == false) {
            $this->_allErpAcctids = $this->erpAccountFactory->create()->setData(['customer_id' => $this->getEntityId()])->getAllErpAcctids();
        }
        return $this->_allErpAcctids;
    }

    public function deleteErpAcctById($erpAcctId)
    {
        return $this->erpAccountFactory->create()->setData(['customer_id' => $this->getEntityId(), 'erp_account_id' => $erpAcctId])->deleteByErpId();
    }

    /**
     * Return EccErpaccountId
     *
     * @return integer
     */
    public function getEccErpaccountId()
    {
        if ($this->getEntityId()) {
            $b2bHierarchyMasquerade = $this->customerSession->getB2BHierarchyMasquerade();
            if ($this->customerSession->getMasqueradeAccountId() && !$b2bHierarchyMasquerade) {
                return $this->customerSession->getMasqueradeAccountId();
            }
            $erpaccountData = $this->getErpAcctCounts();
            if ($erpaccountData && is_array($erpaccountData)) {
                return $erpaccountData[0]['erp_account_id'];
            }
            return 0;
        }

    }

    /**
     * Return ContactCode
     *
     * @return mixed
     */
    public function getEccContactCode($isAdmin = false)
    {
        if ($isAdmin || $this->isSupplier()) {
            return $this->getData('ecc_contact_code');
        }
        if ($this->getEntityId()) {
            if ($this->customerSession->getMasqueradeAccountId()) {
                $erpaccountData = $this->erpAccountFactory->create()
                    ->setData(['erp_account_id' => $this->customerSession->getMasqueradeAccountId(),
                        'customer_id' => $this->getEntityId()
                    ])
                    ->getErpAcctCounts();
                if ($erpaccountData && is_array($erpaccountData)) {
                    return $erpaccountData[0]['contact_code'];
                }
            }
            $erpaccountData = $this->getErpAcctCounts();
            if ($erpaccountData && is_array($erpaccountData)) {
                return $erpaccountData[0]['contact_code'];
            }
            return '';
        }

    }

    /**
     * IS Given Erp Id is valid
     *
     * @return boolean
     */
    public function isValidErpAccount($id)
    {
        if (!$id) {
            return false;
        }
        if ($this->getEntityId()) {
            $erpaccountData = $this->erpAccountFactory->create()
                ->setData(['erp_account_id' => $id,
                    'customer_id' => $this->getEntityId()
                ])
                ->getErpAcctCounts();
            if (!empty($erpaccountData)) {
                return true;
            }
            return false;
        }
        return false;

    }

    /**
     * get Favourite Erp Id
     *
     * @return Mixed
     */
    public function getFavErpId()
    {
        if ($this->getEntityId() && $this->_favErpids == false) {
            $erpaccountData = $this->erpAccountFactory->create()
                ->setData(['is_favourite' => 1,
                    'customer_id' => $this->getEntityId()
                ])
                ->getErpAcctCounts();
            if (!empty($erpaccountData)) {
                $this->_favErpids = $erpaccountData[0]['erp_account_id'];
            }
        }
        return $this->_favErpids;
    }

    /**
     * Update Favourite based on Erp Id
     *
     * @return Boolean
     */
    public function updateFavourite($erpId)
    {
        if ($this->getEntityId()) {
            $erpaccountData = $this->erpAccountFactory->create()
                ->setData(['erp_account_id' => $erpId,
                    'customer_id' => $this->getEntityId()
                ])
                ->updateFavourite();
            return true;
        }
        return false;

    }

    /**
     * Unselect Favourite based on Erp Id
     *
     * @return Boolean
     */
    public function unselectFavourite()
    {
        if ($this->getEntityId()) {
            $erpaccountData = $this->erpAccountFactory->create()
                ->setData(['customer_id' => $this->getEntityId()
                ])
                ->unselectFavourite();
            return true;
        }
        return false;
    }

    /**
     * Retrieve customer address array
     *
     * @param string $type Address type delivery|invoice|registered.
     * @param boolean $isCheckout Is call from checkout.
     * @param boolean $countOnly Get count of address model only.
     *
     * @return array
     */
    public function getCustomAddresses($type = null, bool $isCheckout = false, bool $countOnly = false)
    {
        $helper = $this->commCustomerAddressHelper;

        /*
         * Comm Address Helper
         *
         * @var Address $helper
         */

        $b2bHierarchyMasquerade = $this->customerSession->getB2BHierarchyMasquerade();
        if (($this->isSalesRep() && $helper->isMasquerading()) || ($b2bHierarchyMasquerade && $helper->isMasquerading())) {
            $addresses = [];
            $erpAccount = $this->commCustomerAddressHelper->getErpAccountInfo();
            if ($erpAccount) {
                if ($isCheckout && $this->scopeConfig->isSetFlag('Epicor_Comm/address/force_type',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                    $type = 'delivery';
                    $erpAddressesDelivery = $erpAccount->getAddressesByType($type);
                    if ($countOnly && $type == 'delivery') {
                        return $erpAddressesDelivery;
                    }
                    $addresses = $this->getLoadLimitedAddresses($erpAccount, $erpAddressesDelivery, $isCheckout, $addresses);
                    $type = 'invoice';
                    $erpAddressesInvoice = $erpAccount->getAddressesByType($type);
                    if ($countOnly && $type == 'invoice') {
                        return $erpAddressesInvoice;
                    }
                    $addresses = $this->getLoadLimitedAddresses($erpAccount, $erpAddressesInvoice, $isCheckout, $addresses);
                } else {
                    $erpAddresses = $erpAccount->getAddresses($type);
                    if ($countOnly) {
                        return $erpAddresses;
                    }
                    $addresses = $this->getLoadLimitedAddresses($erpAccount, $erpAddresses, $isCheckout, $addresses);
                }
            }//end if

            return $addresses;
        } else {
            if ($isCheckout) {
                $quote = $this->checkoutSession->getQuoteOnly();
                $shippingAddressId = $quote->getShippingAddress()->getCustomerAddressId();
                $isSameAsShippingaddress = false;
                $count = 0;
                $addresses = $this->getAddresses();
                foreach ($addresses as $address) {
                    if ($isCheckout && $this->getAddressLimit() && ($count > $this->getAddressLimit())) {
                        break;
                    }
                    $addresses[$address->getId()] = $address;
                    $addressModel = $address->getDataModel();
                    $addresses['model'][$address->getId()] = $addressModel;
                    if ($shippingAddressId != $address->getId()) {
                        $isSameAsShippingaddress = true;
                    }
                    $count++;
                }
                if ($isSameAsShippingaddress) {
                    $addressshiiping = $this->getAddressesCollection()->getItemById($shippingAddressId);
                    $addresses[$addressshiiping->getId()] = $addressshiiping;
                    $addressModel = $addressshiiping->getDataModel();
                    $addresses['model'][$addressshiiping->getId()] = $addressModel;
                }
                return $addresses;
            }
            return $this->getAddresses();
        }//end if

    }//end getCustomAddresses()


    /***
     * Return addresses
     *
     * @return array
     */
    protected function getLoadLimitedAddresses($erpAccount, $erpAddresses, $isCheckout, $addresses)
    {
        $collection = $this->addressesFactory->create();
        $count = 0;
        foreach ($erpAddresses as $erpAddress) {
            if ($isCheckout && $this->getAddressLimit() && ($count > $this->getAddressLimit())) {
                break;
            }
            /*
             * ErpAccount Address Model
             *
             * @var \Epicor\Comm\Model\Customer\Erpaccount\Address $erpAddress
             */
            $address = $erpAddress->toCustomerAddress($this, $erpAccount->getId(), $collection);
            $address->setId('erpaddress_' . $erpAddress->getId());
            $addresses[$address->getId()] = $address;
            if ($isCheckout) {
                $addressModel = $address->getDataModel();
                if ($address->getData('is_default_shipping')) {
                    $addressModel->setIsDefaultShipping(true);
                }

                if ($address->getData('is_default_billing')) {
                    $addressModel->setIsDefaultBilling(true);
                }

                $addresses['model'][$address->getId()] = $addressModel;
            }

            $count++;
        }//end foreach
        return $addresses;
    }//end getAddressLimit()


    /***
     * Return Shipping Address limit if any
     *
     * @return integer
     */
    protected function getAddressLimit()
    {
        return $this->scopeConfig->getValue(self::LIMIT_SHIPPING_ADDRESS_CONFIG);

    }//end getAddressLimit()


}
