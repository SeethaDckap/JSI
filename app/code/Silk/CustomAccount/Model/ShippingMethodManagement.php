<?php

namespace Silk\CustomAccount\Model;

use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Quote\Api\ShipmentEstimationInterface;
use Magento\Quote\Model\ResourceModel\Quote\Address as QuoteAddressResource;
use Magento\Quote\Model\Quote;

class ShippingMethodManagement extends \Magento\Quote\Model\ShippingMethodManagement
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
     * @var \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor
     */
    private $dataProcessor;

    /**
     * @var AddressInterfaceFactory $addressFactory
     */
    private $addressFactory;

    /**
     * @var QuoteAddressResource
     */
    private $quoteAddressResource;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * Constructor
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param Cart\ShippingMethodConverter $converter
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param Quote\TotalsCollector $totalsCollector
     * @param AddressInterfaceFactory|null $addressFactory
     * @param QuoteAddressResource|null $quoteAddressResource
     * @param CustomerSession|null $customerSession
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\Cart\ShippingMethodConverter $converter,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        AddressInterfaceFactory $addressFactory = null,
        QuoteAddressResource $quoteAddressResource = null,
        CustomerSession $customerSession = null
    ) {
        $this->addressFactory = $addressFactory ?: ObjectManager::getInstance()
            ->get(AddressInterfaceFactory::class);
        $this->quoteAddressResource = $quoteAddressResource ?: ObjectManager::getInstance()
            ->get(QuoteAddressResource::class);
        $this->customerSession = $customerSession ?? ObjectManager::getInstance()->get(CustomerSession::class);
        parent::__construct($quoteRepository, $converter, $addressRepository, $totalsCollector, $addressFactory, $quoteAddressResource, $customerSession);
    }

    public function estimateByExtendedAddress($cartId, AddressInterface $address)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [];
        }
        return $this->getShippingMethods($quote, $address);
    }

    /**
     * Get list of available shipping methods
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Framework\Api\ExtensibleDataInterface $address
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     */
    private function getShippingMethods(Quote $quote, $address)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/shippoverride.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('new');

        $output = [];
        $shippingAddress = $quote->getShippingAddress();
        $extractedAddressData = $this->extractAddressData($address);
        $shippingAddressExtensionAttributes = $shippingAddress->getExtensionAttributes();
        $shippingAddressExtensionAttributes->setLiftgate(1);
            $shippingAddressExtensionAttributes->setDelivery(1);
            $shippingAddressExtensionAttributes->setOverlength(1);
            $shippingAddressExtensionAttributes->setResidential(1);
            $shippingAddress->setExtensionAttributes($shippingAddressExtensionAttributes);
        if (array_key_exists('extension_attributes', $extractedAddressData)) {
            $logger->info('Extension Attribute: ' . json_encode($extractedAddressData['extension_attributes']));
            foreach ($extractedAddressData['extension_attributes'] as $code => $value) {
                if($code == 'liftgate'){
                    $shippingAddressExtensionAttributes->setLiftgate($value);
                }
                if($code == 'delivery'){
                    $shippingAddressExtensionAttributes->setDelivery($value);
                }
                if($code == 'overlength'){
                    $shippingAddressExtensionAttributes->setOverlength($value);
                }
                if($code == 'residential'){
                    $shippingAddressExtensionAttributes->setResidential($value);
                }
            }
            unset($extractedAddressData['extension_attributes']);
            $shippingAddress->setExtensionAttributes($shippingAddressExtensionAttributes);
        }

        $shippingAddress->addData($extractedAddressData);
        $shippingAddress->setCollectShippingRates(true);

        $this->totalsCollector->collectAddressTotals($quote, $shippingAddress);
        $quoteCustomerGroupId = $quote->getCustomerGroupId();
        $customerGroupId = $this->customerSession->getCustomerGroupId();
        $isCustomerGroupChanged = $quoteCustomerGroupId !== $customerGroupId;
        if ($isCustomerGroupChanged) {
            $quote->setCustomerGroupId($customerGroupId);
        }
        $shippingRates = $shippingAddress->getGroupedAllShippingRates();
        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $output[] = $this->converter->modelToDataObject($rate, $quote->getQuoteCurrencyCode());
            }
        }
        if ($isCustomerGroupChanged) {
            $quote->setCustomerGroupId($quoteCustomerGroupId);
        }
        return $output;
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
        } elseif ($address instanceof EstimateAddressInterface) {
            $className = EstimateAddressInterface::class;
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
