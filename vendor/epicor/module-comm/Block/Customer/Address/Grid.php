<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
declare(strict_types=1);

namespace Epicor\Comm\Block\Customer\Address;

use Epicor\AccessRight\Helper\Data as AccessrightsHelper;
use Epicor\Common\Helper\Data;
use Magento\Customer\Model\ResourceModel\Address\CollectionFactory as AddressCollectionFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Customer address grid
 */
class Grid extends \Magento\Framework\View\Element\Template
{
    const XML_PATH_DEFAULT_SHIPPING_OVERRIDE = 'epicor_comm_field_mapping/cus_mapping/cus_default_shipping_override';
    const EDIT_RESOURCE   = 'Epicor_Customer::my_account_address_book_edit';
    const DELETE_RESOURCE = 'Epicor_Customer::my_account_address_book_delete';
    const CREATE_RESOURCE = 'Epicor_Customer::my_account_address_book_create';

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    private $currentCustomer;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Address\CollectionFactory
     */
    private $addressCollectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Address\Collection
     */
    private $addressCollection;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    protected $_customer;

    protected $_erpAcctCounts;

    /**
     * Access rights helper.
     *
     * @var AccessrightsHelper
     */
    private $accessRightHelper;

    /**
     * Common helper.
     *
     * @var Data
     */
    private $commonHelper;


    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param AddressCollectionFactory $addressCollectionFactory
     * @param CountryFactory $countryFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        AddressCollectionFactory $addressCollectionFactory,
        CountryFactory $countryFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        AccessrightsHelper $accessRightHelper,
        Data $commonHelper,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->currentCustomer = $currentCustomer;
        $this->addressCollectionFactory = $addressCollectionFactory;
        $this->countryFactory = $countryFactory;
        $this->productMetadata = $productMetadata;
        $this->_customer = $this->customerSession->getCustomer();
        $this->_erpAcctCounts = $this->_customer->getErpAcctCounts();
        $this->accessRightHelper = $accessRightHelper;
        $this->commonHelper      = $commonHelper;

