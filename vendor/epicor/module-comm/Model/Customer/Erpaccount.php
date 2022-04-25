<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Customer group class for Erp
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 *
 * @method int getMagentoId()
 * @method string getName()
 * @method bool getAllowBackorders()
 * @method string getEmail()
 * @method string getDefaultDeliveryAddressCode()
 * @method string getDefaultInvoiceAddressCode()
 * @method string getCreatedAt()
 * @method string getUpdatedAt()
 * @method string getPreRegPassword()
 * @method string getAccountType()
 * @method string getNewStores()
 * @method string getAccountNumber()
 * @method string getShortCode()
 * @method string getCompany()
 * @method string getErpCode()
 * @method string getCustomAddressAllowed()
 * @method string getAllowMasquerade()
 * @method string getAllowMasqueradeCartClear()
 * @method string getAllowMasqueradeCartReprice()
 * @method string getWarrantyCustomer()
 * @method string getDefaultLocationCode()
 * @method string getLocationLinkType()
 *
 * @method setMagentoId(string $value)
 * @method setName(string $value)
 * @method setAllowBackorders(string $value)
 * @method setEmail(string $value)
 * @method setDefaultDeliveryAddressCode(string $value)
 * @method setDefaultInvoiceAddressCode(string $value)
 * @method setCreatedAt()
 * @method setUpdatedAt()
 * @method setAccountType(string $value)
 * @method setPreRegPassword(string $value)
 * @method setNewStores(string $new_stores)
 * @method setAccountNumber(string $account_number)
 * @method setShortCode(string $short_code)
 * @method setErpCode(string $erp_code)
 * @method setCompany(string $commpany)
 * @method setCustomAddressAllowed(boolean $allowed)
 * @method setAllowMasquerade(boolean $allowed)
 * @method setAllowMasqueradeCartClear(boolean $allowed)
 * @method setAllowMasqueradeCartReprice(boolean $allowed)
 * @method setWarrantyCustomer(boolean $allowed)
 * @method setDefaultLocationCode(string $code)
 * @method setLocationLinkType(string $code)
 *
 */
class Erpaccount extends \Epicor\Common\Model\AbstractModel
{

    protected $_eventPrefix = 'epicor_comm_customer_erpaccount';
    protected $_eventObject = 'erpaccount';
    public $customerTypes = array(
        self::CUSTOMER_TYPE_B2B, self::CUSTOMER_TYPE_B2C
    );

    const CUSTOMER_TYPE_B2B = 'B2B';
    const CUSTOMER_TYPE_B2C = 'B2C';
    const CUSTOMER_TYPE_Dealer = 'Dealer';
    const CUSTOMER_TYPE_Distributor = 'Distributor';
    const CUSTOMER_TYPE_Supplier = 'Supplier';
    public static $_All_ErpAccountsTypes_List = array(self::CUSTOMER_TYPE_B2B, self::CUSTOMER_TYPE_B2C,
        self::CUSTOMER_TYPE_Dealer, self::CUSTOMER_TYPE_Distributor, self::CUSTOMER_TYPE_Supplier);
     public static $All_ErpAcc_links = array(
         array('type'=>'B2B','link'=>'B'),
         array('type'=>'B2C','link'=>'C'),
         array('type'=>'Dealer','link'=>'R'),
         array('type'=>'Distributor','link'=>'D'),
     );
    const CUSTOMER_TYPE_SUPPLIER = 'Supplier';
    // comme delimited as PHP (before 5.6) doesnt support array constants
    const ACCOUNT_TYPES = 'B2B,B2C,Supplier,Dealer,Distributor';
    const MIN_ORDER_SOURCE_ERP = 'erp';
    const MIN_ORDER_SOURCE_HIGHER = 'higher';
    const MIN_ORDER_SOURCE_LOWER = 'lower';
    const MIN_ORDER_SOURCE_MAGENTO = 'magento';

    protected $_availableCurrencies = array();
    protected $_deletedCurrencies = array();
    protected $_availableAddresses = array();
    protected $_deletedAddresses = array();
    protected $_parents;
    protected $_newParents = array();
    protected $_delParents = array();
    protected $_children = array();
    protected $_childrenIds = array();
    protected $_loadedAllChildren = false;
    protected $_newChildren = array();
    protected $_delChildren = array();
    protected $_hasChildren = array();
    protected $_locations;
    protected $_updatedLocations = array();
    protected $_delLocations = array();
    protected $_newLocations = array();
    protected $_optimizedLocations = false;
    protected $_allowedLocations;
    protected $_allowedLocationCodes;
    protected $_lists = false;
    protected $_delLists = array();
    protected $_newLists = array();
    protected $_allowedErpTypes = array('B2B','Dealer');

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Store\CollectionFactory
     */
    protected $commResourceCustomerErpaccountStoreCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\Customer\Erpaccount\StoreFactory
     */
    protected $commCustomerErpaccountStoreFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerResourceModelCustomerCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\Erp\Customer\Group\HierarchyFactory
     */
    protected $commErpCustomerGroupHierarchyFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Erp\Customer\Group\Hierarchy\CollectionFactory
     */
    protected $commResourceErpCustomerGroupHierarchyCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\Link\CollectionFactory
     */
    protected $commResourceLocationLinkCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\Location\LinkFactory
     */
    protected $commLocationLinkFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Sku\CollectionFactory
     */
    protected $commResourceCustomerSkuCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\Customer\SkuFactory
     */
    protected $commCustomerSkuFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Currency\CollectionFactory
     */
    protected $commResourceCustomerErpaccountCurrencyCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\CollectionFactory
     */
    protected $commResourceCustomerErpaccountAddressCollectionFactory;

    /**
     * @var \Epicor\Comm\Helper\Messaging\Customer
     */
    protected $commMessagingCustomerHelper;

    /**
     * @var \Epicor\Comm\Model\Customer\Erpaccount\CurrencyFactory
     */
    protected $commCustomerErpaccountCurrencyFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Epicor\Comm\Model\Customer\ErpaccountFactory
     */
    protected $commCustomerErpaccountFactory;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\CollectionFactory
     */
    protected $commResourceLocationCollectionFactory;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory
     */
    protected $listsResourceListModelCollectionFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\Erp\AccountFactory
     */
    protected $listsListModelErpAccountFactory;

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\Erp\Account\CollectionFactory
     */
    protected $listsResourceListModelErpAccountCollectionFactory;

