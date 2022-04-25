<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\BranchPickup\Helper;


/**
 * Branch Helper
 *
 * @category   Epicor
 * @package    Epicor_BranchPickup
 * @author     Epicor Websales Team
 */
class Branchpickup extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Epicor\BranchPickup\Helper\DataFactory
     */
    protected $branchPickupHelperFactory;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Epicor\Comm\Model\LocationFactory
     */
    protected $commLocationFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;


    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $directoryCountryFactory;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $directoryRegionFactory;


    /**
     * Sales quote repository
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;


    protected $registry;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Epicor\BranchPickup\Helper\DataFactory $branchPickupHelperFactory,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Comm\Model\LocationFactory $commLocationFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CountryFactory $directoryCountryFactory,
        \Magento\Directory\Model\RegionFactory $directoryRegionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\App\Request\Http $request
    )
    {
        $this->branchPickupHelperFactory = $branchPickupHelperFactory;
        $this->commLocationsHelper = $commLocationsHelper;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->commLocationFactory = $commLocationFactory;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->directoryCountryFactory = $directoryCountryFactory;
        $this->directoryRegionFactory = $directoryRegionFactory;
        $this->quoteRepository = $quoteRepository;
        $this->registry = $registry;
        $this->request = $request;
        parent::__construct(
            $context
        );
    }


    /**
     * Used in grid to return selected customers values.
     *
     * @param int customerid.
     * @return address Data
     */
    public function countLocationBranchPickup()
    {
        $helperData = $this->branchPickupHelperFactory->create();
        /* @var $helper Epicor_BranchPickup_Helper_Data */
        return count($helperData->getSelected());
    }

    /**
     * To check whether the url is secure or not for AJAX calls
     *
     * @param ($_SERVER['HTTPS'])
     * @return boolean
     */
    public function issecure()
    {
        $params = array();
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $params = array(
                '_secure' => true
            );
        } else {
            $params = array(
                '_secure' => false
            );
        }
        return $params;
    }

    public function setBranchLocationFilter($locationcode)
    {
        if (!empty($locationcode)) {
            $helper = $this->commLocationsHelper;
            /* @var $helper Epicor_Comm_Helper_Locations */
            $helper->setCustomerDisplayLocationCodes(array(
                $locationcode
            ));
        }
    }

    public function saveShippingInQuote($locationCode, $payload = null)
    {
        $helperMsging = $this->commMessagingHelper;
        $quote = $this->checkoutSession->getQuote();
        if (!$quote->getItemsCount()) {
            $this->registry->register('dont_send_bsv', true, true);
        }

        $location = $this->commLocationFactory->create();
        $customer = $this->customerSession->getCustomer();
        /* @var $location Epicor_Comm_Model_Location */
        $getLocationData = $location->load($locationCode, 'code');

        $regionId = '';
        $countyCode = $getLocationData->getCountyCode();
        if ($countyCode) {
            $countryModel = $this->directoryCountryFactory->create()->loadByCode($getLocationData->getCountry());
            $countyCode = $this->directoryRegionFactory->create()->loadByCode($getLocationData->getCountyCode(), $countryModel->getId());
            $regionId = $countyCode->getRegionId();
        }

        $customerOrderRef = '';
        $taxExmptRef = '';
        if (isset($payload['ecc_customer_order_ref'])) {
            $customerOrderRef = $payload['ecc_customer_order_ref'];
        }

        if (isset($payload['ecc_tax_exempt_reference'])) {
            $taxExmptRef = $payload['ecc_tax_exempt_reference'];
        }

        if (!$this->customerSession->isLoggedIn()) {
            $firstname = isset($payload['firstname']) ? $payload['firstname'] : '';
            $lastname = isset($payload['lastname']) ? $payload['lastname'] : '';
            $email = isset($payload['email']) ? $payload['email'] : '';
        } else {
            $name = $customer->getName();
            $getName = $this->split_name($name);
            $firstname = $getName[0];
            $lastname = ($getName[1]) ? $getName[1] : ',';
            $email = $customer->getEmail();
        }
        $storeId = $this->storeManager->getStore()->getStoreId();
        $streetvalue = null;
        if ($getLocationData->getData('address1') || $getLocationData->getData('address2') || $getLocationData->getData('address3')) {
            $streetvalue = $getLocationData->getData('address1') . " ," . $getLocationData->getData('address2') . " ," . $getLocationData->getData('address3');
        }
        $shippingAddress = $quote->getShippingAddress()->addData(array(
            'customer_address_id' => '',
            'prefix' => '',
            'firstname' => $firstname,
            'middlename' => '',
            'lastname' => $lastname,
            'suffix' => '',
            'company' => '',
            'email' => $email,
            'street' => $streetvalue,
            'city' => $getLocationData->getCity(),
            'region_id' => $regionId,
            'region' => $getLocationData->getCounty(),
            'country_id' => $getLocationData->getCountry(),
            'postcode' => $getLocationData->getPostcode(),
            'telephone' => $getLocationData->getTelephoneNumber(),
            'fax' => $getLocationData->getFaxNumber(),
            'mobile_number' => $getLocationData->getMobileNumber(),
            'ecc_bsv_carriage_amount' => 0,
            'ecc_bsv_carriage_amount_inc' => 0,
            'shipping_incl_tax' => 0,
            'base_shipping_incl_tax' => 0,
            'shipping_amount' => 0,
            'base_shipping_amount' => 0,
            'save_in_address_book' => 0,
            'ecc_erp_address_code' => null,
            'shipping_method' => \Epicor\BranchPickup\Model\Carrier\Epicorbranchpickup::ECC_BRANCHPICKUP_COMBINE
        ));

        if ($quote->getItemsCount()) {
            $shippingAddress->setCollectShippingRates(true)->collectShippingRates();
        }

        if ($quote->getEccSalesrepChosenCustomerInfo()) {
            $customerInfo = unserialize($quote->getEccSalesrepChosenCustomerInfo());
            if (isset($customerInfo['name'])) {

                $nameParts = explode(' ', $customerInfo['name'], 3);
                $firstname = $nameParts[0];
                if (count($nameParts) == 3) {
                    $lastname = $nameParts[2];
                } else {
                    $lastname = $nameParts[1];
                }
            }
        }

        $billingAddress = $quote->getBillingAddress()->addData(array(
            'customer_address_id' => '',
            'prefix' => '',
            'firstname' => $firstname,
            'middlename' => '',
            'lastname' => $lastname,
            'suffix' => '',
            'company' => '',
            'email' => $email,
            'street' => $streetvalue,
            'city' => $getLocationData->getCity(),
            //'country_id' => 'GB',
            'region' => $getLocationData->getCounty(),
            'postcode' => $getLocationData->getPostcode(),
            'telephone' => $getLocationData->getTelephoneNumber(),
            'fax' => $getLocationData->getFaxNumber(),
            'mobile_number' => $getLocationData->getMobileNumber(),
            'save_in_address_book' => 0,
            'ecc_erp_address_code' => null,
            'shipping_method' => \Epicor\BranchPickup\Model\Carrier\Epicorbranchpickup::ECC_BRANCHPICKUP_COMBINE
        ));

        $ShipStatus = isset($payload['ecc_ship_status_erpcode']) ? $payload['ecc_ship_status_erpcode'] : "";
        $quote->setEccShipStatusErpcode($ShipStatus);
        $quote->setEccCustomerOrderRef($customerOrderRef);
        $quote->setEccTaxExemptReference($taxExmptRef);
        $quote->collectTotals()->save();
        $result = array(
            'type' => 'success'
        );
        return $result;
    }


    public function split_name($name)
    {
        $name = trim($name);
        $last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
        $first_name = trim(preg_replace('#' . $last_name . '#', '', $name));
        return array($first_name, $last_name);
    }

    public function checkPage()
    {
        $controller = $this->request->getControllerName();
        $action = $this->request->getActionName();
        $module = $this->request->getModuleName();
        if ($module == 'checkout' && $controller == 'cart' && $action == 'index') {
            return true;
        } else {
            return false;
        }
    }

}
