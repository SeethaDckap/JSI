<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Customer\Erpaccount;

use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Customer group address class for Erp
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 *
 * @method set
 *
 * @method setType(string $type);
 * @method setErpCode(string $erpCode);
 * @method setErpCustomerGroupCode(string $erpCustomerGroupCode);
 * @method setName(string $addressName);
 * @method setAddress1(string $addressLine1);
 * @method setAddress2(string $addressLine2);
 * @method setAddress3(string $addressLine3);
 * @method setCity(string $city);
 * @method setCounty(string $county);
 * @method setPostcode(string $postCode);
 * @method setCountry(string $country);
 * @method setPhone(string $telephone);
 * @method setFax(string $fax);
 * @method setInstructions(string $instructions);
 * @method setNewStores(array $stores);
 * @method setDefaultLocationCode(string $locationCode);
 *
 * @method string getErpCode();
 * @method string getErpCustomerGroupCode();
 * @method string getName();
 * @method string getAddress1();
 * @method string getAddress2();
 * @method string getAddress3();
 * @method string getCity();
 * @method string getCounty();
 * @method string getPostcode();
 * @method string getCountry();
 * @method string getPhone();
 * @method string getFax();
 * @method string getInstructions();
 * @method array getNewStores()
 * @method string getDefaultLocationCode()
 */
class Address extends \Epicor\Common\Model\AbstractModel
{

    protected $_eventPrefix = 'epicor_comm_customer_erpaddress';
    protected $_eventObject = 'erpaddress';

    const ADDRESS_REGISTERED = 'registered';
    const ADDRESS_BILLING = 'invoice';
    const ADDRESS_SHIPPING = 'delivery';
    const ERP_ADDRESS_REGISTERED = 'R';
    const ERP_ADDRESS_BILLING = 'I';
    const ERP_ADDRESS_SHIPPING = 'D';
    const ADDRESS_DEFAULT = self::ADDRESS_SHIPPING;

    protected $_typeErp2Magento = array(
        self::ERP_ADDRESS_REGISTERED => self::ADDRESS_REGISTERED,
        self::ERP_ADDRESS_BILLING => self::ADDRESS_BILLING,
        self::ERP_ADDRESS_SHIPPING => self::ADDRESS_SHIPPING
    );

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\Store\CollectionFactory
     */
    protected $commResourceCustomerErpaccountAddressStoreCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\Customer\Erpaccount\Address\StoreFactory
     */
    protected $commCustomerErpaccountAddressStoreFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Address\CollectionFactory
     */
    protected $customerResourceModelAddressCollectionFactory;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $directoryRegionFactory;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $customerAddressFactory;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $directoryCountryFactory;

    /**
     * @var \Magento\Customer\Helper\Address
     */
    protected $customerAddressHelper;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;
    /**
     * @var \Epicor\Comm\Model\Import\Address
     */
    protected $_importCustomerAddress;

    /**
     * @var RegionInterfaceFactory
     */
    protected $regionDataFactory;

    /** Configuration.
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Customer messaging helper factory.
     *
     * @var \Epicor\Comm\Helper\Messaging\CustomerFactory
     */
    protected $commMessagingCustomerHelper;

