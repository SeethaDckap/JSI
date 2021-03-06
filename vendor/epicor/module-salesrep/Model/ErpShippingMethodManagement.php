<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Epicor\SalesRep\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Reflection\DataObjectProcessor;
//use Magento\Quote\Api\ShipmentEstimationInterface;
use Magento\Quote\Model\Quote;

/**
 * Shipping method read service.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ErpShippingMethodManagement implements
    \Epicor\SalesRep\Api\ErpShippingMethodManagementInterface,
    \Magento\Quote\Model\ShippingMethodManagementInterface,
    \Magento\Quote\Api\ShipmentEstimationInterface
{
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * Shipping method converter
     *
     * @var \Magento\Quote\Model\Cart\ShippingMethodConverter
     */
    protected $converter;

    /**
     * Customer Address repository
     *
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var Quote\TotalsCollector
     */
    protected $totalsCollector;

    /**
     * @var \Epicor\Comm\Model\Customer\Erpaccount\AddressFactory
     */
    protected $commCustomerErpaccountAddressFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManager;

    protected $customerCustomerFactory;
    /**
     * Constructs a shipping method read service object.
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param Cart\ShippingMethodConverter $converter
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param Quote\TotalsCollector $totalsCollector
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\Cart\ShippingMethodConverter $converter,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        \Epicor\Comm\Model\Customer\Erpaccount\AddressFactory $commCustomerErpaccountAddressFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->converter = $converter;
        $this->addressRepository = $addressRepository;
        $this->totalsCollector = $totalsCollector;
        $this->commCustomerErpaccountAddressFactory = $commCustomerErpaccountAddressFactory;
        $this->_customerSession = $customerSession;
         $this->_cookieManager = $cookieManager;
        $this->customerCustomerFactory = $customerCustomerFactory;

    }


    /**
     * {@inheritDoc}
     */
    public function estimateByAddressId($cartId, $addressId)
    {
        if (strpos($addressId, 'erpaddress_') !== false){
            $this->_cookieManager->setPublicCookie('erp_shipping_customer_addressId', $addressId);
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->quoteRepository->getActive($cartId);
            $addressId = str_replace('erpaddress_', '', $addressId);

            // no methods applicable for empty carts or carts with virtual products
            if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
                return [];
            }

            $customerSession = $this->_customerSession;
            $customer = $customerSession->getCustomer();

            /*$customerSession->setShippingCustomerAddressId($addressId); */
            $address = $this->commCustomerErpaccountAddressFactory->create()->load($addressId);

            $choosen_customerId = $quote->getEccSalesrepChosenCustomerId();

            if ($customer->isSalesRep() && $choosen_customerId) {
                $salesRepCustomer = $this->customerCustomerFactory->create()->load($choosen_customerId);
                $addressData = $address->toCustomerAddress($salesRepCustomer)->getData();
            }else{
                $addressData = $address->toCustomerAddress($customer)->getData();
            }
            $address = $quote->getShippingAddress();
            $address->addData($addressData);
            $address->setCustomerAddressId(null);
            $quote->setShippingAddress($address);
            $quote->collectTotals()->save();
            return $this->getEstimatedRates(
                $quote,
                $addressData['country_id'],
                $addressData['postcode'],
                $addressData['region_id'],
                $addressData['region']
            );
        }else{
            return $this->getEstimatedRates(
                $quote,
                "",
                "",
                "",
                ""
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function get($cartId)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        /** @var \Magento\Quote\Model\Quote\Address $shippingAddress */
        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress->getCountryId()) {
            throw new StateException(__('Shipping address not set.'));
        }

        $shippingMethod = $shippingAddress->getShippingMethod();
        if (!$shippingMethod) {
            return null;
        }

        $shippingAddress->collectShippingRates();
        /** @var \Magento\Quote\Model\Quote\Address\Rate $shippingRate */
        $shippingRate = $shippingAddress->getShippingRateByCode($shippingMethod);
        if (!$shippingRate) {
            return null;
        }
        return $this->converter->modelToDataObject($shippingRate, $quote->getQuoteCurrencyCode());
    }

    /**
     * {@inheritDoc}
     */
    public function getList($cartId)
    {
        $output = [];

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [];
        }

        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress->getCountryId()) {
            throw new StateException(__('Shipping address not set.'));
        }
        $shippingAddress->collectShippingRates();
        $shippingRates = $shippingAddress->getGroupedAllShippingRates();
        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $output[] = $this->converter->modelToDataObject($rate, $quote->getQuoteCurrencyCode());
            }
        }
        return $output;
    }

    /**
     * {@inheritDoc}
     */
    public function set($cartId, $carrierCode, $methodCode)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        try {
            $this->apply($cartId, $carrierCode, $methodCode);
        } catch (\Exception $e) {
            throw $e;
        }

        try {
            $this->quoteRepository->save($quote->collectTotals());
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Cannot set shipping method. %1', $e->getMessage()));
        }
        return true;
    }

    /**
     * @param int $cartId The shopping cart ID.
     * @param string $carrierCode The carrier code.
     * @param string $methodCode The shipping method code.
     * @return void
     * @throws InputException The shipping method is not valid for an empty cart.
     * @throws CouldNotSaveException The shipping method could not be saved.
     * @throws NoSuchEntityException Cart contains only virtual products. Shipping method is not applicable.
     * @throws StateException The billing or shipping address is not set.
     */
    public function apply($cartId, $carrierCode, $methodCode)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (0 == $quote->getItemsCount()) {
            throw new InputException(__('Shipping method is not applicable for empty cart'));
        }
        if ($quote->isVirtual()) {
            throw new NoSuchEntityException(
                __('Cart contains virtual product(s) only. Shipping method is not applicable.')
            );
        }
        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress->getCountryId()) {
            throw new StateException(__('Shipping address is not set'));
        }
        $shippingAddress->setShippingMethod($carrierCode . '_' . $methodCode);
    }

    /**
     * {@inheritDoc}
     */
    public function estimateByAddress($cartId, \Magento\Quote\Api\Data\EstimateAddressInterface $address)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [];
        }

        return $this->getEstimatedRates(
            $quote,
            $address->getCountryId(),
            $address->getPostcode(),
            $address->getRegionId(),
            $address->getRegion()
        );
    }

    /**
     * @inheritdoc
     */
    public function estimateByExtendedAddress($cartId, AddressInterface $address)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [];
        }
        return $this->getShippingMethods($quote, $address->getData());
    }

    /**
     * Get estimated rates
     *
     * @param Quote $quote
     * @param int $country
     * @param string $postcode
     * @param int $regionId
     * @param string $region
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[] An array of shipping methods.
     */
    protected function getEstimatedRates(\Magento\Quote\Model\Quote $quote, $country, $postcode, $regionId, $region)
    {
        $data = [
            EstimateAddressInterface::KEY_COUNTRY_ID => $country,
            EstimateAddressInterface::KEY_POSTCODE => $postcode,
            EstimateAddressInterface::KEY_REGION_ID => $regionId,
            EstimateAddressInterface::KEY_REGION => $region
        ];
        return $this->getShippingMethods($quote, $data);
    }

    /**
     * Get list of available shipping methods
     * @param \Magento\Quote\Model\Quote $quote
     * @param array $addressData
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     */
    private function getShippingMethods(Quote $quote, array $addressData)
    {
        $output = [];
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->addData($addressData);
        $shippingAddress->setCollectShippingRates(true);

        $this->totalsCollector->collectAddressTotals($quote, $shippingAddress);
        $shippingRates = $shippingAddress->getGroupedAllShippingRates();
        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $output[] = $this->converter->modelToDataObject($rate, $quote->getQuoteCurrencyCode());
            }
        }
        return $output;
    }
}