    /**
     * @var
     */
    protected $resourceConfig;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Epicor\Comm\Model\Import\Address
     */
    protected $_importCustomerAddress;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Store\CollectionFactory $commResourceCustomerErpaccountStoreCollectionFactory,
        \Epicor\Comm\Model\Customer\Erpaccount\StoreFactory $commCustomerErpaccountStoreFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Epicor\Comm\Model\Erp\Customer\Group\HierarchyFactory $commErpCustomerGroupHierarchyFactory,
        \Epicor\Comm\Model\ResourceModel\Erp\Customer\Group\Hierarchy\CollectionFactory $commResourceErpCustomerGroupHierarchyCollectionFactory,
        \Epicor\Comm\Model\ResourceModel\Location\Link\CollectionFactory $commResourceLocationLinkCollectionFactory,
        \Epicor\Comm\Model\Location\LinkFactory $commLocationLinkFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\Sku\CollectionFactory $commResourceCustomerSkuCollectionFactory,
        \Epicor\Comm\Model\Customer\SkuFactory $commCustomerSkuFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Currency\CollectionFactory $commResourceCustomerErpaccountCurrencyCollectionFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\CollectionFactory $commResourceCustomerErpaccountAddressCollectionFactory,
        \Epicor\Comm\Helper\Messaging\Customer $commMessagingCustomerHelper,
        \Epicor\Comm\Model\Customer\Erpaccount\CurrencyFactory $commCustomerErpaccountCurrencyFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Comm\Model\ResourceModel\Location\CollectionFactory $commResourceLocationCollectionFactory,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory $listsResourceListModelCollectionFactory,
        \Epicor\Lists\Model\ListModel\Erp\AccountFactory $listsListModelErpAccountFactory,
        \Epicor\Lists\Model\ResourceModel\ListModel\Erp\Account\CollectionFactory $listsResourceListModelErpAccountCollectionFactory,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\Model\Context $context,
        \Epicor\Comm\Model\Import\Address $importCustomerAddress,
        \Magento\Framework\Registry $registry,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->resourceConfig = $resourceConfig;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->scopeConfig = $scopeConfig;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->commResourceCustomerErpaccountStoreCollectionFactory = $commResourceCustomerErpaccountStoreCollectionFactory;
        $this->commCustomerErpaccountStoreFactory = $commCustomerErpaccountStoreFactory;
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        $this->commErpCustomerGroupHierarchyFactory = $commErpCustomerGroupHierarchyFactory;
        $this->commResourceErpCustomerGroupHierarchyCollectionFactory = $commResourceErpCustomerGroupHierarchyCollectionFactory;
        $this->commResourceLocationLinkCollectionFactory = $commResourceLocationLinkCollectionFactory;
        $this->commLocationLinkFactory = $commLocationLinkFactory;
        $this->commResourceCustomerSkuCollectionFactory = $commResourceCustomerSkuCollectionFactory;
        $this->commCustomerSkuFactory = $commCustomerSkuFactory;
        $this->registry = $registry;
        $this->commResourceCustomerErpaccountCurrencyCollectionFactory = $commResourceCustomerErpaccountCurrencyCollectionFactory;
        $this->commResourceCustomerErpaccountAddressCollectionFactory = $commResourceCustomerErpaccountAddressCollectionFactory;
        $this->commMessagingCustomerHelper = $commMessagingCustomerHelper;
        $this->commCustomerErpaccountCurrencyFactory = $commCustomerErpaccountCurrencyFactory;
        $this->storeManager = $storeManager;
        $this->commCustomerErpaccountFactory = $commCustomerErpaccountFactory;
        $this->commHelper = $commHelper;
        $this->commResourceLocationCollectionFactory = $commResourceLocationCollectionFactory;
        $this->commLocationsHelper = $commLocationsHelper;
        $this->listsResourceListModelCollectionFactory = $listsResourceListModelCollectionFactory;
        $this->listsListModelErpAccountFactory = $listsListModelErpAccountFactory;
        $this->listsResourceListModelErpAccountCollectionFactory = $listsResourceListModelErpAccountCollectionFactory;
        $this->_importCustomerAddress = $importCustomerAddress;
        $this->customerRepository = $customerRepository;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    protected function _construct()
    {
        // initialize resource model
        $this->_init('Epicor\Comm\Model\ResourceModel\Customer\Erpaccount');
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
        if ($this->getBrandRefresh() && $this->scopeConfig->isSetFlag('Epicor_Comm/brands/erpaccount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $this->_brandRefresh();
        }
    }

    /**
     * Reprocesses the brands against the erp account to ensure the correct stores are assigned to it
     */
    private function _brandRefresh()
    {
        $helper = $this->commMessagingHelper;

        // process brands for this erp account
        $brands = unserialize($this->getBrands());
        $stores = array();
        $brands = !is_null($brands) ? $brands : array();

        foreach ($brands as $brand) {
            $brandStores = $helper->getStoreFromBranding($brand['company'], $brand['site'], $brand['warehouse'], $brand['group']);
            $stores = $stores + $brandStores;
        }

        $storeCollection = $this->commResourceCustomerErpaccountStoreCollectionFactory->create();
        /* @var $storeCollection \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Store\Collection */
        $storeCollection->addFieldToFilter('erp_customer_group', $this->getId());

        foreach ($storeCollection->getItems() as $store) {
            if (!isset($stores[$store->getStore()])) {
                $store->delete();
            } else {
                unset($stores[$store->getStore()]);
            }
        }

        $this->setBrandRefresh(false);
        $this->save();

        if (!empty($stores)) {
            foreach ($stores as $store) {
                $erp_group_store = $this->commCustomerErpaccountStoreFactory->create();
                /* @var $erp_group_store \Epicor\Comm\Model\Customer\Erpaccount\Store */
                $erp_group_store->setErpCustomerGroup($this->getId());
                $erp_group_store->setStore($store->getId());
                $erp_group_store->save();
            }
        }

        // process brands for each address
        $addresses = $this->getAddresses();

        foreach ($addresses as $address) {
            $address->brandRefresh();
        }
    }

    public function afterCommitCallback()
    {
        parent::afterCommitCallback();

        if ($this->getOrigData('magento_id') != $this->getData('magento_id')) {
            //update customers to new groups.
            $customerCollection = $this->customerResourceModelCustomerCollectionFactory->create();
            /* @var $customerCollection \Magento\Customer\Model\ResourceModel\Customer\Collection */
            $customerCollection->addAttributeToFilter('ecc_erpaccount_id', $this->getId());
            foreach ($customerCollection->getItems() as $customer) {
                $customerRepo = $this->customerRepository->getById($customer->getId());
                $customerRepo->setGroupId($this->getMagentoId());
                $this->customerRepository->save($customerRepo);
            }
        }

        // update store list (if there are new stores to save)
        $newStores = $this->getNewStores();
        if (!empty($newStores) && ($this->scopeConfig->isSetFlag('Epicor_Comm/brands/erpaccount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) || $this->isObjectNew())) {

            // remove old stores no longer needed
            $keptStores = array();
            $storeCollection = $this->commResourceCustomerErpaccountStoreCollectionFactory->create();
            /* @var $storeCollection \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Store\Collection */
            $storeCollection->addFieldToFilter('erp_customer_group', $this->getId());
            foreach ($storeCollection->getItems() as $store) {
                if (!in_array($store->getStore(), $newStores)) {
                    $store->delete();
                } else {
                    $keptStores[] = $store->getStore();
                }
            }

            // add in new stores
            foreach ($newStores as $store) {
                if (!in_array($store, $keptStores)) {
                    $erp_group_store = $this->commCustomerErpaccountStoreFactory->create();
                    /* @var $erp_group_store \Epicor\Comm\Model\Customer\Erpaccount\Store */
                    $erp_group_store->setErpCustomerGroup($this->getId());
                    $erp_group_store->setStore($store);
                    $erp_group_store->save();
                }
            }
        }
    }

    public function beforeSave()
    {
        if (!$this->_optimizedLocations && (!empty($this->_updatedLocations) || !empty($this->_newLocations) || !empty($this->_delLocations))) {
            $this->_optimizeLocations();
        }

        parent::beforeSave();
    }
    public function stripNonSupportChars($string)
    {
        return preg_replace('[/]', '-', $string);
    }

    public function afterSave()
    {
        foreach ($this->_availableCurrencies as $currency_code) {
            $currency = $this->getCurrencyData($currency_code);
            if ($currency->hasDataChanges()) {
                $currency->setErpAccountId($this->getId());
                $currency->save();
            }
        }

        foreach ($this->_deletedCurrencies as $currency_code) {
            $currency = $this->getData('currency_' . $currency_code);
            if ($currency->getId()) {
                $currency->delete();
            }
        }
        $addressList = array();
        foreach ($this->_availableAddresses as $address_code) {
            $address = $this->getAddress($address_code);
            if ($address->hasDataChanges()) {
                $address->setErpCustomerGroupCode($this->getErpCode());
                if (!empty($address->getNewStores())) {
                    $address->save();
                } else {
                    $addressList[] = $address->getData();
                }

            }
        }

        if (!empty($addressList)) {
            $this->_importCustomerAddress->importCustomerAddressData($addressList, 'update');

            foreach ($this->_availableAddresses as $address_code) {
                $address = $this->getAddress($address_code);
                if ($address->hasDataChanges()) {
                    $address->setErpCustomerGroupCode($this->getErpCode());
                    $address->afterCommitCallback();
                }
            }
        }

        $addressList = [
            'erp_address' => [],
            'cus_address' => []
        ];
        foreach ($this->_deletedAddresses as $address_code) {
            $address_codestrip = $this->stripNonSupportChars($address_code);
            $address = $this->getData('address_' . $address_codestrip);
            if ($address->getId()) {
                $addressList['erp_address'][] = $address->getId();
                $addressList['cus_address'] = $address->getCustomerAddresses($addressList['cus_address']);
            }
        }
        if (!empty($addressList)) $this->_importCustomerAddress->importCustomerAddressData($addressList, 'delete');

        foreach ($this->_newParents as $type => $id) {
            $parent = $this->commErpCustomerGroupHierarchyFactory->create();
            /* @var $parent \Epicor\Comm\Model\Erp\Customer\Group\Hierarchy */

            $parent->setParentId($id);
            $parent->setChildId($this->getId());
            $parent->setType($type);
            $parent->save();
        }

        foreach ($this->_delParents as $type => $id) {
            $parents = $this->commResourceErpCustomerGroupHierarchyCollectionFactory->create();
            /* @var $parent \Epicor\Comm\Model\ResourceModel\Erp\Customer\Group\Hierarchy\Collection */
            $parents->addFieldToFilter('child_id', $this->getId());
            $parents->addFieldToFilter('parent_id', $id);
            $parents->addFieldToFilter('type', $type);
            $parent = $parents->getFirstItem();
            if (!$parent->isObjectNew()) {
                $parent->delete();
            }
        }

        foreach ($this->_newChildren as $type => $id) {
            $child = $this->commErpCustomerGroupHierarchyFactory->create();
            /* @var $child \Epicor\Comm\Model\Erp\Customer\Group\Hierarchy */

            $child->setChildId($id);
            $child->setParentId($this->getId());
            $child->setType($type);
            $child->save();
        }

        foreach ($this->_delChildren as $type => $id) {
            $children = $this->commResourceErpCustomerGroupHierarchyCollectionFactory->create();
            /* @var $child \Epicor\Comm\Model\ResourceModel\Erp\Customer\Group\Hierarchy\Collection */
            $children->addFieldToFilter('parent_id', $this->getId());
            $children->addFieldToFilter('child_id', $id);
            $children->addFieldToFilter('type', $type);
            $child = $children->getFirstItem();
            if (!$child->isObjectNew()) {
                $child->delete();
            }
        }

        foreach ($this->_updatedLocations as $locationCode => $linkType) {
            $links = $this->commResourceLocationLinkCollectionFactory->create();
            /* @var $links \Epicor\Comm\Model\ResourceModel\Location\Link\Collection */
            $links->addFieldToFilter('entity_id', $this->getId());
            $links->addFieldToFilter('entity_type', \Epicor\Comm\Model\Location\Link::ENTITY_TYPE_ERPACCOUNT);
            $links->addFieldToFilter('location_code', $locationCode);

            $link = $links->getFirstItem();
            /* @var $link \Epicor\Comm\Model\Location\Link */

            $link->setEntityId($this->getId());
            $link->setEntityType(\Epicor\Comm\Model\Location\Link::ENTITY_TYPE_ERPACCOUNT);
            $link->setLocationCode($locationCode);
            $link->setLinkType($linkType);

            $link->save();
        }

        foreach ($this->_newLocations as $locationCode => $linkType) {
            $link = $this->commLocationLinkFactory->create();
            /* @var $link \Epicor\Comm\Model\Location\Link */

            $link->setEntityId($this->getId());
            $link->setEntityType(\Epicor\Comm\Model\Location\Link::ENTITY_TYPE_ERPACCOUNT);
            $link->setLocationCode($locationCode);
            $link->setLinkType($linkType);

            $link->save();
        }

        foreach ($this->_delLocations as $locationCode) {
            $links = $this->commResourceLocationLinkCollectionFactory->create();
            /* @var $links \Epicor\Comm\Model\ResourceModel\Location\Link\Collection */
            $links->addFieldToFilter('entity_id', $this->getId());
            $links->addFieldToFilter('entity_type', \Epicor\Comm\Model\Location\Link::ENTITY_TYPE_ERPACCOUNT);
            $links->addFieldToFilter('location_code', $locationCode);

            $link = $links->getFirstItem();
            /* @var $link \Epicor\Comm\Model\Location\Link */

            if (!$link->isObjectNew()) {
                $link->delete();
            }
        }

        $this->_saveLists();

        parent::afterSave();
    }

    public function beforeDelete()
    {
        $customerCollection = $this->getCustomers();
        foreach ($customerCollection->getItems() as $customer) {
            if ($this->isTypeCustomer()) {
                $accountType ='guest';
                $customerField ='guest';
                $accountId =0;
                $customer->setEccPreviousErpaccount($this->getErpCode());
                $customer->setEccErpAccountType($accountType);
                $customer->setData($customerField, $accountId);
            } else if ($this->isTypeSupplier()) {
                $customer->setEccSupplierErpaccountId(false);
                $customer->setEccPreviousSupplierErpaccount($this->getErpCode());
            }
            $customer->save();
        }

        // if erp account to be deleted is same as config default erpaccount, delete the default too
        $this->deleteDefaultErpSettings();

        parent::beforeDelete();
    }


    /**
     * Loops through all instances of default erp account config that match this id and removes them
     */
    protected function deleteDefaultErpSettings()
    {
        // GJTODO NEEDS CONVERTING PROPERLY
        $config = $this->resourceConfig;
//        $defaultConfig = Mage::getModel('core/config_data')->addFieldToFilter('path', array('eq' => 'customer/create_account/default_erpaccount'))
//            ->addFieldToFilter('value', array('eq' => $this->getId()));
//        foreach ($defaultConfig as $value) {
//            $config->deleteConfig(
//                'customer/create_account/default_erpaccount', $value->getScope(), $value->getScopeId()
//            );
//        }
    }
    protected function _afterDelete()
    {
        $collection = $this->commResourceCustomerSkuCollectionFactory->create()
            ;

        /* @var $collection \Epicor\Comm\Model\ResourceModel\Customer\Sku\Collection */
        $collection->addFieldToFilter('customer_group_id', $this->getId());

        foreach ($collection->getItems() as $sku) {
            $customerSku = $this->commCustomerSkuFactory->create();
            /* @var $customerSku \Epicor\Comm\Model\Customer\Sku */
            $customerSku->load($sku->getId())->delete();
        }

        parent::_afterDelete();
    }

    /**
     *
     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    public function getCustomers($erpAccountIdOverride = false)
    {
        $customerCollection = $this->customerResourceModelCustomerCollectionFactory->create();
        /* @var $customerCollection \Magento\Customer\Model\ResourceModel\Customer\Collection */

        $accountId = $erpAccountIdOverride ? $erpAccountIdOverride : $this->getId();

        if ($this->isTypeCustomer()) {
            $customerCollection->addAttributeToFilter('ecc_erpaccount_id', $accountId);
            $customerCollection->addAttributeToSelect('ecc_previous_erpaccount');
        } else if ($this->isTypeSupplier()) {
            $customerCollection->addAttributeToFilter('ecc_supplier_erpaccount_id', $accountId);
            $customerCollection->addAttributeToSelect('ecc_prev_supplier_erpaccount');
        }

        $customerCollection->addAttributeToSelect('firstname');
        $customerCollection->addAttributeToSelect('middlename');
        $customerCollection->addAttributeToSelect('lastname');
        $customerCollection->addAttributeToSelect('ecc_function');
        $customerCollection->addAttributeToSelect('ecc_contact_code');
        $customerCollection->addAttributeToSelect('ecc_telephone_number');
        $customerCollection->addAttributeToSelect('ecc_fax_number');
        $customerCollection->addAttributeToSelect('ecc_erp_login_id');
        return $customerCollection;
    }

    /**
     * Load Erp Customer Currency Data
     */
    protected function _getCurrencyData()
    {
        if (empty($this->_availableCurrencies)) {
            $currencies = $this->registry->registry('ecc_erp_account_currencies_' . $this->getId());

            if (is_null($currencies)) {
                $collection = $this->commResourceCustomerErpaccountCurrencyCollectionFactory->create();
                /* @var $collection \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Currency\Collection */
                $collection->addFieldToFilter('erp_account_id', $this->getId());
                $currencies = $collection->getItems();

                $this->registry->unregister('ecc_erp_account_currencies_' . $this->getId());
                $this->registry->register('ecc_erp_account_currencies_' . $this->getId(), $currencies);
            }

            foreach ($currencies as $currency) {
                /* @var $currency \Epicor\Comm\Model\Customer\Erpaccount\Currency */
                $currency->_hasDataChanges = false;
                $this->setData('currency_' . $currency->getCurrencyCode(), $currency);
                if ($currency->getIsDefault()) {
                    $this->setDefaultCurrencyCode($currency->getCurrencyCode());
                }

                $this->_availableCurrencies[$currency->getCurrencyCode()] = $currency->getCurrencyCode();
            }
        }
    }

    /**
     * Load Erp Customer Address Data
     * @param null|string $type
     */
    protected function _getAddressData($type = null)
    {
        if (empty($this->_availableAddresses)) {
            $addresses = $this->registry->registry('ecc_erp_account_addresses_' . $this->getId());

            if (is_null($addresses)) {
                $collection = $this->commResourceCustomerErpaccountAddressCollectionFactory->create();
                /* @var $collection \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Currency\Collection */
                $collection->addFieldToFilter('erp_customer_group_code', $this->getErpCode());
                if (!is_null($type)) {
                    $collection->addFieldToFilter('is_' . $type, 1);
                }
                $addresses = $collection->getItems();

                $this->registry->unregister('ecc_erp_account_addresses_' . $this->getId());
                $this->registry->register('ecc_erp_account_addresses_' . $this->getId(), $addresses);
            }

            foreach ($addresses as $address) {
                /* @var $address \Epicor\Comm\Model\Customer\Erpaccount\Address */

            $address_codestrip = $this->stripNonSupportChars($address->getErpCode());
                $this->setData('address_' . $address_codestrip, $address);

                $this->_availableAddresses[$address->getErpCode()] = $address->getErpCode();
            }
        }
    }

    /**
     * Add Erp Address
     * @param string $addressCode
     * @param string $type
     * @param array $data
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function addAddress($addressCode, $type, $data = array())
    {
        $this->_getAddressData();
        $this->_hasDataChanges = true;
        $address_codestrip = $this->stripNonSupportChars($addressCode);
        $address = $this->getData('address_' . $address_codestrip);
        /* @var $address \Epicor\Comm\Model\Customer\Erpaccount\Address  */
        if (is_null($address)) {
            $helper = $this->commMessagingCustomerHelper;
            /* @var $helper \Epicor\Comm\Helper\Messaging\Customer */
            $address = $helper->getErpAddress($addressCode, $this->getErpCode(), $type);
            $address->setErpCode($addressCode);
            $address->setErpCustomerGroupCode($this->getErpCode());
        }

        $this->_availableAddresses[$addressCode] = $addressCode;

        if (array_key_exists($addressCode, $this->_deletedAddresses))
            unset($this->_deletedAddresses[$addressCode]);
        $address->addData($data);

        $addressCodestrip = $this->stripNonSupportChars($addressCode);
        $this->setData('address_' . $addressCodestrip, $address);
        return $this;
    }

    /**
     * Get Customer Address by Erp Code
     * @param string $addressCode
     * @return \Epicor\Comm\Model\Customer\Erpaccount\Address
     */
    public function getAddress($addressCode)
    {
        $address = false;

        $this->_getAddressData();

        if ($this->hasAddressCode($addressCode)) {
             $addressCodestrip = $this->stripNonSupportChars($addressCode);
            $address = $this->getData('address_' . $addressCodestrip);
        }

        return $address;
    }

    /**
     * Get All addresses for this account
     * @param null|string $type
     * @return array of \Epicor\Comm\Model\Customer\Erpaccount\Address
     */
    public function getAddresses($type =  null)
    {
        $this->_getAddressData($type);

        $addresses = array();
        foreach ($this->_availableAddresses as $addressCode) {
            $addressCodestrip = $this->stripNonSupportChars($addressCode);
            if($type === null){
                $addresses[$addressCode] = $this->getData('address_' . $addressCodestrip);
            }else{
                $address = $this->getData('address_' . $addressCodestrip);
                if($address->getData('is_' . $type)) {
                    $addresses[$addressCode] = $this->getData('address_' . $addressCodestrip);
                }
            }
        }

        return $addresses;
    }

    /**
     * Get  Addresses by type
     * @param null|string $type
     * @return array of \Epicor\Comm\Model\Customer\Erpaccount\Address
     */
    public function getAddressesByType($type)
    {
        $this->_getAddressData();
        $addresses = array();
        foreach ($this->_availableAddresses as $addressCode) {
            $addressCodestrip = $this->stripNonSupportChars($addressCode);
            if($type === null){
                $addresses[$addressCode] = $this->getData('address_' . $addressCodestrip);
            }else{
                $address = $this->getData('address_' . $addressCodestrip);
                if($address->getData('is_' . $type)) {
                    $addresses[$addressCode] = $this->getData('address_' . $addressCodestrip);
                }
            }
        }
        return $addresses;
    }

    public function removeAddress($addressCode)
    {
        $this->_getAddressData();

        if ($this->hasAddressCode($addressCode)) {
            $this->_deletedAddresses[$addressCode] = $addressCode;
            if (array_key_exists($addressCode, $this->_availableAddresses))
                unset($this->_availableAddresses[$addressCode]);

            $this->_hasDataChanges = true;
        }
        return $this;
    }

    /**
     * Add new Currency to Erp Account
     *
     * @param string $currencyCode
     * @return \Epicor\Comm\Model\Customer\Erpaccount|bool
     */
    public function addCurrency($currencyCode)
    {
        $newCurrency = false;
        $this->_getCurrencyData();
        if ($currencyCode && is_null($this->getData('currency_' . $currencyCode))) {
            $newCurrency = $this->commCustomerErpaccountCurrencyFactory->create();
            /* @var $newCurrency \Epicor\Comm\Model\Customer\Erpaccount\Currency */
            $newCurrency->setCurrencyCode($currencyCode);

            $this->_availableCurrencies[$currencyCode] = $currencyCode;
            $this->setData('currency_' . $currencyCode, $newCurrency);

            if (array_key_exists($currencyCode, $this->_deletedCurrencies))
                unset($this->_deletedCurrencies[$currencyCode]);
        } elseif (!is_null($this->getData('currency_' . $currencyCode)))
            $newCurrency = $this->getData('currency_' . $currencyCode);

        return $this;
    }

    public function removeCurrency($currencyCode)
    {
        $this->_getCurrencyData();
        if ($this->hasCurrencyCode($currencyCode)) {
            $this->_deletedCurrencies[$currencyCode] = $currencyCode;
            if (array_key_exists($currencyCode, $this->_availableCurrencies))
                unset($this->_availableCurrencies[$currencyCode]);

            $this->_hasDataChanges = true;
        }
        return $this;
    }

    /**
     * Get Customer Currency Data
     *
     * @param string $currencyCode
     * @return \Epicor\Comm\Model\Customer\Erpaccount\Currency
     */
    public function getCurrencyData($currencyCode = null)
    {
        $this->_getCurrencyData();

        $currency = null;
        if (is_null($currencyCode))
            $currencyCode = $this->getDefaultCurrencyCode();

        if ($this->hasCurrencyCode($currencyCode))
            $currency = $this->getData('currency_' . $currencyCode);

        if (is_null($currency))
            $currency = $this->dataObjectFactory->create();

        return $currency;
    }

    /**
     * Get All Customer Currency Data
     *
     * @return array of \Epicor\Comm\Model\Customer\Erpaccount\Currency
     */
    public function getAllCurrencyData()
    {
        $this->_getCurrencyData();

        $currencies = array();
        foreach ($this->_availableCurrencies as $currencyCode) {
            $currencies[$currencyCode] = $this->getData('currency_' . $currencyCode);
        }

        return $currencies;
    }

    /**
     * Checks if ErpAccount has values for requested currency
     *
     * @param string $currencyCode
     * @return bool
     */
    public function hasCurrencyCode($currencyCode)
    {
        $this->_getCurrencyData();
        return array_key_exists($currencyCode, $this->_availableCurrencies);
    }

    /**
     * Checks if ErpAccount has an address for the reequested addressCode
     *
     * @param string $addressCode
     * @return bool
     */
    public function hasAddressCode($addressCode)
    {
        $this->_getAddressData();
        return array_key_exists($addressCode, $this->_availableAddresses);
    }

    public function getDefaultCurrencyCode()
    {
        $this->_getCurrencyData();
        return $this->getData('currency_code');
    }

    /**
     * Get an array of valid sotres for this erp account
     * @return array
     */
    public function getValidStores()
    {
        $storeCollection = $this->commResourceCustomerErpaccountStoreCollectionFactory->create();
        /* @var $storeCollection \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Store\Collection */
        $storeCollection->addFieldToFilter('erp_customer_group', $this->getId());

        $stores = array();

        foreach ($storeCollection->getItems() as $store) {
            $stores[] = $store->getStore();
        }
        return $stores;
    }

    /**
     * Checks if ErpAccount is valid for passed store or if null passed the current store
     *
     * @param \Epicor\Comm\Model\Store $store
     * @return bool
     */
    public function isValidForStore($store = null)
    {
        if (is_null($store)) {
            $store = $this->storeManager->getStore();
        }

        $loginRestriction = $this->scopeConfig->getValue('Epicor_Comm/login/restriction', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        switch ($loginRestriction) {
            case 'store':
                $valid = $this->_checkBranding($store);
                break;

            case 'currency':
                $valid = $this->_checkCurrency($store);
                break;

            case 'full':
                $valid = $this->_checkBranding($store) && $this->_checkCurrency($store);
                break;

            default:
                $valid = true;
                break;
        }

        if ($valid) {
            $valid = $this->_checkCustomerType($store);
        }

        return $valid;
    }

    /**
     * Check Store Currency matches ErpAccount Currencies
     *
     * @param \Epicor\Comm\Model\Store $store
     * @return bool
     */
    private function _checkCustomerType($store = null)
    {
        $allowedTypes = $store->getEccAllowedCustomerTypes();

        if (empty($allowedTypes)) {
            $allowedTypes = $store->getWebsite()->getEccAllowedCustomerTypes();
        }

        $types = explode(',', $allowedTypes);

        $allowed = false;

        if (empty($types) || in_array('nobody', $types)) {
            $allowed = false;
        } else if (in_array($this->getAccountType(), $types) || in_array('all', $types)) {
            $allowed = true;
        }

        return $allowed;
    }

    /**
     * Check Store Currency matches ErpAccount Currencies
     *
     * @param \Epicor\Comm\Model\Store $store
     * @return bool
     */
    private function _checkCurrency($store = null)
    {
        if (!$this->isTypeSupplier()) {
            return $this->hasCurrencyCode($store->getBaseCurrencyCode());
        } else {
            return true;
        }
    }

    public function checkBranding($store = null)
    {
        return $this->_checkBranding($store);
    }

    public function isBrandingValidOnStore($store = null)
    {
        return $this->_checkBranding($store);
    }

    /**
     * Check Store Branding matches ErpAccount Branding
     *
     * @param \Epicor\Comm\Model\Store $store
     * @return bool
     */
    private function _checkBranding($store = null)
    {
        if (is_null($store)) {
            $store = $this->storeManager->getStore();
        }

        /* @var $store \Epicor\Comm\Model\Store */
        $valid_stores = $this->getValidStores();
        return in_array($store->getId(), $valid_stores);
    }

    /**
     * Checks if ErpAccount is default for any stores and returns them groupd by website id
     *
     * @return bool
     */
    public function getDefaultForStores()
    {
        $defaultStores = array();
        foreach ($this->storeManager->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $storeDefault = $this->scopeConfig->getValue('customer/create_account/default_erpaccount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId());
                    if ($storeDefault == $this->getId()) {
                        if (!isset($defaultStores[$website->getId()])) {
                            $defaultStores[$website->getId()] = array();
                        }
                        $defaultStores[$website->getId()][] = $store->getId();
                    }
                }
            }
        }

        return $defaultStores;
    }

    /**
     * Checks if ErpAccount is default for any stores and returns them groupd by website id
     *
     * @return bool
     */
    public function isDefaultForStore($store = null)
    {
        $default = false;
        if (is_null($store)) {
            $store = $this->storeManager->getStore()->getId();
        }

        $storeDefault = $this->scopeConfig->getValue('customer/create_account/default_erpaccount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
        if ($storeDefault == $this->getId()) {
            $default = true;
        }

        return $default;
    }

    /**
     * @param string $currencyCode
     * @return string
     */
    public function getCurrencyCode($currencyCode = null)
    {
        return $this->getCurrencyData($currencyCode)->getCurrencyCode();
    }

    /**
     * @param string $currencyCode
     * @return bool
     */
    public function getIsDefault($currencyCode = null)
    {
        return $this->getCurrencyData($currencyCode)->getIsDefault();
    }

    /**
     * @param string $currencyCode
     * @return bool
     */
    public function getOnstop($currencyCode = null)
    {
        return (bool) $this->getCurrencyData($currencyCode)->getOnstop();
    }

    /**
     * @param string $currencyCode
     * @return float
     */
    public function getBalance($currencyCode = null)
    {
        return $this->getCurrencyData($currencyCode)->getBalance();
    }

    /**
     * @param string $currencyCode
     * @return float
     */
    public function getCreditLimit($currencyCode = null)
    {
        return $this->getCurrencyData($currencyCode)->getCreditLimit();
    }

    /**
     * @param string $currencyCode
     * @return float
     */
    public function getUnallocatedCash($currencyCode = null)
    {
        return $this->getCurrencyData($currencyCode)->getUnallocatedCash();
    }

    /**
     * @param string $currencyCode
     * @return float
     */
    public function getMinOrderAmount($currencyCode = null)
    {
        return $this->getCurrencyData($currencyCode)->getMinOrderAmount();
    }

    /**
     * @param string $currencyCode
     * @return string
     */
    public function getLastPaymentDate($currencyCode = null)
    {
        return $this->getCurrencyData($currencyCode)->getLastPaymentDate();
    }

    /**
     * @param string $currencyCode
     * @return float
     */
    public function getLastPaymentValue($currencyCode = null)
    {
        return $this->getCurrencyData($currencyCode)->getLastPaymentValue();
    }

    /**
     * @param string $value
     * @param string $currencyCode
     */
    public function setCurrencyCode($value, $currencyCode = null)
    {
        $this->_hasDataChanges = true;
        $this->getCurrencyData($currencyCode)->setCurrencyCode($value);
        return $this;
    }

    /**
     * @param string $value
     * @param string $currencyCode
     */
    public function setIsDefault($value, $currencyCode = null)
    {
        $this->_hasDataChanges = true;
        $this->getCurrencyData($currencyCode)->setIsDefault($value);
        $this->setDefaultCurrencyCode($this->getCurrencyData($currencyCode)->getCurrencyCode());
        return $this;
    }

    /**
     * @param bool $value
     * @param string $currencyCode
     */
    public function setOnstop($value, $currencyCode = null)
    {
        $this->_hasDataChanges = true;
        $this->getCurrencyData($currencyCode)->setOnstop($value);
        return $this;
    }

    /**
     * @param float $value
     * @param string $currencyCode
     */
    public function setBalance($value, $currencyCode = null)
    {
        $this->_hasDataChanges = true;
        $this->getCurrencyData($currencyCode)->setBalance($value);
        return $this;
    }

    /**
     * @param float $value
     * @param string $currencyCode
     */
    public function setCreditLimit($value, $currencyCode = null)
    {
        $this->_hasDataChanges = true;
        $this->getCurrencyData($currencyCode)->setCreditLimit($value);
        return $this;
    }

    /**
     * @param float $value
     * @param string $currencyCode
     */
    public function setUnallocatedCash($value, $currencyCode = null)
    {
        $this->_hasDataChanges = true;
        $this->getCurrencyData($currencyCode)->setUnallocatedCash($value);
        return $this;
    }

    /**
     * @param float $value
     * @param string $currencyCode
     */
    public function setMinOrderAmount($value, $currencyCode = null)
    {
        $this->_hasDataChanges = true;
        $this->getCurrencyData($currencyCode)->setMinOrderAmount($value);
        return $this;
    }

    /**
     * @param string $value
     * @param string $currencyCode
     */
    public function setLastPaymentDate($value, $currencyCode = null)
    {
        $this->_hasDataChanges = true;
        $this->getCurrencyData($currencyCode)->setLastPaymentDate($value);
        return $this;
    }

    /**
     * @param float $value
     * @param string $currencyCode
     */
    public function setLastPaymentValue($value, $currencyCode = null)
    {
        $this->_hasDataChanges = true;
        $this->getCurrencyData($currencyCode)->setLastPaymentValue($value);
        return $this;
    }

    /**
     *
     * @param string $currencyCode
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function setDefaultCurrencyCode($currencyCode)
    {
        $this->setData('currency_code', $currencyCode);
        foreach ($this->_availableCurrencies as $currency_code) {


            $currency = $this->getCurrencyData($currency_code);
            if ($currency->getCurrencyCode() == $currencyCode && !$currency->getIsDefault())
                $currency->setIsDefault(true);
            elseif ($currency->getCurrencyCode() != $currencyCode && $currency->getIsDefault())
                $currency->setIsDefault(false);
        }
        return $this;
    }

    public function setType($type, $addressCode)
    {
        if ($type == 'registered') {
            $this->getAddress($addressCode)->setIsRegistered(true);
        } else if ($type == 'invoice') {
            $this->getAddress($addressCode)->setIsInvoice(true);
        } else if ($type == 'delivery') {
            $this->getAddress($addressCode)->setIsDelivery(true);
        }

        return $this;
    }

    public function unsetType($type, $addressCode)
    {
        if ($type == 'registered') {
            $this->getAddress($addressCode)->setIsRegistered(false);
        } else if ($type == 'invoice') {
            $this->getAddress($addressCode)->setIsInvoice(false);
        } else if ($type == 'delivery') {
            $this->getAddress($addressCode)->setIsDelivery(false);
        }

        return $this;
    }

    /**
     *
     * @param string $addressName
     * @param string $addressCode
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function setAddressName($addressName, $addressCode)
    {
        $this->getAddress($addressCode)->setName($addressName);
        return $this;
    }

    /**
     *
     * @param string $addressLine1
     * @param string $addressCode
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function setAddress1($addressLine1, $addressCode)
    {
        //M1 > M2 Translation Begin (Rule 9)
        //$this->getAddress($addressCode)->setAddress1($addressLine1);
        $this->getAddress($addressCode)->setData('address1', $addressLine1);
        //M1 > M2 Translation End
        return $this;
    }

    /**
     *
     * @param string $addressLine2
     * @param string $addressCode
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function setAddress2($addressLine2, $addressCode)
    {
        //M1 > M2 Translation Begin (Rule 9)
        //$this->getAddress($addressCode)->setAddress2($addressLine2);
        $this->getAddress($addressCode)->setData('address2', $addressLine2);
        //M1 > M2 Translation End
        return $this;
    }

    /**
     *
     * @param string $addressLine3
     * @param string $addressCode
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function setAddress3($addressLine3, $addressCode)
    {
        //M1 > M2 Translation Begin (Rule 9)
        //$this->getAddress($addressCode)->setAddress3($addressLine3);
        $this->getAddress($addressCode)->setData('address3', $addressLine3);
        //M1 > M2 Translation End
        return $this;
    }

    /**
     *
     * @param string $city
     * @param string $addressCode
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function setCity($city, $addressCode)
    {
        $this->getAddress($addressCode)->setCity($city);
        return $this;
    }

    /**
     *
     * @param string $county
     * @param string $addressCode
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function setCounty($county, $addressCode)
    {
        $this->getAddress($addressCode)->setCounty($county);
        return $this;
    }

    /**
     *
     * @param string $countyCode
     * @param string $addressCode
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function setCountyCode($countyCode, $addressCode)
    {
        $this->getAddress($addressCode)->setCountyCode($countyCode);
        return $this;
    }

    /**
     *
     * @param string $postCode
     * @param string $addressCode
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function setPostcode($postCode, $addressCode)
    {
        $this->getAddress($addressCode)->setPostcode($postCode);
        return $this;
    }

    /**
     *
     * @param string $country
     * @param string $addressCode
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function setCountry($country, $addressCode)
    {
        $this->getAddress($addressCode)->setCountry($country);
        return $this;
    }

    /**
     *
     * @param string $telephone
     * @param string $addressCode
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function setPhone($telephone, $addressCode)
    {
        $this->getAddress($addressCode)->setPhone($telephone);
        return $this;
    }

    /**
     *
     * @param string $telephone
     * @param string $addressCode
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function setMobileNumber($mobile, $addressCode)
    {
        $mobile = ($mobile) ? $mobile : 'N/A';
        $this->getAddress($addressCode)->setMobileNumber($mobile);
        return $this;
    }

    /**
     *
     * @param string $fax
     * @param string $addressCode
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function setFax($fax, $addressCode)
    {
        $this->getAddress($addressCode)->setFax($fax);
        return $this;
    }

    /**
     *
     * @param string $instructions
     * @param string $addressCode
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function setInstructions($instructions, $addressCode)
    {
        $this->getAddress($addressCode)->setInstructions($instructions);
        return $this;
    }

    /**
     *
     * @param array $stores
     * @param string $addressCode
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function setAddressStores($stores, $addressCode)
    {
        $this->getAddress($addressCode)->setNewStores($stores);
        return $this;
    }

    /**
     *
     * @param array $brands
     * @param string $addressCode
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function setAddressBrands($brands, $addressCode)
    {
        $this->getAddress($addressCode)->setBrands(serialize($brands));
        return $this;
    }

    /**
     *
     * @param string $locationCode
     * @param string $addressCode
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function setAddressLocationCode($locationCode, $addressCode)
    {
        return $this->getAddress($addressCode)->setDefaultLocationCode($locationCode);
    }

    /**
     *
     * @param string $addressCode
     * @return string
     */
    public function getAddressName($addressCode)
    {
        return $this->getAddress($addressCode)->getName();
    }

    /**
     *
     * @param string $addressCode
     * @return string
     */
    public function getAddress1($addressCode)
    {
        //M1 > M2 Translation Begin (Rule 9)
        //return $this->getAddress($addressCode)->getAddress1();
        return $this->getAddress($addressCode)->getData('address1');
        //M1 > M2 Translation End
    }

    /**
     *
     * @param string $addressCode
     * @return string
     */
    public function getAddress2($addressCode)
    {
        //M1 > M2 Translation Begin (Rule 9)
        //return $this->getAddress($addressCode)->getAddress2();
        return $this->getAddress($addressCode)->getData('address2');
        //M1 > M2 Translation End
    }

    /**
     *
     * @param string $addressCode
     * @return string
     */
    public function getAddress3($addressCode)
    {
        //M1 > M2 Translation Begin (Rule 9)
        //return $this->getAddress($addressCode)->getAddress3();
        return $this->getAddress($addressCode)->getData('address3');
        //M1 > M2 Translation End
    }

    /**
     *
     * @param string $addressCode
     * @return string
     */
    public function getCity($addressCode)
    {
        return $this->getAddress($addressCode)->getCity();
    }

    /**
     *
     * @param string $addressCode
     * @return string
     */
    public function getCounty($addressCode)
    {
        return $this->getAddress($addressCode)->getCounty();
    }

    /**
     *
     * @param string $addressCode
     * @return string
     */
    public function getPostcode($addressCode)
    {
        return $this->getAddress($addressCode)->getPostcode();
    }

    /**
     *
     * @param string $addressCode
     * @return string
     */
    public function getCountry($addressCode)
    {
        return $this->getAddress($addressCode)->getCountry();
    }

    /**
     *
     * @param string $addressCode
     * @return string
     */
    public function getPhone($addressCode)
    {
        return $this->getAddress($addressCode)->getPhone();
    }

    /**
     *
     * @param string $addressCode
     * @return string
     */
    public function getFax($addressCode)
    {
        return $this->getAddress($addressCode)->getFax();
    }

    /**
     *
     * @param string $addressCode
     * @return string
     */
    public function getInstructions($addressCode)
    {
        return $this->getAddress($addressCode)->getInstructions();
    }

    /**
     *
     * @param string $addressCode
     * @return string
     */
    public function getAddressLocationCode($addressCode)
    {
        return $this->getAddress($addressCode)->getDefaultLocationCode();
    }

    /**
     * Checks whether the Account Type matches the given value
     *
     * @param string $accountType
     *
     * @return bolean
     */
    public function isType($accountType)
    {
        return $this->getAccountType() == $accountType;
    }

    /**
     * Checks whether the account type is B2B
     *
     * @return boolean
     */
    public function isTypeB2b()
    {
        return $this->isType('B2B');
    }

    /**
     * Checks whether the account type is B2B/Dealer
     *
     * @return boolean
     */
    public function checkCustomertype()
    {
        $allowedTypes = $this->_allowedErpTypes;
        $allowed=false;
        if(in_array($this->getAccountType(), $allowedTypes)) {
            $allowed = true;
        }
        return $allowed;
    }

    /**
     * Checks whether the account type is B2C
     *
     * @return boolean
     */
    public function isTypeB2c()
    {
        return $this->isType('B2C');
    }

    /**
     * Checks whether the account type is B2C
     *
     * @return boolean
     */
    public function isTypeCustomer()
    {
        return $this->isType('B2C') || $this->isType('B2B');
    }

    /**
     * Checks whether the account type is B2C
     *
     * @return boolean
     */
    public function isTypeSupplier()
    {
        return $this->isType('Supplier');
    }

    public function getParents()
    {
        if (is_null($this->_parents)) {
            $this->_parents = array();
            $parents = $this->commResourceErpCustomerGroupHierarchyCollectionFactory->create();
            /* @var $parents \Epicor\Comm\Model\ResourceModel\Erp\Customer\Group\Hierarchy\Collection */

            $parents->addFieldToFilter('child_id', $this->getId());

            foreach ($parents->getItems() as $parent) {
                /* @var $parent \Epicor\Comm\Model\Erp\Customer\Group\Hierarchy */
                $account = $this->commCustomerErpaccountFactory->create()->load($parent->getParentId());
                $account->setParentType($parent->getType());
                $this->_parents[$parent->getType()] = $account;
            }
        }

        return $this->_parents;
    }

    public function getParent($type)
    {
        $parents = $this->getParents();

        return isset($parents[$type]) ? $parents[$type] : false;
    }

    public function addParent($id, $type)
    {
        $this->_newParents[$type] = $id;

        $existing = $this->getParent($type);

        if ($existing && $existing->getId() != $id) {
            $this->removeParent($existing->getId(), $type);
        }
    }

    public function removeParent($id, $type)
    {
        $this->_delParents[$type] = $id;
    }

    public function removeParentByType($type)
    {
        $parent = $this->getParent($type);

        if ($parent) {
            $this->removeParent($parent->getId(), $type);
        }
    }

    /**
     * get Brand data unserilized
     *
     * @return array
     */
    public function getUnserializedBrands()
    {
        $user_brands = $this->getBrands() ? unserialize($this->getBrands()) : array();

        if (array_key_exists('company', $user_brands) || array_key_exists('site', $user_brands) || array_key_exists('warehouse', $user_brands) || array_key_exists('group', $user_brands)) {
            // only one entry
            $userBrand[] = $user_brands;
        } else {
            //multiple entries
            $userBrand = $user_brands;
        }
        return $userBrand;
    }

    public function hasChildAccounts($type = '')
    {
        if ($type != '' && !array_key_exists($type, $this->_hasChildren)) {
            $this->_hasChildren[$type] = (bool) $this->_getChildAccountLinks($type)->count();
        } elseif ($type == '') {
            return (bool) $this->_getChildAccountLinks()->count();
        }

        return $this->_hasChildren[$type];
    }

    /**
     * Get Child Account Linkage Ids
     *
     * @param string $type
     * @return \Epicor\Comm\Model\ResourceModel\Erp\Customer\Group\Hierarchy\Collection
     */
    protected function _getChildAccountLinks($type = '')
    {
        $children = $this->commResourceErpCustomerGroupHierarchyCollectionFactory->create();
        /* @var $children \Epicor\Comm\Model\ResourceModel\Erp\Customer\Group\Hierarchy\Collection */

        $children->addFieldToFilter('parent_id', $this->getId());

        if (!empty($type)) {
            $children->addFieldToFilter('type', $type);
        }

        return $children;
    }

    public function getAllChildAccountIds()
    {
        return $this->getChildAccounts('', true, false, true);
    }

    public function getChildAccounts($type = '', $unique = false, $grouped = false, $ids = false)
    {
        if ($type == '' && !$this->_loadedAllChildren || !array_key_exists($type, $this->_children)) {
            if ($type == '') {
                $this->_children = array();
                $this->_childrenIds = array();
            } else {
                $this->_children[$type] = array();
                $this->_childrenIds[$type] = array();
            }
            $children = $this->_getChildAccountLinks($type);

            $accountIds = array();

            foreach ($children->getItems() as $child) {
                /* @var $child \Epicor\Comm\Model\Erp\Customer\Group\Hierarchy */
                $account = $this->commCustomerErpaccountFactory->create()->load($child->getChildId());
                /* @var $account \Epicor\Comm\Model\Customer\Erpaccount */
                $account->setChildType($child->getType());
                $data = array(
                    'type' => $child->getType(),
                    'id' => $account->getId(),
                );
                $account->setChildTypeData(base64_encode(serialize($data)));
                $uniqueKey = $account->getChildType() . '-' . $account->getId();
                if (!$unique || !in_array($uniqueKey, $accountIds)) {
                    $this->_childrenIds[$child->getType()][] = $account->getId();
                    $this->_children[$child->getType()][] = $account;
                }
                $accountIds[] = $uniqueKey;
            }
            if ($type == '') {
                $this->_loadedAllChildren = true;
            }
        }

        if ($type == '') {
            if ($grouped) {
                return ($ids) ? $this->_childrenIds : $this->_children;
            } else {
                $mergedchildren = array();

                $childrenItems = ($ids) ? $this->_childrenIds : $this->_children;

                foreach ($childrenItems as $type => $children) {
                    $mergedchildren = array_merge($children, $mergedchildren);
                }
                return $mergedchildren;
            }
        } else {
            return ($ids) ? $this->_childrenIds[$type] : $this->_children[$type];
        }
    }

    public function addChild($id, $type, $validate = false)
    {
        if (!$validate) {
            $this->_newChildren[$type] = $id;
        } else {
            $childAccount = $this->commCustomerErpaccountFactory->create()->load($id);
            /* @var $childAccount \Epicor\Comm\Model\Customer\Erpaccount */

            $parents = $childAccount->getParents();

            if (!$parents || !isset($parents[$type])) {
                $this->_newChildren[$type] = $id;
            } else {
                $helper = $this->commHelper;
                /* @var $helper \Epicor\Comm\Helper\Data */

                $types = \Epicor\Comm\Model\Erp\Customer\Group\Hierarchy::$linkTypes;

                //M1 > M2 Translation Begin (Rule 55)
                //throw new \Exception($helper->\\('Cannot Add Child: %s already has a Parent of type %s', $childAccount->getAccountNumber(), $types[$type]));
                throw new \Exception($helper->__('Cannot Add Child: %1 already has a Parent of type %2', $childAccount->getAccountNumber(), $types[$type]));
                //M1 > M2 Translation End
            }
        }
    }

    public function removeChild($id, $type)
    {
        $this->_delChildren[$type] = $id;
    }

    /**
     * Returns whether this customer can masquerade as a child account
     *
     * @return boolean
     */
    public function isMasqueradeAllowed()
    {
        return $this->checkConfig('epicor_comm_erp_accounts/masquerade/allow', 'allow_masquerade');
    }

    /**
     * Returns allowed contract type
     *
     * @return string
     */
    public function checkAllowedContractType()
    {
        return $this->checkConfig('epicor_lists/contracts/allowedcontract', 'allowed_contract_type');
    }

    /**
     * Returns required contract type
     *
     * @return string
     */
    public function checkRequiredContractType()
    {
        return $this->checkConfig('epicor_lists/contracts/requiredcontract', 'required_contract_type');
    }

    /**
     * Returns allowed non contract items
     *
     * @return string
     */
    public function checkAllowNonContractItems()
    {
        return $this->checkConfig('epicor_lists/contracts/allowcontractitem', 'allow_non_contract_items');
    }

    /**
     * Returns whether this customer can clear cart on masquerade
     *
     * @return boolean
     */
    public function isMasqueradeCartClearAllowed()
    {
        return $this->checkConfig('epicor_comm_erp_accounts/masquerade/allow_cart_clear', 'allow_masquerade_cart_clear');
    }

    /**
     * Returns whether this customer can save cart on masquerade
     *
     * @return boolean
     */
    public function isMasqueradeCartSaveAllowed()
    {
        return $this->checkConfig('epicor_comm_erp_accounts/masquerade/allow_cart_save', 'allow_masquerade_cart_save');
    }

    /**
     * Returns whether this customer can reprice cart on masquerade
     *
     * @return boolean
     */
    public function isMasqueradeCartRepriceAllowed()
    {
        return $this->checkConfig('epicor_comm_erp_accounts/masquerade/allow_cart_reprice', 'allow_masquerade_cart_reprice');
    }

    /**
     * Returns whether this customer can create new addresses
     *
     * @return boolean
     */
    public function checkConfig($globalPath, $dataPath)
    {
        $allowed = false;

        $globalAllow = $this->scopeConfig->getValue($globalPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $accountAllow = $this->getData($dataPath);
        if ($globalAllow == 'forceyes') {
            $allowed = true;
        } else if ($globalAllow == 'forceno') {
            $allowed = false;
        } else if ($accountAllow == null) {
            $allowed = $globalAllow == 1 ? true : false;
            if ($globalAllow != 1 || $globalAllow != 0) {
                $allowed = $globalAllow;
            }
        } else if ($accountAllow == 1) {
            $allowed = true;
        } else if ($accountAllow != 1) {
            $allowed = $accountAllow;
        }

        return $allowed;
    }

    /**
     * Loads locations for this ERP Account
     *
     * @return array
     */
    protected function _loadLocationLinks()
    {
        if (is_null($this->_locations) && !$this->isObjectNew()) {
            $this->_locations = array();
            $links = $this->commResourceLocationLinkCollectionFactory->create();
            /* @var $links \Epicor\Comm\Model\ResourceModel\Location\Link\Collection */
            $links->addFieldToFilter('entity_id', $this->getId());
            $links->addFieldToFilter('entity_type', \Epicor\Comm\Model\Location\Link::ENTITY_TYPE_ERPACCOUNT);

            foreach ($links as $link) {
                /* @var $link \Epicor\Comm\Model\Location\Link */
                $this->_locations[$link->getLocationCode()] = $link->getLinkType();
            }
        }

        return $this->_locations;
    }

    public function getAllowedLocationCodes()
    {
        return $this->getAllowedLocations(true);
    }

    public function getAllowedLocations($codes = false)
    {
        if (is_null($this->_allowedLocations)) {
            $this->_allowedLocations = array();
            $this->_allowedLocationCodes = array();
            $filter = false;
            if ($this->getLocationLinkType()) {
                $erpLocations = $this->_loadLocationLinks();
                $filter = true;
                $condition = ($this->getLocationLinkType() == \Epicor\Comm\Model\Location\Link::LINK_TYPE_INCLUDE) ? 'in' : 'nin';
            }
            $collection = $this->commResourceLocationCollectionFactory->create();
            /* @var $collection \Epicor\Comm\Model\ResourceModel\Location\Collection */

            if ($filter && !empty($erpLocations)) {
                $collection->addFieldToFilter('code', array($condition => array_map('strval', array_keys($erpLocations))));
            }
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

    /**
     * Adds a location code to the ERP account
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
            }
        } else {
            $this->_newLocations[$locationCode] = $linkType;
        }

        if (isset($this->_delLocations[$locationCode])) {
            unset($this->_delLocations[$locationCode]);
        }
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
        }
    }

    /**
     * Updates an ERP Account locations based on the array provided
     *
     * @param array $locations
     * @param string $linkType
     * @param boolean $fromErp
     */
    public function updateLocations($locations, $linkType = \Epicor\Comm\Model\Location\Link::LINK_TYPE_INCLUDE)
    {
        $currentLinks = $this->_loadLocationLinks();

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

    private function _optimizeLocations()
    {
        $currentLinks = $this->_loadLocationLinks();

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

        $locations = $this->commResourceLocationCollectionFactory->create();

        $all = array();
        foreach ($locations->getData() as $location) {
            if(isset($location['code'])){
            $all[] = $location['code'];
            }
        }
        if ($this->getLocationLinkType() == \Epicor\Comm\Model\Location\Link::LINK_TYPE_INCLUDE) {
            $erpAllowed = array_keys($currentLinks);
        } else {
            if (!empty($currentLinks)) {
                $collection = $this->commResourceLocationCollectionFactory->create();
                /* @var $collection \Epicor\Comm\Model\ResourceModel\Location\Collection */

                $collection->addFieldToFilter('code', array('nin' => array_keys($currentLinks)));
                $erpAllowed = array();
                foreach ($collection->getItems() as $location) {
                    $erpAllowed[] = $location->getCode();
                }
            } else {
                $erpAllowed = $all;
            }
        }

        $diff = array_diff($all, $erpAllowed);
        if (!empty($diff)) {
            $helper = $this->commLocationsHelper;
            /* @var $helper \Epicor\Comm\Helper\Locations */
            $optimized = $helper->optimizeLocations(array_keys($currentLinks), $diff, $this->getLocationLinkType());
            $this->_newLocations = array();
            $this->_updatedLocations = array();
            $this->_delLocations = array();
            $this->updateLocations($optimized['locations'], $optimized['link_type']);
        } else {
            $this->_newLocations = array();
            $this->_updatedLocations = array();
            $this->_delLocations = array();
            $this->setLocationLinkType(\Epicor\Comm\Model\Location\Link::LINK_TYPE_EXCLUDE);
            foreach ($currentLinks as $locationCode => $type) {
                $this->deleteLocationLink($locationCode);
            }
        }

        $this->_optimizedLocations = true;
    }

    public function getDefaultLocationForCustomer()
    {
        return $this->checkConfig('epicor_comm_erp_accounts/masquerade/allow_cart_reprice', 'ecc_default_location_code');
        return $this->checkConfig('epicor_comm_erp_accountsounts/masquerade/allow_cart_reprice', 'ecc_default_location_code');
    }

    /**
     * Retrives Lists from the ErpAccount
     *
     * @return array
     */
    public function getLists()
    {
        if (!$this->_lists) {
            $collection = $this->listsResourceListModelCollectionFactory->create();
            /* @var $collection \Epicor\Lists\Model\ResourceModel\List\Collection */
            $collection->filterByErpAccount($this->getId());

            $lists = array();
            foreach ($collection->getItems() as $item) {
                /* @var $item \Epicor\Lists\Model\ListModel */
                $lists[$item->getId()] = $item;
            }

            $this->_lists = $lists;
        }

        return $this->_lists;
    }

    /**
     * Add Lists to the Erp Account
     *
     * @param array|int|object $lists
     */
    public function addLists($lists)
    {
        if (!is_array($lists)) {
            $lists = array($lists);
        }

        foreach ($lists as $list) {
            $listId = (is_object($list) ? $list->getId() : $list);
            $this->_newLists[$listId] = $list;
            if (isset($this->_delLists[$listId])) {
                unset($this->_delLists[$listId]);
            }
        }

        $this->_hasDataChanges = true;
    }

    /**
     * Removes Lists from the Erp Account
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

    /**
     * Save Lists from the Erp Account
     *
     * @param array|int|object $lists
     * Allowed B2C -> B2C,No-specific link(But change List ERP Account link type to Chosen ERP),Chosen ERP Account
     * Allowed B2B -> B2B,No-specific link(But change List ERP Account link type to Chosen ERP),Chosen ERP Account
     */
    protected function _saveLists()
    {
        $existingLists = $this->getLists();

        $listIds = array();
        foreach ($this->_newLists as $listId => $list) {
            if (!array_key_exists($listId, $existingLists)) {
                $listIds[] = $listId;
            }
        }

        if (count($listIds) > 0) {
            $newLists = $listsCollection = $this->listsResourceListModelCollectionFactory->create();
            /* @var $listsCollection \Epicor\Lists\Model\ResourceModel\ListModel\Collection */
            $newLists->addFieldtoFilter('id', array('in' => $listIds));

            foreach ($newLists as $newList) {
                $list = $this->listsListModelErpAccountFactory->create();
                $list = $this->listsListModelErpAccountFactory->create();
                /* @var $list Epicor_Lists_Model_ListModel_Erp_Account */
                /* @var $list \Epicor\Lists\Model\ListModel\Erp\Account */
                $list->setErpAccountId($this->getId());
                $list->setListId($newList->getId());
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
            $listsCollection = $this->listsResourceListModelErpAccountCollectionFactory->create();
            /* @var $listsCollection \Epicor\Lists\Model\ResourceModel\ListModel\Erp\Account\Collection */
            $listsCollection->addFieldtoFilter('list_id', array('in' => $listIds));
            $listsCollection->addFieldtoFilter('erp_account_id', $this->getId());
            foreach ($listsCollection->getItems() as $item) {
                $item->delete();
            }
        }
        $this->_delLists = array();
    }

    /**
     * Gets Contract ship to settings for this ERP Account
     *
     * @return array
     */
    public function getContractShipToSettings()
    {
        return array(
            'shipto_default' => $this->checkConfig('epicor_lists/contracts/shiptoselection', 'contract_shipto_default'),
            'shipto_date' => $this->checkConfig('epicor_lists/contracts/shiptodate', 'contract_shipto_date'),
            'shipto_prompt' => $this->checkConfig('epicor_lists/contracts/shiptoprompt', 'contract_shipto_prompt')
        );
    }

    /**
     * Gets Contract header settings for this ERP Account
     *
     * @return array
     */
    public function getContractHeaderSettings()
    {
        return array(
            'header_selection' => $this->checkConfig('epicor_lists/contracts/headerselection', 'contract_header_selection'),
            'header_prompt' => $this->checkConfig('epicor_lists/contracts/headerprompt', 'contract_header_prompt'),
            'header_always' => $this->checkConfig('epicor_lists/contracts/headeralways', 'contract_header_always')
        );
    }

    /**
     * Gets Contract line settings for this ERP Account
     *
     * @return array
     */
    public function getContractLineSettings()
    {
        return array(
            'line_selection' => $this->checkConfig('epicor_lists/contracts/lineselection', 'contract_line_selection'),
            'line_prompt' => $this->checkConfig('epicor_lists/contracts/lineprompt', 'contract_line_prompt'),
            'line_always' => $this->checkConfig('epicor_lists/contracts/linealways', 'contract_line_always')
        );
    }

    /**
     * @param $addressId
     * @return \Epicor\Comm\Model\Customer\Erpaccount\Address
     */
    public function getAddressById($addressId)
    {
        $address = $this->commResourceCustomerErpaccountAddressCollectionFactory->create()->getItemById($addressId);
        return $address;
    }

    /**
     * @param string|null $type
     * @return array
     */
    public function getAddressIds($type = null)
    {
        $collection = $this->commResourceCustomerErpaccountAddressCollectionFactory->create();
        /* @var $collection \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Currency\Collection */
        $collection->addFieldToFilter('erp_customer_group_code', $this->getErpCode());
        if (!is_null($type)) {
            $collection->addFieldToFilter('is_' . $type, 1);
        }
        $addresses = $collection->getColumnValues('entity_id');
        $addresses = array_map(
            function ($addressId) {
                return 'erpaddress_' . $addressId;
            }, $addresses
        );
        return $addresses;
    }
}
