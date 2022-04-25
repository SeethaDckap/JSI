<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\Sales\Quote\Address\Total;


/**
 * MSQ Total Model
 *
 * Used to determine if an MSQ needs to be sent for the cart
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 *
 */
class Msq extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{

    /**
     * Allowed urls for sending MSQ.
     */
    const allowedUrls = ['SaveLocationQuote', 'removebranchpickup', 'removeitemsincart'];

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Epicor\Comm\Helper\Configurator
     */
    protected $commConfiguratorHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Comm\Model\Message\Request\MsqFactory
     */
    protected $commMessageRequestMsqFactory;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Product
     */
    protected $listsFrontendProductHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;
    /**
     * @var \Magento\CatalogInventory\Model\Spi\StockStateProviderInterface
     */
    protected $_stockStateProvider;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $catalogInventoryApiStockRegistryInterface;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Quote\Api\CartItemRepositoryInterface
     */
    protected $quoteItemRepository;

    protected $productMetadata;

    /**
     * @var string
     */
    protected $stockVisibility;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Comm\Helper\Configurator $commConfiguratorHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Model\Message\Request\MsqFactory $commMessageRequestMsqFactory,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        \Epicor\Lists\Helper\Frontend\Product $listsFrontendProductHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\CatalogInventory\Model\Spi\StockStateProviderInterface $stockStateProvider,
        \Magento\CatalogInventory\Api\StockRegistryInterface $catalogInventoryApiStockRegistryInterface,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Quote\Api\CartItemRepositoryInterface $quoteItemRepository
    )
    {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->request = $request;
        $this->commConfiguratorHelper = $commConfiguratorHelper;
        $this->registry = $registry;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->customerSession = $customerSession;
        $this->commMessageRequestMsqFactory = $commMessageRequestMsqFactory;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->eventManager = $eventManager;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->listsFrontendProductHelper = $listsFrontendProductHelper;
        $this->storeManager = $storeManager;
        $this->directoryHelper = $directoryHelper;
        $this->messageManager = $messageManager;
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->_stockStateProvider = $stockStateProvider;
        $this->catalogInventoryApiStockRegistryInterface = $catalogInventoryApiStockRegistryInterface;
        $this->checkoutSession = $checkoutSession;
        $this->commLocationsHelper = $commLocationsHelper;
        $this->quoteRepository = $quoteRepository;
        $this->quoteItemRepository = $quoteItemRepository;
        $this->productMetadata = $productMetadata;
        $this->stockVisibility = $this->commLocationsHelper->getStockVisibilityFlag();
        $this->setCode('msq');
    }

    /**
     * Collect method required to conform for Magento
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total)
    {
        if ($shippingAssignment->getShipping()->getAddress()->getAddressType() == 'shipping') {
            parent::collect($quote, $shippingAssignment, $total);

            if (!$quote->getIsM2eProQuote()) {
                $this->sendMsqForCart($quote, $shippingAssignment->getShipping()->getAddress());
            }
        }
    }

    /**
     * Works out if an MSQ needs to  be sent for the cart
     *
     * @param \Epicor\Comm\Model\Quote $quote
     * @param \Epicor\Comm\Model\Quote\Address $address
     *
     * @return $this
     */
    protected function sendMsqForCart($quote, $address)
    {
        if ($this->registry->registry('processed_items_after_msq') || $this->registry->registry('msq_sent')) {
            return $this;
        }
        /* @var $quote \Epicor\Comm\Model\Quote */
        $module = $this->request->getModuleName();
        $controller = $this->request->getControllerName();
        $action = $this->request->getActionName();
        $route = $this->request->getRouteName();
        // run an MSQ first to make sure we get the right prices

        $helper = $this->commConfiguratorHelper;
        /* @var $helper \Epicor\Comm\Helper\Configurator */

        if (
            !$this->registry->registry('bsv-processing') &&
            !($route == 'checkout' && $action == 'index') &&
            ($module != 'multishipping' || ($module == 'multishipping' && $controller == 'checkout' && $action == 'addressesPost')) &&
            !($quote->getEccQuoteId())
        ) {

            $helper->removeUnlicensedConfiguratorProducts($quote, false);

            $items = $this->getMsqItems($quote, $address);
            if ($this->registry->registry('csv_quickpad_send_msq')) {
                $skuslimit = $this->scopeConfig->getValue('epicor_comm_enabled_messages/msq_request/quickpad_max_sku_in_msq', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $chunkproducts = array_chunk($items, $skuslimit);
                foreach ($chunkproducts as $chunkitems) {
                    $this->_sendMsqForBsvItems($quote, $chunkitems);
                    //$address = $this->resetAddressData($address);
                }
            } else {
                $this->_sendMsqForBsvItems($quote, $items);
                //$address = $this->resetAddressData($address);
            }
        }

        $cartItems = $this->_getCartItems($quote, $address);
        $session = $this->customerSessionFactory->create();
        if ($quote->getIsMultiShipping()) {
            $reg = $session->getCartMsqRegistry();
            //When we are using a operand It should be array.
            $reg += is_array($cartItems) ? $cartItems : array();
            $session->setCartMsqRegistry($reg);
        } else {
            $session->setCartMsqRegistry($cartItems);
        }

        return $this;
    }

    /**
     * Works out which items need an MSQ to be sent
     *
     * @param \Epicor\Comm\Model\Quote $quote
     * @param \Epicor\Comm\Model\Quote\Address $address
     *
     * @return array
     */
    protected function getMsqItems($quote, $address = null)
    {
        $items = array();

        if (!$quote->getIsMultiShipping()) {
            $this->registry->unregister('QuantityValidatorObserver');
            $this->registry->register('QuantityValidatorObserver', 1);
            $cartItems = $quote->getAllVisibleItems();
            $this->registry->unregister('QuantityValidatorObserver');
        } else {
            $cartItems = $address->getAllItems();
        }

        foreach ($cartItems as $item) {
            if ($this->doesItemNeedMsq($item)) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * Checks to see if an Item has needs to have an MSQ sent
     *
     * @param \Epicor\Comm\Model\Quote\Item $item
     *
     * @return boolean
     */
    protected function doesItemNeedMsq($item)
    {
        if (
            $item->isDeleted() == false &&
            $item->getParentItemId() == null &&
            ($item->getParentId() == null || $this->getPromotions()) &&
            ($this->itemSessionChanged($item) || $this->allowedPages())
        ) {
            return true;
        }

        return false;
    }

    /**
     *  Checks to see if an Item has changed from it's session value
     *
     * @param \Epicor\Comm\Model\Quote\Item $item
     *
     * @return boolean
     */
    protected function itemSessionChanged($item)
    {
        $sessionItems = $this->customerSessionFactory->create()->getCartMsqRegistry();
        if (isset($sessionItems[$item->getId()]) == false) {
            return true;
        }

        $sessionItem = $sessionItems[$item->getId()];
        if (
            $sessionItem['qty'] != $item->getQty() ||
            $sessionItem['contract'] != $item->getEccContractCode() || $this->customerSession->getQuoteHasError()
        ) {
            return true;
        }

        return false;
    }

    /**
     * Sends an MSQ for the provided quote, used just before BSV
     *
     * @param \Epicor\Comm\Model\Quote $quote - quote the BSV is for
     * @param array $items - array of items
     */
    protected function _sendMsqForBsvItems(&$quote, &$items)
    {
        // to fix the issue of  at the time of logged it  took default  in MSQ
        if ($this->registry->registry('after_login_msq_init')) {
            $this->registry->unregister('after_login_msq');
            $this->registry->register('after_login_msq', 1);
            $this->registry->unregister('after_login_msq_init');
        }

        $msq = $this->commMessageRequestMsqFactory->create();
        /* @var $msq \Epicor\Comm\Model\Message\Request\Msq */

        $msqSuccessful = false;

        $basketItemsCache = $this->customerSession->getBasketItemsMsqData();

        if (!$this->registry->registry('SkipEvent') && count($items) > 0 && !$this->registry->registry('msq-processing')) {

            $this->registry->register('msq-processing', true);
            $this->registry->register('bsv-processing', true);

            if ($msq->isActive()) {
                $msq->setAllowPriceRules(false);
                $msq->setForceMsqPrices(true);
            }


            $products = [];
            $qtys = [];
            $msqlocations = [];
            $showLocations = $this->commLocationsHelper->isLocationsEnabled();

            foreach ($items as $x => $item) {
                /* @var $item \Magento\Quote\Model\Quote\Item */

                $attributes = $this->getItemAttributes($item);
                $tmpProduct = $this->catalogProductFactory->create();
                $tmpProduct->setData($item->getProduct()->getData());
                $tmpProduct->setMsqAttributes($attributes);

                $this->setProductMsqContract($item, $tmpProduct);

                $item->setProduct($tmpProduct);

                if ($option = $item->getOptionByCode('simple_product')) {
                    $products[$x] = $option->getProduct();
                } else {
                    $products[$x] = $item->getProduct();
                }
                if ($showLocations && in_array($this->stockVisibility, ['all_source_locations'])) {
                    $msqlocations = array_keys($item->getProduct()->getCustomerLocations());
                } else {
                    $locationcode = $item->getEccLocationCode();
                    if ($locationcode) {
                        $msqlocations[] = $locationcode;
                    }
                }
                $qtys[$x] = $item->getQty();
            }

            $transportObject = $this->dataObjectFactory->create();
            $transportObject->setProducts($products);
            $transportObject->setMessage($msq);
            $this->eventManager->dispatch('msq_sendrequest_before', array(
                'data_object' => $transportObject,
                'message' => $msq,
                'quote'   => $quote
            ));
            $products = $transportObject->getProducts();

            if ($msq->isActive()) {
                if ($showLocations && !empty($msqlocations)) {
                    $msq->addLocations($msqlocations);
                }
                foreach ($products as $id => $product) {
                    $msq->addProduct($product, $qtys[$id]);
                }
                //Rounding must be done in the msq before the BSV
                $msq->setPreventRounding(true);
                $msqSuccessful = $msq->sendMessage();
            }

            $this->eventManager->dispatch('msq_sendrequest_after', array(
                'data_object' => $transportObject,
                'message' => $msq,
            ));
            $this->registry->unregister('msq_sent');
            $this->registry->register('msq_sent', true);
            $this->resetAddressData($quote->getShippingAddress());

            //In 2.3.1, lot of changes are made on quote repository level
            //Also sales_quote_save_after was changed
            if ($this->productMetadata->getVersion() > '2.3.0') {
                $this->registry->unregister('QuantityValidatorObserver');
                $this->registry->register('QuantityValidatorObserver', 1);
                $quote->save();
                $this->registry->unregister('QuantityValidatorObserver');
            } else {
                $quote->save();
            }

            if (!$quote->getEccQuoteId()) {
                $this->processItemsAfterMsq($quote, $items, $msqSuccessful);
                $this->registry->unregister('processed_items_after_msq');
                $this->registry->register('processed_items_after_msq', true);
            }

            $this->registry->unregister('msq-processing');
            $this->registry->unregister('bsv-processing');
            $this->checkoutSession->unsetData('bsv_sent_for_cart_page');
        }

        $this->processItemsAfterMsq($quote, $items, $msqSuccessful, true);

        return $msqSuccessful;
    }

    /**
     * Sets data on the product model for contracts
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param \Epicor\Comm\Model\Product $tmpProduct
     *
     * @retrun void
     */
    protected function setProductMsqContract($item, $tmpProduct)
    {
        $contracts = array();

        $contractHelper = $this->listsFrontendContractHelper;
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */

        $eccSelectedContract = $contractHelper->getSelectedContractCode();

        if (
            $contractHelper->contractsDisabled() ||
            $eccSelectedContract
        ) {
            return;
        }

        if ($item->getEccContractCode()) {
            $contracts[] = $item->getEccContractCode();
        } else {
            $productHelper = $this->listsFrontendProductHelper;
            /* @var $productHelper Epicor_Lists_Helper_Frontend_Product */
            $lineSettings = $contractHelper->getLineContractSettings();
            $addContracts = true;
            $productContracts = $productHelper->getContractsForProduct($tmpProduct->getId());

            $requiredType = $contractHelper->requiredContractType();

            $lineFilterAll = ($lineSettings['enabled'] && $lineSettings['line_selection'] == 'all');
            $lineAlways = ($lineSettings['enabled'] && $lineSettings['line_always']);

            if (
                (count($productContracts) > 1 && $lineFilterAll) ||
                (count($productContracts) == 1 && $requiredType == 'O' && $lineAlways == false)
            ) {
                $addContracts = false;
            }

            if ($addContracts) {
                foreach ($productContracts as $contract) {
                    /* @var $contract Epicor_Lists_Model_ListModel */
                    $contracts[] = $contract->getErpCode();
                }
            }
        }

        $tmpProduct->setEccContracts($contracts);
    }

    /**
     * Processes times after an MSQ has/hasnt been called
     *
     * resets BSV values and reset price
     *
     * Also sets the epicor original price on the item if the relevant param is passed
     *
     * @param \Epicor\Comm\Model\Quote $quote
     * @param array $items
     * @param boolean $msqSuccessful
     * @param boolean $setOriginalPrice
     */
    protected function processItemsAfterMsq(&$quote, &$items, $msqSuccessful, $setOriginalPrice = false)
    {
        $basketItemsCache = $this->customerSession->getBasketItemsMsqData();
        $contractHelper = $this->listsFrontendContractHelper;
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */
        $selectedContract = $contractHelper->getSelectedContractCode();

        $contractsOptional = $contractHelper->requiredContractType() == 'O';

        if ($selectedContract) {
            $quote->setEccContractCode($selectedContract);
        }

        $this->processItems($quote, $items, $msqSuccessful, $setOriginalPrice);

        $this->registry->unregister('cart_merged');
        $this->customerSession->setBasketItemsMsqData($basketItemsCache);
    }

    /**
     * Gets a message related to stock for backordering
     *
     * @param \Magento\CatalogInventory\Model\Stock\Item $stockItem
     * @param \Epicor\Comm\Model\Product $product
     * @param string $message
     * @param string $locationName
     *
     * @return string
     */
    protected function _updateStockMessage($stockItem,  $product, $message, $locationName = "")
    {
        $checkMessage = __('We don\'t have as many "%1" as you requested.', $stockItem->getProductName());
        if ($message == $checkMessage) {
            $availableQty = $stockItem->getQty() - $stockItem->getMinQty();
            if ($availableQty > 0) {
                $locMsg = '';
                $isDiscontinued = '';
                if ($locationName) {
                    $locMsg = " for %3";
                }
                if ($product->getIsEccDiscontinued()) {
                    $isDiscontinued = ' as this will be discontinued';
                }
                $productName = $stockItem->getProductName() ?: $product->getName();
                $message = __(
                    'We don\'t have as many "%1" as you requested' . $isDiscontinued . '. Only %2 available' . $locMsg . '.',
                    $productName,
                    $availableQty,
                    $locationName
                );
            }
        }

        return $message;
    }

    /**
     * Gets cart items in simple format
     *
     * @param \Epicor\Comm\Model\Quote $quote
     * @param \Epicor\Comm\Model\Quote\Address $address
     *
     * @return array
     */
    protected function _getCartItems($quote)
    {
        $items = [];

        foreach ($quote->getAllItems() as $item) {
            /* @var $item \Magento\Quote\Model\Quote\Item */
            $items[$item->getId()] = array(
                'sku' => $item->getSku(),
                'qty' => $item->getQty(),
                'contract' => $item->getEccContractCode(),
            );
        }

        return $items;
    }

    /**
     * Resets the address data
     *
     * @param $address
     * @return $address
     */
    protected function resetAddressData($address)
    {
        $address->unsetData('cached_items_all');
        $address->unsetData('cached_items_nominal');
        $address->unsetData('cached_items_nonnominal');
        $address->getAllItems();

        $address->setEccBsvGoodsTotal(null);
        $address->setEccBsvGoodsTotalInc(null);
        $address->setEccBsvCarriageAmount(null);
        $address->setEccBsvCarriageAmountInc(null);
        $address->setEccBsvGrandTotal(null);
        $address->setEccBsvGrandTotalInc(null);
        return $address;
    }

    /**
     *  Get Quote Item attributes
     *
     * @param $item
     * @return array
     */
    protected function getItemAttributes($item)
    {
        $helper = $this->commMessagingHelper;
        /* @var $helper \Epicor\Comm\Helper\Messaging */
        $productOptions = $helper->getItemProductOptions($item);
        $attributes = [];
        if (!empty($productOptions) && !empty($productOptions['options'])) {
            if (is_array($productOptions['options'])) {
                foreach ($productOptions['options'] as $option) {
                    if (!in_array($option['option_type'], array(
                        'ewa_description',
                        'ewa_title',
                        'ewa_short_description',
                        'ewa_sku'
                    ))) {
                        $label = $option['option_type'] == 'ewa_code' ? 'Ewa Code' : $option['label'];
                        $attributes[$label] = $option['value'];
                    }
                }
            }
        } else if (isset($productOptions['info_buyRequest']['options']['ewa_code'])) {
            $label = 'Ewa Code';
            $attributes[$label] = $productOptions['info_buyRequest']['options']['ewa_code'];
        }
        return $attributes;
    }

    /**
     * Process Quote Items
     * @param $quote
     * @param $items
     * @param $msqSuccessful
     * @param $setOriginalPrice
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function processItems($quote, $items, $msqSuccessful, $setOriginalPrice)
    {
        $_fromCurr = $quote->getBaseCurrencyCode() ?: $this->storeManager->getStore()->getBaseCurrencyCode();
        $_toCurr = $this->storeManager->getStore()->getCurrentCurrencyCode();
        $basketItemsCache = $this->customerSession->getBasketItemsMsqData();
        $contractHelper = $this->listsFrontendContractHelper;
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */
        $linesCheckLogin = $contractHelper->linesCheckExistingProducts();
        if ($this->productMetadata->getVersion() > '2.3.0') {
            $greaterVersion = true;
        } else {
            $greaterVersion = false;
        }
        $successMessages = [];
        foreach ($items as $x => $item) {
            /* @var $item \Magento\Quote\Model\Quote\Item */
            $item->setEccBsvPrice(null);
            $item->setEccBsvPriceInc(null);
            $item->setEccBsvLineValue(null);
            $item->setEccBsvLineValueInc(null);

            /**
             * For configurable product item get product should give null value
             * so getting MSQ ECC set product data Should Require to use get Option By Code
             */
            if ($option = $item->getOptionByCode('simple_product')) {
                $product = $option->getProduct();
            } else {
                $product = $item->getProduct();
            }
            //$product = $item->getProduct();
            $item->setEccMsqBasePrice($product->getEccMsqBasePrice());
            //catalog rule was applied and the action is "To fixed"
            $item->setHigherDiscount($product->getHigherDiscount());
            $item->setPromotionalAmount($product->getPromotionalAmount()); //promotional fixed amount
            $item->setOrdinaryCustomerAmount($product->getOrdinaryCustomerAmount()); //customer price

            if ($msqSuccessful) {
                $basketItemsCache[$item->getId()] = $product->debug();
            } else {
                if (isset($basketItemsCache[$item->getId()])) {
                    $product = $this->catalogProductFactory->create()->addData($basketItemsCache[$item->getId()]);
                }
            }
            //If there is price list rule applied then
            //Ignore location price
            $ignoreLocation = false;
            if ($product->getDiscountApplied() || $product->getPriceListApplied()) {
                $ignoreLocation = true;
            }

            //This was a patch given by Gareth for setting the location price
            //Previously this patch was not applied because bistrack was the one which supports multiple location price concept
            if ($item->getEccLocationCode() && (!$ignoreLocation)) {
                $staticLocationCode = (string)$item->getEccLocationCode();
                $product->setToLocationPrices($staticLocationCode);
                $product->setEccOriginalPrice(false);
                $product->unsFinalPrice();
                $product->setStaticLocationPrice(true);
                $item->setEccOriginalPrice($product->getEccOriginalPrice() ?: $product->calculateEpicorOriginalPrice($item->getQty()));
            } else {
                if (($greaterVersion && ($product->getDiscountApplied() || $product->getPriceListApplied())) || $this->registry->registry('cart_merged')) {
                    $item->setEccOriginalPrice($product->getEccOriginalPrice() ?: $product->calculateEpicorOriginalPrice($item->getQty()));
                }
            }
            if (!$quote->getEccQuoteId() && $contractHelper->contractsEnabled()) {
                $contractHelper->lineContractCheck($quote, $product, $item, $linesCheckLogin);
            }

            if ($setOriginalPrice) {
                /* @var $product Epicor_Comm_Model_Product */
                $item->setEccOriginalPrice($product->getEccOriginalPrice() ?: $product->calculateEpicorOriginalPrice($item->getQty()));
            } else if (!$quote->getEccQuoteId()) {
                $price = $product->getFinalPrice($item->getQty());
                $customPrice = $this->directoryHelper->currencyConvert($price, $_fromCurr, $_toCurr);
                $item->setCustomPrice($customPrice);
                $item->setOriginalCustomPrice($customPrice);
                $item->getProduct()->setIsSuperMode(true);
            }

            if ($contractHelper->contractsEnabled()) {
                $contractCode = $quote->getEccContractCode() ?: $item->getEccContractCode();

                $contractData = $item->getProduct()->getEccMsqContractData();

                if ($contractCode && $contractData) {
                    $contractQty = $this->getItemContractQty($contractCode, $contractData);

                    if ($contractQty > -1 && $item->getQty() > $contractQty) {
                        $message = __('The requested quantity for "%1" is not available. Only %2 available on the selected contract', $item->getProduct()->getName(), $contractQty);
                        $item = $this->processContractForItem($quote, $item, $message);
                        if (!$this->registry->registry('processed_items_after_msq')) {
                            $this->messageManager->addError($message);
                            continue;
                        }
                    }
                }
            }

            $msqAlwaysInStock = $this->scopeConfig->getValue('epicor_comm_enabled_messages/msq_request/products_always_in_stock', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            /**
             * First check discontinued is true then return false else
             * second return msq config always in stock
             */
            $alwaysInStock = $product->getIsEccDiscontinued() ? false : $msqAlwaysInStock; // discontinued item

            /**
             * First check non-stock is true then return true else
             * second return parent var alwaysInStock
             */
            $alwaysInStock = $product->getIsEccNonStock() ?: $alwaysInStock; // non-stock item
            if (!$alwaysInStock) {
                $item = $this->processStocksForItems($quote, $item, $product);
            } else {
                $item = $this->processStocksData($quote, $item, $product);
            }

            //Added to remove out of stock products from cart
            $remove = $this->registry->registry('hide_out_of_stock_product');
            if ($remove && in_array($item->getProductId(), $remove)) {
                $item = $this->processOutOfStockItems($quote, $item);
            }
            // needed for other modules to ensure discounts get calculated before bsv (ahem, amasty)
            if (!$quote->getIsMultiShipping()) {
                if($setOriginalPrice) {
                    $item->save();
                }
                if (!$quote->getItemsCollection()->getItemById($item->getId())) {
                    foreach ($quote->getItemsCollection()->getItems() as $id => $it) {
                        if ($it->getId() == $item->getId()) {
                            $quote->getItemsCollection()->removeItemByKey($id);
                        }
                    }
                    $quote->getItemsCollection()->addItem($item);
                }
                if ($setOriginalPrice && $greaterVersion) {
                    $quote->setTriggerRecollect(1);
                }
            }
            $ismultiplePId = $this->registry->registry('add_multiple_to_cart') ?:[];
            if(!$item->getErrorInfos() && $item->hasDataChanges() && array_key_exists($product->getId(), $ismultiplePId) ) {
                $options = $item->getOptionByCode("product_type");
                if ($options && $options->getValue() == "grouped") { //For Group product display parent name for message
                    $productName =  $options->getProduct() ? $options->getProduct()->getName() : $product->getName();
                    $productId = $options->getProduct() ? $options->getProduct()->getId() : $product->getId();
                    $successMessages[$productId] = __('%1 was successfully added to your shopping cart.', $productName);
                } elseif($item->getProduct()->getTypeId() == 'configurable') {
                    $productName =  $item->getProduct()->getName();
                    $successMessages[$item->getProduct()->getId()] = __('%1 was successfully added to your shopping cart.', $productName);
                } else {
                    $productName =  $product->getName();
                    $successMessages[$product->getId()] = __('%1 was successfully added to your shopping cart.', $productName);
                }
                //$this->messageManager->addSuccessMessage($message);
            }
        }

        if($successMessages) {
            foreach($successMessages as $message){
                $this->messageManager->addSuccessMessage($message);
            }
        }
        return;
    }

    /**
     * Gets the Quote Item contract qty
     * @param $contractCode
     * @param $contractData
     * @return int
     */
    protected function getItemContractQty($contractCode, $contractData)
    {
        $contractQty = -1;

        foreach ($contractData as $contract) {
            if (!($contract instanceof \Magento\Framework\DataObject)) {
                if (!empty($contract) && $contract['contractCode'] == $contractCode) {
                    $maxQty = $contract['maximumContractQty'];
                    if ($maxQty === "0" || $maxQty === 0 || $maxQty) {
                        $contractQty = $contract['maximumContractQty'];
                    }
                }
            } else {
                if ($contract && $contract->getContractCode() == $contractCode) {
                    $maxQty = $contract->getMaximumContractQty();
                    if ($maxQty === "0" || $maxQty === 0 || $maxQty) {
                        $contractQty = $contract->getMaximumContractQty();
                    }
                }
            }
        }
        return $contractQty;
    }

    /**
     * Process Contracts for items
     * @param $quote
     * @param $item
     * @param $message
     * @return mixed
     */
    protected function processContractForItem($quote, $item, $message)
    {
        $item->setData($item->getOrigData());
        $item->setMessage($message);
        $item->setHasError(1);
        if ($item->isObjectNew()) {
            $item->setQuote($quote);
            $item->isDeleted(true);
            if ($item->getHasChildren()) {
                foreach ($item->getChildren() as $child) {
                    $child->isDeleted(true);
                }
            }

            $parent = $item->getParentItem();
            if ($parent) {
                $parent->isDeleted(true);
            }
            $item->delete();
        }
        return $item;
    }

    protected function processStocksForItems($quote, $item, $product)
    {
        $stockItem = $this->catalogInventoryApiStockRegistryInterface->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        /* @var $stockItem \Magento\CatalogInventory\Model\Stock\Item */
        $stockItem->setOrderedItems(0);
        $stockItem->setManageStock(1);
        $stockItem->setIsInStock(1);
        $stockQty = $stockItem->getQty();

        /**
         * Use location level free-stock/Qty
         * instead of msq parts free-stock
         */
        $showLocations = $this->commLocationsHelper->isLocationsEnabled();
        $locationName = "";
        if ($showLocations) {
            $qty = $this->registry->registry('aggregate_stock_levels_' . $item->getSku());
            if (in_array($this->stockVisibility, ['all_source_locations'])) {
                $stockQty = $product->getStockLevel();
            } else if (isset($item["ecc_location_code"]) && isset($qty[$item["ecc_location_code"]])) {
                $locationName = $item["ecc_location_name"];
                $stockQty     = $qty[$item["ecc_location_code"]];
            }
        }
        $decimalPlaces = $product->getDecimalPlaces();
        $roundedQty    = $this->commMessagingHelper->qtyRounding($stockQty, $decimalPlaces);
        $stockItem->setQty($roundedQty);

        //set stock discontinued flag for checking stock
        $isDiscontinued = $product->getIsEccDiscontinued() ?: 0;

        $stockItem->setData("is_ecc_discontinued", $isDiscontinued);
        if ($stockItem->getQty() <= 0) {
            $stockItem->setIsInStock(0);
        } else {
            $stockItem->setIsInStock(1);
        }
        $check = $this->_stockStateProvider->checkQuoteItemQty($stockItem, $item->getQty(), $item->getQty());
        $message = $this->_updateStockMessage($stockItem, $product, $check->getMessage(), $locationName);
        if (is_null($item->getMessage())) {
            $item->setMessage($message);
        }
        $item->setHasError($check->getHasError());

        if ($check->getHasError()) {
            $item->setData($item->getOrigData());
            $item->setProduct($product);
            if ($item->isObjectNew() || $quote->getIsPunchout()) {
                $item->setQuote($quote);
                $item->isDeleted(true);
                if ($item->getHasChildren()) {
                    foreach ($item->getChildren() as $child) {
                        $child->isDeleted(true);
                    }
                }

                $parent = $item->getParentItem();
                if ($parent) {
                    $parent->isDeleted(true);
                }
                $item->delete();
            }
            if (!$quote->getEccPunchoutConnectionId()) {
                $quote->setHasError(1)->save();
            }
            if (!$this->registry->registry('processed_items_after_msq')) {
                if ($message instanceof \Magento\Framework\Phrase) {
                    $message = $message->__toString();
                }
                $this->messageManager->addComplexErrorMessage(
                    'lineQtyError',
                    [
                        'product_id' => $product->getId(),
                        'message'    => $message
                    ]
                );
            }
        } else if ($check->getMessage()) {
            if (!$this->registry->registry('processed_items_after_msq')) {
                $this->messageManager->addWarning($message);
            }
        }
        return $item;
    }

    /**
     * @param $quote
     * @param $item
     * @param $product
     *
     * @return mixed
     */
    protected function processStocksData($quote, $item, $product)
    {
        $stockItem    = $this->catalogInventoryApiStockRegistryInterface->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        $messageCheck = $this->_stockStateProvider->checkQuoteItemQty($stockItem, $item->getQty(), $item->getQty());
        if ($messageCheck->getHasError()) {
            $item->setMessage($messageCheck->getMessage());
            $item->setHasError(1);
            $item->setData($item->getOrigData());
            $this->handleQuoteItem($item, $quote);
            if (!$quote->getEccPunchoutConnectionId()) {
                $quote->setHasError(1)->save();
            }
            if (!$this->registry->registry('processed_items_after_msq')) {
                $this->messageManager->addError($messageCheck->getMessage());
            }
        }
        return $item;

    }
    /**
     * @param $item
     * @param $quote
     */
    protected function handleQuoteItem($item, $quote)
    {
        if ($item->isObjectNew() || $quote->getIsPunchout()) {
            $item->setQuote($quote);
            $item->isDeleted(true);
            if ($item->getHasChildren()) {
                foreach ($item->getChildren() as $child) {
                    $child->isDeleted(true);
                }
            }

            $parent = $item->getParentItem();
            if ($parent) {
                $parent->isDeleted(true);
            }
            $item->delete();
        }
    }
    protected function processOutOfStockItems($quote, $item)
    {
        $canDelete = 0;
        $message = __('Product "%1" currently out of stock.', $item->getProduct()->getName());
        if (!$item->isObjectNew()) {
            $canDelete = 1;
            $message = __('Product "%1" currently out of stock and has been removed from the cart.', $item->getProduct()->getName());
        }
        $item->setData($item->getOrigData());
        $item->setMessage($message);
        $item->setHasError(1);
        if ($item->isObjectNew() || $canDelete) {
            $item->setQuote($quote);
            $item->isDeleted(true);
            if ($item->getHasChildren()) {
                foreach ($item->getChildren() as $child) {
                    $child->isDeleted(true);
                }
            }

            $parent = $item->getParentItem();
            if ($parent) {
                $parent->isDeleted(true);
            }
            $item->delete();
            $quote->setHasError(1);
        }
        $quote->setHasError(1)->save();
        $this->checkoutSession->getMessages(true);
        $this->customerSession->setQuoteHasError(1);

        if (!$this->registry->registry('processed_items_after_msq')) {
            $this->messageManager->addError($message);
        }
        return $item;
    }


    /**
     * Allowed pages for sending MSQ.
     *
     * @return boolean
     */
    public function allowedPages()
    {
        $needMsq           = false;
        $controller = $this->request->getControllerName();
        $action     = $this->request->getActionName();
        if ($controller === 'pickup' &&
            in_array($action, self::allowedUrls))
        {
            $needMsq = true;
        }

        return $needMsq;

    }//end allowedPages()


}