        parent::__construct($context, $data);
    }

    /**
     * Prepare the Address Book section layout
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 102.0.1
     */
    protected function _prepareLayout()
    {
        if($this->isGridCompatible()) {
            parent::_prepareLayout();
            $this->preparePager();
        }
    }

    /**
     * Generate and return "New Address" URL
     *
     * @return string
     * @since 102.0.1
     */
    public function getAddAddressUrl()
    {
        return $this->getUrl('customer/address/new', ['_secure' => true]);
    }

    /**
     * Generate and return "Delete" URL
     *
     * @return string
     * @since 102.0.1
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('customer/address/delete');
    }

    /**
     * Generate and return "Edit Address" URL.
     *
     * Address ID passed in parameters
     *
     * @param int $addressId
     * @return string
     * @since 102.0.1
     */
    public function getAddressEditUrl($addressId)
    {
        return $this->getUrl('customer/address/edit', ['_secure' => true, 'id' => $addressId]);
    }

    /**
     * Get current additional customer addresses
     *
     * Return array of address interfaces if customer has additional addresses and false in other cases
     *
     * @return \Magento\Customer\Api\Data\AddressInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws NoSuchEntityException
     * @since 102.0.1
     */
    public function getAdditionalAddresses()
    {
        $additional = [];
        $addresses = $this->getAddressCollection();
        $primaryAddressIds = [$this->getDefaultBilling(), $this->getDefaultShipping()];
        if (count($this->_erpAcctCounts) > 1) {
            $defBillingCode = $this->_customer->getDefaultBillingAddress()->getData("ecc_erp_address_code");
            $defShippingCode = $this->_customer->getDefaultShippingAddress()->getData("ecc_erp_address_code");
            $primaryAddressIds = [$defBillingCode, $defShippingCode];
        }
        foreach ($addresses as $address) {
            $addressId = (int)$address->getId();
            if (count($this->_erpAcctCounts) > 1) {
                $addressId = $address->getData("ecc_erp_address_code");
            }
            if (!in_array($addressId, $primaryAddressIds, true)) {
                $additional[] = $address;
            }
        }
        return $additional;
    }

    /**
     * Get current customer
     *
     * Return stored customer or get it from session
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @since 102.0.1
     */
    public function getCustomer()
    {
        $customer = $this->getData('customer');
        if ($customer === null) {
            $customer = $this->currentCustomer->getCustomer();
            $this->setData('customer', $customer);
        }
        return $customer;
    }

    /**
     * Get one string street address from the Address DTO passed in parameters
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return string
     * @since 102.0.1
     */
    public function getStreetAddress(\Magento\Customer\Api\Data\AddressInterface $address)
    {
        $street = $address->getStreet();
        if (is_array($street)) {
            $street = implode(', ', $street);
        }
        return $street;
    }

    /**
     * Get country name by $countryCode
     *
     * Using \Magento\Directory\Model\Country to get country name by $countryCode
     *
     * @param string $countryCode
     * @return string
     * @since 102.0.1
     */
    public function getCountryByCode(string $countryCode)
    {
        /** @var \Magento\Directory\Model\Country $country */
        $country = $this->countryFactory->create();
        return $country->loadByCode($countryCode)->getName();
    }

    /**
     * Get default billing address
     *
     * Return address string if address found and null if not
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDefaultBilling()
    {
        $customer = $this->getCustomer();

        return (int)$customer->getDefaultBilling();
    }

    /**
     * Get default shipping address
     *
     * Return address string if address found and null if not
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDefaultShipping()
    {
        $customer = $this->getCustomer();

        return (int)$customer->getDefaultShipping();
    }

    /**
     * Get pager layout
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function preparePager()
    {
        $addressCollection = $this->getAddressCollection();
        if (null !== $addressCollection) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'customer.addresses.pager'
            )->setCollection($addressCollection);
            $this->setChild('pager', $pager);
        }
    }

    /**
     * Get customer addresses collection.
     *
     * Filters collection by customer id
     *
     * @return \Magento\Customer\Model\ResourceModel\Address\Collection
     * @throws NoSuchEntityException
     */
    private function getAddressCollection()
    {
        if (null === $this->addressCollection) {
            if (null === $this->getCustomer()) {
                throw new NoSuchEntityException(__('Customer not logged in'));
            }
            /** @var \Magento\Customer\Model\ResourceModel\Address\Collection $collection */
            //$collection = $this->getCustomerSession()->getCustomer()->getAddressesCollection();
            $collection = $this->addressCollectionFactory->create();
            $collection->addAttributeToSelect('ecc_erp_address_code');
            $collection->setOrder('entity_id', 'desc')
                ->setCustomerFilter([$this->getCustomer()->getId()]);
            if (count($this->_erpAcctCounts) > 1) {
                $erpInfo = $this->_customer->getCustomerErpAccount();
                $erpCustomerGroupCode = $erpInfo->getData("erp_code");
                $attributes = [
                    ['attribute' => 'ecc_erp_group_code', 'eq' => $erpCustomerGroupCode],
                    ['attribute' => 'ecc_erp_group_code', 'null' => true],
                ];
                $collection->addAttributeToFilter($attributes, null, 'left');
            }
            $this->addressCollection = $collection;
        }
        return $this->addressCollection;
    }

    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * Get customer address by ID
     *
     * @param int $addressId
     * @return \Magento\Customer\Api\Data\AddressInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAddressById($addressId)
    {
        try {
            return $this->addressRepository->getById($addressId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * validate address book
     * Grid magento Compatible  >= 2.3.1
     *
     * @return bool
     */
    public function isGridCompatible()
    {
        if ($this->productMetadata->getVersion() > '2.3.0') {
            return true;
        }
        return false;
    }


    /**
     * Allow delete link for address.
     *
     * @param mixed $address Customer address model.
     *
     * @return boolean
     */
    public function canDelete($address)
    {
        $hasErpAddressCode = (
            empty($address->getEccErpAddressCode())
            && $address->getEccErpAddressCode() !== 0
            && $address->getEccErpAddressCode() !== '0'
        ) ? false : true;

        $accessRightDelete = $this->accessRightHelper->isAllowed(self::DELETE_RESOURCE);
        $allowAddition     = $this->commonHelper->customerAddressPermissionCheck('create');
        $isGuest           = $this->_customer->isGuest();

        return $isGuest || (!$hasErpAddressCode && $accessRightDelete && $allowAddition);

    }//end canDelete()


    /**
     * Allow edit link for address.
     *
     * @param mixed $address Customer address model.
     *
     * @return boolean
     */
    public function canEdit($address)
    {
        $allowDefaultAddressOverride = $this->_scopeConfig->isSetFlag(
            self::XML_PATH_DEFAULT_SHIPPING_OVERRIDE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $allowAddition               = $this->commonHelper->customerAddressPermissionCheck('create');
        $allowDefaultAddressOverride =  (((!$address->getEccErpAddressCode() && $address->getEccErpAddressCode() !== 0 && $address->getEccErpAddressCode() !== '0') &&
                ($allowAddition || $allowDefaultAddressOverride)) ||
            (($address->getEccErpAddressCode() || $address->getEccErpAddressCode() === 0 || $address->getEccErpAddressCode() === '0') && $allowDefaultAddressOverride));

        $accessRightEdits = $this->accessRightHelper->isAllowed(self::EDIT_RESOURCE);
        $isGuest          = $this->_customer->isGuest();

        return $isGuest || ($allowDefaultAddressOverride && $accessRightEdits);

    }//end canEdit()


    /**
     * Allow edit link for address.
     *
     * @return boolean
     */
    public function canCreate()
    {
        $accessRightEdits = $this->accessRightHelper->isAllowed(self::CREATE_RESOURCE);
        $allowAddition    = $this->commonHelper->customerAddressPermissionCheck('create');

        return $accessRightEdits && $allowAddition;

    }//end canCreate()


}

