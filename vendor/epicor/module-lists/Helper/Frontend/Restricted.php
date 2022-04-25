<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Helper\Frontend;

use Magento\Customer\Api\AddressRepositoryInterface;

/**
 * Helper for Restricted purchases on the frontend
 *
 * @category   Epicor1
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Restricted extends \Epicor\Lists\Helper\Frontend
{

    /**
     * @var \Epicor\Comm\Model\LocationFactory
     */
    protected $commLocationFactory;

    /**
     * @var \Magento\Quote\Model\Quote\AddressFactory
     */
    protected $quoteQuoteAddressFactory;
    protected $addressInterface;
    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    public function __construct(
        // FOR PARENT
        \Epicor\Lists\Helper\Context $context,
        \Epicor\Lists\Model\Contract\AddressFactory $listsContractAddressFactory,
        \Epicor\Lists\Model\ListFilterReader $filterReader,
        // FOR THIS CLASS
        \Epicor\Comm\Model\LocationFactory $commLocationFactory,
        \Magento\Quote\Model\Quote\AddressFactory $quoteQuoteAddressFactory,
        \Magento\Quote\Api\Data\AddressInterface $addressInterface,
        \Epicor\AccessRight\Helper\Data $authorization,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->quoteQuoteAddressFactory = $quoteQuoteAddressFactory;
        $this->commLocationFactory = $commLocationFactory;
        $this->addressInterface = $addressInterface;
        $listsSessionHelper = $context->getListsSessionHelper();
        $customerAddressFactory = $context->getCustomerAddressFactory();
        $this->_accessauthorization = $authorization->getAccessAuthorization();
        $this->addressRepository = $addressRepository;
        $this->customerSession = $context->getCustomerSession();
        parent::__construct(
            $context,
            $listsContractAddressFactory,
            $filterReader
        );

    }

    public function isCartAsListActive()
    {
        if (!$this->_accessauthorization->isAllowed('Epicor_Checkout::checkout_checkout_cart_list')) {
            return false;
        }

        return $this->scopeConfig->getValue('epicor_lists/savecartaslist/enablecarttolistat');
    }
    public function getRestrictionAddress()
    {
        $customer = $this->customerSessionFactory->create()->getCustomer();
        /* @var $customer Epicor_Comm_Model_Customer */
        $quote = $this->checkoutSession->getQuoteOnly();
        /* @var $quote Epicor_Comm_Model_Quote */

        if ($quote->getShippingAddress()->getPostcode()) {
            $address = $quote->getShippingAddress();
        } else if ($customer->getDefaultShippingAddress()) {
            $address = $customer->getDefaultShippingAddress();
        }

        //If Branch Pickup was selected, then add that address also here
        $checkBranchPickup = $this->getBranchPickupAddress();
        if ($checkBranchPickup) {
            $locationData = $this->getLocationAddress($checkBranchPickup, 'delivery');
            $addressData = $this->quoteQuoteAddressFactory->create();
            $address = $addressData->setData($locationData);
        }

        return $address;
    }

    public function getBranchPickupAddress()
    {
        $sessionHelper = $this->listsSessionHelper;
        /* @var $sessionHelper Epicor_Lists_Helper_Session */
        return $sessionHelper->getValue('ecc_selected_branchpickup');
    }

    public function getLocationAddress($locationCode, $page = 'delivery')
    {
        $location = $this->commLocationFactory->create();
        /* @var $location Epicor_Comm_Model_Location */
        $getLocationData = $location->load($locationCode, 'code');
        $helper = $this->commMessagingHelper;
        /* @var $helper Epicor_Comm_Helper_Messaging */
        $customer = $this->customerSessionFactory->create()->getCustomer();
        if (!empty($getLocationData)) {
            $Name = explode(' ', $customer->getName(), 2);
            $firstName = $Name[0];
            $lastName = !empty($Name[1]) ? $Name[1] : "";
            $addressD1 = $getLocationData->getAddress1();
            $addressD2 = $getLocationData->getAddress2();
            $addressD3 = $getLocationData->getAddress3();
            $address1 = !empty($addressD1) ? $helper->stripNonPrintableChars($addressD1) : "";
            $address2 = !empty($addressD2) ? "  " . $helper->stripNonPrintableChars($addressD2) : "";
            $address3 = !empty($addressD3) ? "  " . $helper->stripNonPrintableChars($addressD3) : "";
            $countyCode = $getLocationData->getCountyCode();
            $regionId = '';
            if ($countyCode) {
                $countryModel = $this->directoryCountryFactory->create()->loadByCode($getLocationData->getCountry());
                $countyCode = $this->directoryRegionFactory->create()->loadByCode($getLocationData->getCountyCode(), $countryModel->getId());
                $regionId = $countyCode->getRegionId();
            }

            $addressData['shipping'] = array(
                'locationid' => $helper->stripNonPrintableChars($getLocationData->getId()),
                'ecc_erp_address_code' => $locationCode,
                'firstname' => $helper->stripNonPrintableChars($firstName),
                'lastname' => $helper->stripNonPrintableChars($lastName),
                'street1' => trim($address1),
                'street2' => trim($address2),
                'street3' => trim($address3),
                'city' => $helper->stripNonPrintableChars($getLocationData->getCity()),
                'region' => $helper->stripNonPrintableChars($getLocationData->getCounty()),
                'region_id' => $regionId,
                'country_id' => $getLocationData->getCountry(),
                'postcode' => $helper->stripNonPrintableChars($getLocationData->getPostcode()),
                'email' => $helper->stripNonPrintableChars($getLocationData->getEmailAddress()),
                'telephone' => $helper->stripNonPrintableChars($getLocationData->getTelephoneNumber()),
                'mobile_number' => $helper->stripNonPrintableChars($getLocationData->getMobileNumber()),
                'fax' => $helper->stripNonPrintableChars($getLocationData->getFaxNumber())
            );
            //If its delivery page, then add street and return the array without "shipping"
            if ($page == "delivery") {
                $addressData['shipping']['street'] = $address1 . ' ' . $address2 . ' ' . $address3;
                return $addressData['shipping'];
            } else {
                return $addressData;
            }
        }
    }

    public function setRestrictionAddress($addressId)
    {
        $shipToAddress = $this->getAddressFromSelectParam($addressId);
        /* @var $shipToAddress Mage_Customer_Model_Address */

        $currentCart = $this->checkoutSession;
        $quoteId = $currentCart->getQuoteId();
        $quote = $this->checkoutCartFactory->create()->getQuote();
        /* @var $quote Epicor_Comm_Model_Quote */
        $this->registry->unregister('dont_send_bsv');
        $this->registry->registry('dont_send_bsv', true);

        $quoteShippingAddress = $this->quoteQuoteAddressFactory->create();
        $quoteShippingAddress->setData($shipToAddress->getData());
        $quoteShippingAddress->setCustomerAddressId($shipToAddress->getId());
        $quote->setShippingAddress($quoteShippingAddress);
        $quote->getShippingAddress()->setCustomerAddressId($shipToAddress->getId());
        $quote->getShippingAddress()->setEccErpAddressCode($shipToAddress->getEccErpAddressCode());
        $quote->collectTotals()->save();
        if (!$quoteId) {
            $this->checkoutSession->setQuoteId($quote->getId());
        }
    }

    protected function getAddressFromSelectParam($addressId)
    {
        $customer = $this->customerSessionFactory->create()->getCustomer();
        /* @var $customer Epicor_Comm_Model_Customer */
        $shipToAddress = $customer->getDefaultShippingAddress();
        /* @var $shipToAddress Mage_Customer_Model_Address */

        if ($addressId) {
            $b2bHierarchyMasquerade = $this->customerSession->getB2BHierarchyMasquerade();
            if ($this->isMasquerading() === true && ($customer->isSalesRep() === true || $b2bHierarchyMasquerade)) {
                $addId = explode('erpaddress_', $addressId)[1];
                $shipToAddress = $this->commMessagingHelper->getSalesrepAddress(
                    $this->customerSessionFactory->create()->getMasqueradeAccountId(),
                    $addId
                );
                $shipToAddress->setId($addressId);
                $shipToAddress->implodeStreetAddress();
            } else if ($this->isMasquerading() === true && $customer->isSalesRep() === false) {
                $address       = $this->addressRepository->getById($addressId);
                $shipToAddress = $this->customerAddressFactory->create();
                $shipToAddress = $shipToAddress->updateData($address);
                $shipToAddress->implodeStreetAddress();
            } else {
                $customerAddress = $customer->getAddressById($addressId);
                /* @var $customerAddress Mage_Customer_Model_Address */
                if ($customerAddress->isObjectNew() == false) {
                    $shipToAddress = $customerAddress;
                }
            }
        }

        return $shipToAddress;
    }

    /**
     * Get all sku of particular address
     *
     * @param string $addressId
     * @return array
     */
    public function checkProductAddress($addressId, $new = false, $newType = '')
    {
        $cartItems = $this->getCartItems();

        $helper = $this->listsFrontendProductHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Product */

        $helper->resetLists();

        if ($new) {
            $addressData = $this->quoteQuoteAddressFactory->create();
            $addressData->setData($addressId);
        } else {
            $addressData = $this->getAddressFromSelectParam($addressId);
        }

        $this->registry->register('checkproduct-address-data', $addressData);
        $listsHelper = $this->listsFrontendProductHelper;
        $listsHelper->getActiveLists();
        $this->registry->unregister('checkproduct-address-data');

        // There are some lists, so get the products for them

        /* @var $listsHelper Epicor_Lists_Helper_Frontend_Product */
        $productCollection = $this->catalogResourceModelProductCollectionFactory->create();
        /* @var $productCollection Mage_Catalog_Model_Resource_Product_Collection */
        $this->performContractProductFiltering($productCollection);
        $productIds = $productCollection->getAllIds();
        $this->registry->unregister('force_products_reload');

        $filteredProds = array_filter($productIds);
        if (!empty($filteredProds)) {
            //comparing keys is much muck quicker than using array_diff
            $resultKeys = array_diff_key(array_flip($cartItems), array_flip($productIds));
            $result = array_flip($resultKeys);
        } else {
            $result = array();
        }

        return $result;
    }

    /**
     * Get all sku of particular address
     *
     * @param string $address
     *
     * @return array
     */
    public function checkProductAddressNew($address, $type)
    {
        return $this->checkProductAddress($address, true, $type);
    }

}
