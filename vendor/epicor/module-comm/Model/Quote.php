<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model;

use Epicor\Comm\Model\MinOrderAmountFlag;
use Magento\Framework\App\Http;
use Magento\Store\Model\ScopeInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurable;
use Magento\Bundle\Model\Product\Type as TypeBundle;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class Quote extends \Magento\Quote\Model\Quote
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Sales\Helper\Data
     */
    protected $salesHelper;

    /**
     * @var \Epicor\Common\Helper\Cart
     */
    protected $commonCartHelper;

    /**
     * @var \Magento\Quote\Model\Quote\ItemFactory
     */
    protected $quoteQuoteItemFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    protected $_isSalesRep=null;
    protected $_canDisplayCartContracts=null;

    private $minOrderAmountFlag;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;
    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    private $locationsHelper;


    public function __construct(
        MinOrderAmountFlag $minOrderAmountFlag,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Quote\Model\Quote\AddressFactory $quoteAddressFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollectionFactory,
        \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory,
        \Magento\Framework\Message\Factory $messageFactory,
        \Magento\Sales\Model\Status\ListFactory $statusListFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Quote\Model\Quote\PaymentFactory $quotePaymentFactory,
        \Magento\Quote\Model\ResourceModel\Quote\Payment\CollectionFactory $quotePaymentCollectionFactory,
        \Magento\Framework\DataObject\Copy $objectCopyService,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Quote\Model\Quote\Item\Processor $itemProcessor,
        \Magento\Framework\DataObject\Factory $objectFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        \Magento\Quote\Model\Cart\CurrencyFactory $currencyFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        \Magento\Quote\Model\Quote\TotalsReader $totalsReader,
        \Magento\Quote\Model\ShippingFactory $shippingFactory,
        \Magento\Quote\Model\ShippingAssignmentFactory $shippingAssignmentFactory,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        \Magento\Sales\Helper\Data $salesHelper,
        \Epicor\Common\Helper\Cart $commonCartHelper,
        \Magento\Quote\Model\Quote\ItemFactory $quoteQuoteItemFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Framework\App\Request\Http $request = null,
        \Epicor\Comm\Helper\Locations $locationsHelper = null,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->commHelper = $commHelper;
        $this->eventManager = $context->getEventDispatcher();
        $this->storeManager = $storeManager;
        $this->salesHelper = $salesHelper;
        $this->commonCartHelper = $commonCartHelper;
        $this->quoteQuoteItemFactory = $quoteQuoteItemFactory;
        $this->checkoutSession = $checkoutSession;
        $this->registry = $registry;
        $this->customerFactory = $customerFactory;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $quoteValidator,
            $catalogProduct,
            $scopeConfig,
            $storeManager,
            $config,
            $quoteAddressFactory,
            $customerFactory,
            $groupRepository,
            $quoteItemCollectionFactory,
            $quoteItemFactory,
            $messageFactory,
            $statusListFactory,
            $productRepository,
            $quotePaymentFactory,
            $quotePaymentCollectionFactory,
            $objectCopyService,
            $stockRegistry,
            $itemProcessor,
            $objectFactory,
            $addressRepository,
            $criteriaBuilder,
            $filterBuilder,
            $addressDataFactory,
            $customerDataFactory,
            $customerRepository,
            $dataObjectHelper,
            $extensibleDataObjectConverter,
            $currencyFactory,
            $extensionAttributesJoinProcessor,
            $totalsCollector,
            $totalsReader,
            $shippingFactory,
            $shippingAssignmentFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->minOrderAmountFlag = $minOrderAmountFlag;
        $this->request = $request;
        $this->locationsHelper = $locationsHelper;
    }


    public function validateMinimumAmount($multishipping = false)
    {
        if($this->getData('arpayments_quote')) {
            return true;
        }

        $minOrderMulti = $this->isSiteMultiAddressMinOrderEnabled();
        $minAmount = $this->getErpAccountMinOrderAmount();

        if (!$this->minOrderAmountFlag->isMinOrderActive($this->getData('ecc_erp_account_id'))) {
            return true;
        }

        $addresses = $this->getAllAddresses();

        if ($multishipping) {
            if ($minOrderMulti) {
                foreach ($addresses as $address) {
                    foreach ($address->getQuote()->getItemsCollection() as $item) {
                        $amount = $item->getBaseRowTotal() - $item->getBaseDiscountAmount();
                        if ($amount < $minAmount) {
                            return false;
                        }
                    }
                }
            } else {
                $baseTotal = 0;
                foreach ($addresses as $address) {
                    /* @var $address Mage_Sales_Model_Quote_Address */
                    $baseTotal += $address->getBaseSubtotalWithDiscount();
                }
                if ($baseTotal < $minAmount) {
                    return false;
                }
            }
        } else {
            foreach ($addresses as $address) {
                /* @var $address Mage_Sales_Model_Quote_Address */
                if (!$address->validateMinimumAmount()) {
                    return false;
                }
            }
        }
        return true;
    }

    private function getErpAccountMinOrderAmount()
    {
        return $this->commHelper->getMinimumOrderAmount($this->customerFactory->create()->getEccErpaccountId());
    }

    private function isSiteLevelMinOrderEnabled(): bool
    {
        return (bool) $this->scopeConfig->isSetFlag(
            'sales/minimum_order/active',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    private function isSiteMultiAddressMinOrderEnabled(): bool
    {
        return (bool) $this->scopeConfig->isSetFlag(
            'sales/minimum_order/multi_address',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }


    public function addLine($product, $options)
    {
        return $this->addOrUpdateLine($product, $options, true);
    }

    public function addOrUpdateLine($product, $options, $newLine = false)
    {
        $helper = $this->commonCartHelper;
        /* @var $helper \Epicor\Common\Helper\Cart */

        if ($newLine) {
            $options['new_line'] = 1;
        }

        $helper->processQuoteLine($this, $product, $options);

        return $this;
    }

    public function findItem($product, $options)
    {
        $locationCode = isset($options['location_code']) ? $options['location_code'] : '';

        if (is_array($locationCode)) {
            $locationCode = isset($locationCode[$product->getId()]) ? $locationCode[$product->getId()] : false;
        }

        $itemId = isset($options['request']['cart_item_id']) ? $options['request']['cart_item_id'] : false;

        if ($itemId) {
            return $this->getItemById($itemId);
        }

        $productItem = $this->quoteQuoteItemFactory->create();
        foreach ($this->getAllItems() as $item) {
            /* @var $item Mage_Sales_Model_Quote_Item */
            $match = true;

            if (isset($options['super_attribute'])) {
                if ($product && $product->getTypeId() == 'configurable') {
                    $productChild = $product->getTypeInstance()
                        ->getProductByAttributes($options['super_attribute'], $product);
                    if ($item->getSku() != $productChild->getSku()) {
                        $match = false;
                    }
                }
            }
            if ($item->getProductId() != $product->getId()) {
                $match = false;
            }

            if (!empty($locationCode) && $item->getEccLocationCode() != $locationCode) {
                $match = false;
            }

            //Kit product and same child product add with different rows
            if (isset($options['bundle_option'])
                && $product->getTypeId() == "simple"
                && $item->getProductId() == $product->getId()
            ) {
                $match = false;
            }

            //Kit product and same child product add with different rows
            if ($product->getTypeId() == "simple"
                && $item->getProductId() == $product->getId()
                && $item->getParentItemId() !== null
            ) {
                $match = false;
            }

            if ($match) {
                $productItem = $item;
                break;
            }
        }

        return $productItem;
    }

    /**
     * Get Customer Order Reference
     *
     * @return string
     */
    public function getCustomerRef()
    {

        $customerRef = $this->getData('customer_ref');
        if (empty($customerRef)) {
            $customerRef = $this->checkoutSession->getEccCustomerOrderRef();
        }

        return $customerRef;
    }

    /**
     * Save related items
     *
     * @return \Epicor\Comm\Model\Quote
     */
    public function afterSave()
    {
        parent::afterSave();

        $items = $this->getAllItems();
        if (empty($items) || count($items) == 0) {
            $this->checkoutSession->unsetData('ecc_customer_order_ref');
            $this->checkoutSession->unsetData('ecc_tax_exempt_reference');
        }

        return $this;
    }

    /**
     * Assign customer model to quote with billing and shipping address change
     *
     * @param  \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param  \Magento\Quote\Model\Quote\Address $billingAddress
     * @param  \Magento\Quote\Model\Quote\Address $shippingAddress
     * @return \Magento\Quote\Model\Quote
     */
    public function assignCustomerWithAddressChange(
        \Magento\Customer\Api\Data\CustomerInterface $customer,
        \Magento\Quote\Model\Quote\Address $billingAddress = null,
        \Magento\Quote\Model\Quote\Address $shippingAddress = null
    )
    {
        $customerAddressId = $this->getShippingAddress()->getCustomerAddressId();
        $erpAddressCode = $this->getShippingAddress()->getEccErpAddressCode();

        if (
            is_null($shippingAddress) &&
            ($erpAddressCode ||
                $customerAddressId)
        ) {
            $this->registry->unregister('dont_send_bsv');
            $this->registry->registry('dont_send_bsv', true);
            $shippingAddressData = false;
            if ($customerAddressId) {
                $shippingAddressData = $this->customerFactory->create()->getAddressById($customerAddressId);
            } else {
                $customerModel = $this->customerFactory->create()->load($this->getCustomer()->getId());
                $addresses = $customerModel->getAddresses();
                foreach ($addresses as $address) {
                    /* @var $address Epicor_Comm_Model_Customer_Address */
                    if ($address->getEccErpAddressCode() == $erpAddressCode) {
                        $shippingAddressData = $address;
                        break;
                    }
                }
            }

            $shippingAddress = $this->_quoteAddressFactory->create();
            if ($shippingAddressData) {
                $shippingAddress->setData($shippingAddressData->getData());
            }
            $shippingAddress->setCustomerAddressId($customerAddressId);
        }
        return parent::assignCustomerWithAddressChange($customer, $billingAddress, $shippingAddress);
    }

    /**
     * Checking product exist in Quote and return qty
     *
     * @param int $productId
     * @param string $locationCode
     * @return float
     */
    public function hasProductQty($productId, $locationCode)
    {
        foreach ($this->getAllItems() as $item) {
            if (($item->getProductId() == $productId) && $item->getEccLocationCode() == $locationCode) {
                return $item->getQty();
            }
        }
        return false;
    }

    /**
     * Trigger collect totals after loading, if required
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        // collect totals and save me, if required
        if (1 == $this->getTriggerRecollect()) {
            $this->collectTotals()
                ->setTriggerRecollect(0)
                ->save();
        }
        return parent::_afterLoad();
    }

    public function isSalesRep()
    {
        if (null === $this->_isSalesRep) {
            $this->_isSalesRep = $this->commHelper->getCustomer()->isSalesRep();
        }
        return $this->_isSalesRep;
    }

    public function canDisplayCartContracts()
    {
        if (null === $this->_canDisplayCartContracts) {
            $this->_canDisplayCartContracts = $this->listsFrontendContractHelper->canDisplayCartContracts();
        }
        return $this->_canDisplayCartContracts;
    }

    public function updateItem($itemId, $buyRequest, $params = null)
    {
        $item = $this->getItemById($itemId);
        if (!$item) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('This is the wrong quote item id to update configuration.')
            );
        }
        $productId = $item->getProduct()->getId();

        //We need to create new clear product instance with same $productId
        //to set new option values from $buyRequest
        $product = clone $this->productRepository->getById($productId, false, $this->getStore()->getId());

        if (!$params) {
            $params = new \Magento\Framework\DataObject();
        } elseif (is_array($params)) {
            $params = new \Magento\Framework\DataObject($params);
        }
        $params->setCurrentConfig($item->getBuyRequest());
        $buyRequest = $this->_catalogProduct->addParamsToBuyRequest($buyRequest, $params);

        $buyRequest->setResetCount(true);
        if ($this->isTypeSkipAddProduct($item)) {
            $resultItem = $item;
        } else {
            $resultItem = $this->addProduct($product, $buyRequest);
        }

        if (is_string($resultItem)) {
            throw new \Magento\Framework\Exception\LocalizedException(__($resultItem));
        }

        if ($resultItem->getParentItem()) {
            $resultItem = $resultItem->getParentItem();
        }

        if ($resultItem->getId() != $itemId) {
            /**
             * Product configuration didn't stick to original quote item
             * It either has same configuration as some other quote item's product or completely new configuration
             */
            $this->removeItem($itemId);
            $items = $this->getAllItems();
            foreach ($items as $item) {
                if ($item->getProductId() == $productId && $item->getId() != $resultItem->getId()) {
                    if ($resultItem->compare($item)) {
                        // Product configuration is same as in other quote item
                        $resultItem->setQty($resultItem->getQty() + $item->getQty());
                        $this->removeItem($item->getId());
                        break;
                    }
                }
            }
        } else {
            $resultItem->setQty($buyRequest->getQty());
        }

        return $resultItem;
    }

    /**
     * @return bool
     */
    private function isUpdateWithLocationsEnabled()
    {
        $route = $this->request->getFullActionName();
        return $route === 'checkout_cart_updateItemOptions' && $this->locationsHelper->isLocationsEnabled();
    }

    /**
     * @param $item
     * @return \Magento\Catalog\Model\Product
     */
    private function getItemProduct($item)
    {
        if ($item instanceof QuoteItem) {
            return $item->getProduct();
        }
    }

    /**
     * @param $item
     * @return mixed
     */
    private function getEccConfigurator($item)
    {
        $product = $this->getItemProduct($item);
        if ($product instanceof Product) {
            return $product->getEccConfigurator();
        }
    }

    /**
     * @param $item
     * @return bool
     */
    private function isTypeSkipAddProduct($item)
    {
        if (!$item instanceof QuoteItem) {
            return false;
        }
        return $this->getEccConfigurator($item)
            || $item->getProductType() === TypeBundle::TYPE_CODE
            || $item->getProductType() === TypeConfigurable::TYPE_CODE
            || $this->isUpdateWithLocationsEnabled();
    }
}