    /**
     * Address constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Customer\Helper\Address $customerAddressHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Epicor\Comm\Helper\Data $commHelper
     * @param \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\Store\CollectionFactory $commResourceCustomerErpaccountAddressStoreCollectionFactory
     * @param Address\StoreFactory $commCustomerErpaccountAddressStoreFactory
     * @param \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $customerResourceModelAddressCollectionFactory
     * @param \Epicor\Comm\Helper\Messaging $commMessagingHelper
     * @param \Magento\Directory\Model\RegionFactory $directoryRegionFactory
     * @param \Magento\Customer\Model\AddressFactory $customerAddressFactory
     * @param \Magento\Directory\Model\CountryFactory $directoryCountryFactory
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param RegionInterfaceFactory $regionDataFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param \Epicor\Comm\Model\Import\Address $importCustomerAddress
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Customer\Helper\Address $customerAddressHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\Store\CollectionFactory $commResourceCustomerErpaccountAddressStoreCollectionFactory,
        \Epicor\Comm\Model\Customer\Erpaccount\Address\StoreFactory $commCustomerErpaccountAddressStoreFactory,
        \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $customerResourceModelAddressCollectionFactory,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Directory\Model\RegionFactory $directoryRegionFactory,
        \Magento\Customer\Model\AddressFactory $customerAddressFactory,
        \Magento\Directory\Model\CountryFactory $directoryCountryFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        RegionInterfaceFactory $regionDataFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Epicor\Comm\Model\Import\Address $importCustomerAddress,
        ScopeConfigInterface $scopeConfig,
        \Epicor\Comm\Helper\Messaging\CustomerFactory $commMessagingCustomerHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->commHelper = $commHelper;
        $this->commResourceCustomerErpaccountAddressStoreCollectionFactory = $commResourceCustomerErpaccountAddressStoreCollectionFactory;
        $this->commCustomerErpaccountAddressStoreFactory = $commCustomerErpaccountAddressStoreFactory;
        $this->customerResourceModelAddressCollectionFactory = $customerResourceModelAddressCollectionFactory;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->directoryRegionFactory = $directoryRegionFactory->create();
        $this->customerAddressFactory = $customerAddressFactory;
        $this->directoryCountryFactory = $directoryCountryFactory;
        $this->customerAddressHelper = $customerAddressHelper;
        $this->addressRepository = $addressRepository;
        $this->regionDataFactory = $regionDataFactory;
        $this->_importCustomerAddress = $importCustomerAddress;
        $this->scopeConfig = $scopeConfig;
        $this->commMessagingCustomerHelper = $commMessagingCustomerHelper;
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
        $this->_init('Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address');
    }

    public function afterCommitCallback()
    {
        parent::afterCommitCallback();
        if (!$this->registry->registry('updating_erp_address')) {
            $this->registry->register('updating_erp_address', true);

            $erp_group = $this->commHelper->getErpAccountByAccountNumber($this->getErpCustomerGroupCode());
            /* @var $erp_group \Epicor\Comm\Model\Customer\Erpaccount */
            $collection = $erp_group->getCustomers();
            $customers = $collection->getItems();
            $addressList = array();
            foreach ($customers as $customer) {
                $address = $this->toCustomerAddress($customer);
                $addressList[] = $address->getData();
            }
            if (!empty($addressList)) {
                $this->_importCustomerAddress->importCustomerAddressData($addressList, 'update');
            }
            $this->registry->unregister('updating_erp_address');
        }

        // update store list (if there are new stores to save)
        $newStores = $this->getNewStores();
        if (!empty($newStores)) {

            // remove old stores no longer needed

            $storeCollection = $this->commResourceCustomerErpaccountAddressStoreCollectionFactory->create();
            /* @var $storeCollection \Magento\Customer\Model\ResourceModel\Customer\Collection */
            $storeCollection->addFieldToFilter('erp_customer_group_address', $this->getId());

            $keptStores = array();
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
                    $erp_group_address_store = $this->commCustomerErpaccountAddressStoreFactory->create();
                    /* @var $erp_group_address_store \Epicor\Comm\Model\Customer\Erpaccount\Address\Store */
                    $erp_group_address_store->setErpCustomerGroupAddress($this->getId());
                    $erp_group_address_store->setStore($store);
                    $erp_group_address_store->save();
                }
            }
        }
    }

    /**
     * Delete all linked addresses in customers.
     */
    public function beforeDelete()
    {
        parent::beforeDelete();
        $collection = $this->getCustomerAddressCollection();
        foreach ($collection->getItems() as $address) {
            $this->addressRepository->deleteById($address->getId());
        }
    }

    /**
     * Get Address Type Mapping Array
     * @return array
     */
    public function getMappingData()
    {
        return $this->_typeErp2Magento;
    }

    /**
     * Get Address Stores
     * @return array
     */
    public function getStoreIds()
    {

        return array();
    }

    /**
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @return \Magento\Customer\Model\Address
     */
    public function toCustomerAddress($customer, $accountId = null, $collection = null)
    {
        $helper = $this->commMessagingHelper;
        /* @var $helper \Epicor\Comm\Helper\Messaging */

        if (!$accountId) {
            $accountId = $customer->getEccErpaccountId();
        }

        $erp_group = $helper->getErpAccountInfo($accountId);

        $cus_address = $this->getExistingCustomerAddress($customer, $collection);
        $cus_address->setCustomer($customer);
        $cus_address->setData('prefix', $customer->getPrefix());
        $cus_address->setData('firstname', $customer->getFirstname());
        $cus_address->setData('middlename', $customer->getMiddlename());
        $cus_address->setData('lastname', $customer->getLastname());
        $cus_address->setData('suffix', $customer->getSuffix());
        $cus_address->setData('company', $this->getName());

        $street = [];
        $maxStreetLines = 20;
        for ($i = 1; $i <= $maxStreetLines; $i++) {
            if (!is_null($al = $this->getData('address' . $i))) {
                array_push($street, $al);
            }
        }
        if (empty($street)) {
            $street = '';
        }

        $cus_address->setData('street', $street);
        $cus_address->setData('city', $this->getCity());
        $regionName = $helper->getRegionNameOrCode($this->getCountry(), $this->getCounty());
        $cus_address->setData('region', $regionName);

        $countyCode = $this->getCountyCode();
        $regionId = 0;
        if (!empty($countyCode)) {
            $region = $this->directoryRegionFactory->loadByCode($countyCode, $this->getCountry());
            if (!empty($region) && !$region->isObjectNew()) {
                $regionId = $region->getId();
            }
        }
        $cus_address->setData('region_id', $regionId);
        $cus_address->setData('postcode', $this->getPostcode());
        $cus_address->setData('country_id', $this->getCountry()); // NOTE: must be in ISO-2 format
        $cus_address->setData('telephone', $this->getPhone() ? $this->getPhone() : 'N/A');
        $cus_address->setData('ecc_mobile_number', $this->getMobileNumber() ? $this->getMobileNumber() : 'N/A');
        $cus_address->setData('fax', $this->getFax());
        $cus_address->setData('ecc_erp_address_code', $this->getErpCode());
        $cus_address->setData('ecc_erp_group_code', $this->getErpCustomerGroupCode());

        $cus_address->setData('ecc_is_registered', $this->getIsRegistered());
        $cus_address->setData('ecc_is_delivery', $this->getIsDelivery());
        $cus_address->setData('ecc_is_invoice', $this->getIsInvoice());

        $instructions = $this->getInstructions();
        if (!empty($instructions)) {
            $cus_address->setData('ecc_instructions', $instructions);
        }

        // Check if override for default addresses are allowed.
        $commCustomerHelper   = $this->commMessagingCustomerHelper->create();
        $allowDefaultShipping = $commCustomerHelper->shopperShippingDefault($customer);
        $allowDefaultBilling  = $commCustomerHelper->shopperBillingDefault($customer);

        if ($erp_group->getDefaultInvoiceAddressCode() == $this->getErpCode()) {
            $cus_address->setData('is_default_billing', $allowDefaultBilling);
        }

        if ($erp_group->getDefaultDeliveryAddressCode() == $this->getErpCode()) {
            $cus_address->setData('is_default_shipping', $allowDefaultShipping);
        }

        return $cus_address;
    }

    /**
     * Validates the address using the magento customwer address model
     * returning an array of errors
     *
     * @return array()
     */
    public function validate()
    {
        $address = $this->customerAddressFactory->create();
        /* @var $address \Magento\Customer\Model\Address */

        // set firstanem and last name as these won't be set and will fail validation if not
        $address->setData('firstname', 'validate');
        $address->setData('lastname', 'me');
        $address->setData('company', $this->getName());

        $street = array(
            //M1 > M2 Translation Begin (Rule 9)
            //$this->getAddress1(),
            //$this->getAddress2(),
            //$this->getAddress3()
            $this->getData('address1'),
            $this->getData('address2'),
            $this->getData('address3')
            //M1 > M2 Translation End
        );

        $erpCode = $this->getErpCode();
        //M1 > M2 Translation Begin (Rule 9)
        //$address1 = $this->getAddress1();
        $address1 = $this->getData('address1');
        //M1 > M2 Translation End
        $address->setData('street', $street);
        $address->setData('city', $this->getCity());
        $address->setData('region', $this->getCounty());
        $address->setData('region_id', 0);
        $address->setData('postcode', $this->getPostcode());
        $address->setData('country_id', $this->getCountry()); // NOTE: must be in ISO-2 format
        $address->setData('telephone', ($this->getPhone()) ?: 'validateme');
        $address->setData('fax', $this->getFax());
        $address->setData('ecc_mobile_number', $this->getEccMobileNumber());
        $address->setData('ecc_erp_address_code', $erpCode);
        $address->setData('ecc_erp_group_code', $this->getErpCustomerGroupCode());

        $instructions = $this->getInstructions();
        if (!empty($instructions))
            $address->setData('ecc_instructions', $instructions);

        $errors = $address->validate();

        if (empty($erpCode) && $erpCode != 0 && $erpCode != "0") {
            $error = 'Address Code is missing';
            if (!is_array($errors)) {
                $errors = array();
            }
            $errors[] = $error;
        }

        if ($this->getCountry()) {
            try {
                $countryModel = $this->directoryCountryFactory->create()->loadByCode($this->getCountry());
            } catch (\Exception $e) {
                $countryModel = null;
            }
        } else {
            $countryModel = null;
        }

        if (empty($countryModel) || $countryModel->isObjectNew()) {
            $error = 'Unknown country specified';
            if (!is_array($errors)) {
                $errors = array();
            }
            $errors[] = $error;
        }

        if (empty($address1)) {
            $error = 'Address1 is missing';
            if (!is_array($errors)) {
                $errors = array();
            }
            $errors[] = $error;
        }
        return $errors;
    }

    /**
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Magento\Customer\Model\ResourceModel\Address\Collection|null $collection
     * @return \Magento\Customer\Model\Address
     */
    private function getExistingCustomerAddress($customer, $collection = null)
    {
        $collection = $collection ? :$this->customerResourceModelAddressCollectionFactory->create();
        /* @var $collection \Magento\Customer\Model\ResourceModel\Address\Collection */
        $collection->addAttributeToFilter('ecc_erp_address_code', $this->getErpCode());
        $collection->addAttributeToFilter('ecc_erp_group_code', $this->getErpCustomerGroupCode());
        $collection->setCustomerFilter($customer);

        return $collection->getFirstItem();
    }

    /**
     * Reprocesses the brands against the address to ensure the correct stores are assigned to it
     */
    public function brandRefresh()
    {
        $helper = $this->commMessagingHelper;

        // process brands for this erp account
        $brands = unserialize($this->getBrands());
        $stores = array();

        foreach ($brands as $brand) {
            $brandStores = $helper->getStoreFromBranding($brand['company'], $brand['site'], $brand['warehouse'], $brand['group']);
            $stores = $stores + $brandStores;
        }

        $storeCollection = $this->commResourceCustomerErpaccountAddressStoreCollectionFactory->create();
        /* @var $storeCollection \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Store\Collection */
        $storeCollection->addFieldToFilter('erp_customer_group_address', $this->getId());

        foreach ($storeCollection->getItems() as $store) {
            if (!isset($stores[$store->getStore()])) {
                $store->delete();
            } else {
                unset($stores[$store->getStore()]);
            }
        }

        if (!empty($stores)) {
            foreach ($stores as $store) {
                $erp_group_address_store = $this->commCustomerErpaccountAddressStoreFactory->create();
                /* @var $erp_group_address_store \Epicor\Comm\Model\Customer\Erpaccount\Address\Store */
                $erp_group_address_store->setErpCustomerGroupAddress($this->getId());
                $erp_group_address_store->setStore($store->getId());
                $erp_group_address_store->save();
            }
        }
    }

    /**
     * Customer Addresses Collection for the ERP Address
     *
     * @return \Magento\Customer\Model\ResourceModel\Address\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getCustomerAddressCollection()
    {
        $collection = $this->customerResourceModelAddressCollectionFactory->create();
        /* @var $collection \Magento\Customer\Model\ResourceModel\Address\Collection */
        $collection->addAttributeToFilter('ecc_erp_address_code', $this->getErpCode());
        $collection->addAttributeToFilter('ecc_erp_group_code', $this->getErpCustomerGroupCode());
        return $collection;
    }

    /**
     * Customer address ids for ERP address
     *
     * @param array $cusaddresses
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerAddresses($cusaddresses = [])
    {
        $collection = $this->getCustomerAddressCollection();
        $addresses = $collection->getItems();
        foreach ($addresses as $address) {
            $cusaddresses[] = $address->getId();
        }
        return $cusaddresses;
    }

}
