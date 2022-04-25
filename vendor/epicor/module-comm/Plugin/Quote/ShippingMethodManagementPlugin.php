<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Quote;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Reflection\DataObjectProcessor;

class ShippingMethodManagementPlugin {


    /**
     * @var \Epicor\Comm\Helper\Cart\SendbsvFactory
     */
    protected $sendBsvHelperFactory;

    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * Customer Address repository
     *
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    protected $customerAddressFactory;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor
     */
    private $dataProcessor;

    /**
     * @var \Epicor\Lists\Helper\Session
     */
    protected $listsSessionHelper;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Epicor\Comm\Helper\Cart\SendbsvFactory $sendBsvHelperFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Model\AddressFactory $customerAddressFactory,
        \Epicor\BranchPickup\Helper\Data $listsSessionHelper,
        \Magento\Framework\Registry $registry
    )
    {
        $this->sendBsvHelperFactory = $sendBsvHelperFactory;
        $this->quoteRepository = $quoteRepository;
        $this->addressRepository = $addressRepository;
        $this->customerAddressFactory = $customerAddressFactory;
        $this->listsSessionHelper = $listsSessionHelper;
        $this->registry = $registry;
    }


    /**
     * By passing Qty validation.
     *
     * @param \Magento\Quote\Model\ShippingMethodManagement $subject
     * @param \Closure $proceed
     * @param int $cartId
     * @param \Magento\Quote\Api\Data\EstimateAddressInterface $address
     * @return array $result
     */

    public function aroundEstimateByAddressId(
        \Magento\Quote\Model\ShippingMethodManagement $subject,
        \Closure $proceed,
        $cartId,
        $address
    ) {
        $this->registry->unregister('QuantityValidatorObserver');
        $this->registry->register('QuantityValidatorObserver', 1);
        $result = $proceed($cartId,$address);
        $this->registry->unregister('QuantityValidatorObserver');
        return $result;
    }
    /**
     * Send BSV after quote collect totals is run
     *
     * @param \Magento\Quote\Model\Quote $subject
     * @param \Magento\Quote\Model\Quote $return
     * @return type
     */
    public function beforeEstimateByAddressId(\Magento\Quote\Model\ShippingMethodManagement $subject, $cartId, $addressId)
    {

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $this->listsSessionHelper->emptyBranchPickup();
        $address = $this->addressRepository->getById($addressId);
        $shippingAddress = $quote->getShippingAddress();
        $addressData = $this->extractAddressData($address);
        if (is_array($addressData['region'])) {
            $addressData['region'] = isset($addressData['region']['region']) ? $addressData['region']['region'] : "";
        }
        $shippingAddress->addData($addressData);
        $shipToAddress = $this->customerAddressFactory->create()->load($addressId);
        $shippingAddress->setEccErpAddressCode($shipToAddress->getEccErpAddressCode());
        $shippingAddress->setCustomerAddressId($address->getId());
        $shippingAddress->setShippingMethod(null);
        $quote->setShippingAddress($shippingAddress);
        $this->quoteRepository->save($quote->collectTotals());

        return [$cartId, $addressId];
    }



    /**
     * Get transform address interface into Array
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface  $address
     * @return array
     */
    private function extractAddressData($address)
    {
        $className = \Magento\Customer\Api\Data\AddressInterface::class;
        if ($address instanceof \Magento\Quote\Api\Data\AddressInterface) {
            $className = \Magento\Quote\Api\Data\AddressInterface::class;
        } elseif ($address instanceof \Magento\Quote\Api\Data\EstimateAddressInterface) {
            $className = \Magento\Quote\Api\Data\EstimateAddressInterface::class;
        }
        return $this->getDataObjectProcessor()->buildOutputDataArray(
            $address,
            $className
        );
    }

    /**
     * Gets the data object processor
     *
     * @return \Magento\Framework\Reflection\DataObjectProcessor
     * @deprecated 101.0.0
     */
    private function getDataObjectProcessor()
    {
        if ($this->dataProcessor === null) {
            $this->dataProcessor = ObjectManager::getInstance()
                ->get(DataObjectProcessor::class);
        }
        return $this->dataProcessor;
    }
}
