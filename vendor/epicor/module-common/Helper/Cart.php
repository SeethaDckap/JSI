<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Helper;


use Mage;
use Magento\Store\Model\ScopeInterface;

/**
 * Cart helper
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Cart extends \Epicor\Common\Helper\Data
{
    const REORDER_OPTION = 'sales/reorder/cart_merge_action';
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Epicor\Comm\Helper\Configurator
     */
    protected $commConfiguratorHelper;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $salesOrderItemFactory;

    /**
     * @var \Magento\Quote\Model\Quote\ItemFactory
     */
    protected $quoteQuoteItemFactory;

    /**
     * @var \Magento\Bundle\Model\OptionFactory
     */
    protected $bundleOptionFactory;

    /**
     * @var \Magento\Bundle\Model\SelectionFactory
     */
    protected $bundleSelectionFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Epicor\BranchPickup\Helper\Data
     */
    protected $branchPickupHelper;

    /**
     * @return \Magento\Framework\App\RequestInterface
     */
    protected $request;

    protected $reorderOption;



    public function __construct(
        \Epicor\Common\Helper\Context $context,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Epicor\Comm\Helper\ConfiguratorFactory $commConfiguratorHelper,
        \Magento\Sales\Model\Order\ItemFactory $salesOrderItemFactory,
        \Magento\Quote\Model\Quote\ItemFactory $quoteQuoteItemFactory,
        \Magento\Bundle\Model\OptionFactory $bundleOptionFactory,
        \Magento\Bundle\Model\SelectionFactory $bundleSelectionFactory,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->catalogProductFactory = $catalogProductFactory;
        $this->commConfiguratorHelper = $commConfiguratorHelper;
        $this->salesOrderItemFactory = $salesOrderItemFactory;
        $this->quoteQuoteItemFactory = $quoteQuoteItemFactory;
        $this->bundleOptionFactory = $bundleOptionFactory;
        $this->bundleSelectionFactory = $bundleSelectionFactory;
        $this->messageManager = $context->getMessageManager();
        $this->commProductHelper = $commProductHelper;
        $this->commLocationsHelper = $context->getCommLocationsHelper();
        $this->branchPickupHelper = $branchPickupHelper;
        $this->request = $request;
        parent::__construct($context);
    }
    /**
     * Attempts to build a cart for a reorder
     *
     * @param \Epicor\Comm\Model\Xmlvarien $order
     *
     * @return boolean
     */
    public function processReorder($order)
    {

        try {

            $processed = false;
            $allowProcess = true;
            $productErrors = array();
            $products = array();
            $superGroupConfig = array();
            $bundleOptions = array();
            // process lines to make sure we have some products to add
            $lines = $order->getVarienDataArrayFromPath('lines/line');
            $allowReorderZeroQtyLines = $this->scopeConfig->isSetFlag('customerconnect/crq_options/allow_reorder_zero_qty_lines', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $displayedLocations = $this->commLocationsHelper->getCustomerDisplayLocationCodes();
            $messages = [];
            if (!empty($lines)) {
                foreach ($lines as $line) {
                    /* @var $line \Epicor\Common\Model\Xmlvarien */

                    $qtyOrdered = $line->getQuantity()->getOrdered();
                    $qtyFloat = (float) $qtyOrdered;
                    if ((empty($qtyFloat)
                            && !$allowReorderZeroQtyLines)
                        || strtoupper($line->getIsKit()) == 'C'
                    ) {
                        continue;
                    }

                    $productCode = $line->getProductCode();
                    // check if product code is on global excluded list, if so ignore
                    if (!$this->isSkuGloballyExcluded($productCode)) {
                        $product = $this->catalogProductFactory->create();
                        $product->setStoreId($this->storeManager->getStore()->getId())
                            ->load($product->getIdBySku($productCode));
                        /* @var $product \Epicor\Comm\Model\Product */
                        $add = true;
                        $productUrl = $product->getProductUrl();
                        if (!$product || $product->isObjectNew() || !$product->isSaleable() || !$product->isReorderable()) {
                            $add = false;
                            $productErrors[] = $productCode;
                            $this->messageManager->addErrorMessage('Product ' . $productCode . ' could not be added to basket as it is not currently available');
                        } else {
                            if ($product->getTypeId() == 'grouped') {
                                $superGroupConfig = array('product_type' => 'grouped','product_id' => $product->getId());
                                $uomSeparator = $this->commMessagingHelper->getUOMSeparator();
                                $productCode .= $uomSeparator . $line->getUnitOfMeasureDescription();
                                $product = $this->catalogProductFactory->create();
                                $product->setStoreId($this->storeManager->getStore()->getId())
                                    ->load($product->getIdBySku($productCode));
                                $product->setIsGrouped(true);
                                if (!$product || $product->isObjectNew() || !$product->isSaleable() || !$product->isReorderable()) {
                                    $add = false;
                                    $productErrors[] = $productCode;
                                    $this->messageManager->addErrorMessage('Product ' . $productCode . ' could not be added to basket as it is not currently available');
                                }
                            }
                        }

                        if ($add) {
                            $locationCode = $line->getLocationCode();
                            $branchHelper = $this->branchPickupHelper;
                            if ($branchHelper->isBranchPickupAvailable() && $branchHelper->getSelectedBranch()) {
                                $locationCode = $branchHelper->getSelectedBranch();
                            }

                            if (!$this->commLocationsHelper->isLocationsEnabled()) {
                                $locationCode = null;
                            }

                            $products[] = array(
                                'product' => $product,
                                'qty' => $qtyOrdered,
                                'price' => $line->getPrice(),
                                'groupSequence' => $line->getGroupSequence(),
                                'location_code' => $locationCode,
                                'product_url' => $productUrl
                            );
                        }
                    }
                }

                if (!empty($productErrors)) {
                    if (count($productErrors) == count($lines)) {
                        $this->messageManager->addErrorMessage('Could not process reorder request as no lines are available to reorder');
                        $allowProcess = false;
                    }
                }

                if ($allowProcess) {


                    $helper = $this->commConfiguratorHelper->create();
                    /* @var $helper \Epicor\Comm\Helper\Configurator */

                    $cart = $this->checkoutCartFactory->create();
                    $quote = $this->checkoutSession->getQuote();
                    /* @var $quote \Epicor\Comm\Model\Quote */

                    $this->updateExistingCart($quote);
                    $quote->setEccQuoteId(null);
                    $quote->setEccIsDdaDate(false);
                    $quote->setAllowSaving(true);
                    $quote->save();

                    $this->registry->register('send_msq', true, true);

                    $cart->setQuote($quote);

                    foreach ($products as $line) {
                        if ($line['product']->getTypeId() == 'bundle') {
                            $bundleOptions = [];
                            $optionIds = $line['product']->getTypeInstance(true)->getOptionsIds($line['product']);
                            $selections = $line['product']->getTypeInstance(true)->getSelectionsCollection($optionIds, $line['product']);
                            foreach ($selections as $selection) {
                                if ($selection->getIsDefault()) {
                                    $bundleOptions[$selection->getOptionId()] = $selection->getSelectionId();
                                }
                            }
                            if (empty($bundleOptions)) {
                              $bundleOptions = array_combine($optionIds, $optionIds);
                            }
                        }
                        /* @var $product \Magento\Catalog\Model\Product */
                        if ($line['product']->getEccConfigurator()) {
                            $helper->reorderProduct($line['product']->getId(), $line['groupSequence'], $line['qty']);
                        } else {
                            try {
                                $locEnabled = $this->commLocationsHelper->isLocationsEnabled();
                                if ($locEnabled && isset($line['location_code'])) {
                                    $newQty = $this->commProductHelper->getCorrectOrderQty($line['product'], $line['qty'], $locEnabled, $line['location_code']);
                                    //Minimum and Maximum Qty check for product
                                    if ($newQty['qty'] != $line['qty']) {
                                        $line['qty'] = $newQty['qty'];
                                        $message = $newQty['message'];
                                        $this->messageManager->addSuccessMessage($message);
                                    }
                                }
                                if($bundleOptions){
                                    $options = array(
                                        'qty' => $line['qty'],
                                        'location_code' => $line['location_code'],
                                        'bundle_option' => $bundleOptions,
                                    );
                                }else if ($line['product']->getIsGrouped()) {
                                     $options = array(
                                        'qty' => $line['qty'],
                                        'location_code' => $line['location_code'],
                                        'super_product_config' => $superGroupConfig,
                                    );
                                }else {
                                    $options = array(
                                        'qty' => $line['qty'],
                                        'location_code' => $line['location_code'],
                                    );
                                }

                                $options = new \Magento\Framework\DataObject($options);
                                $quote->addOrUpdateLine($line['product'], $options);
                                if (!$quote->hasProductId($line['product']->getId())) {
                                    // Product is not in the shopping cart so
                                    // show an error
                                    throw new \Exception('Could not find product in basket');
                                } else {
                                    $messages[] = $line['product'];
                                }
                            } catch (\Exception $ex) {
                                $message = $ex->getMessage();
                                if (count($displayedLocations) > 1 && isset($line['product_url'])) {
                                    $this->messageManager->addComplexErrorMessage(
                                        'reorderErrorMessage',
                                        [
                                            'message' => $message,
                                            'product_url' => $line['product_url'],
                                        ]
                                    );
                                } else {
                                    $this->messageManager->addErrorMessage($message);
                                }
                            }
                        }
                    }
                    $cart->save();
                    $quote = $cart->getQuote();
                    if(!empty($messages)){
                        array_filter($messages, function($var)  use ($quote){
                            if($quote->hasProductId($var->getId())){
                                $this->messageManager->addSuccessMessage($var->getName(). __(' has been added to your cart'));
                            }
                        });
                    }
                    $this->checkoutSession->setCartWasUpdated(true);


                    $processed = ($quote->getItemsCount() > 0) ? true : false;
                }
            } else {
                $this->messageManager->addErrorMessage('Could not process reorder request as no lines are available to reorder');
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $processed;
    }

    public function isSkuGloballyExcluded($sku)
    {
        $currentStore = $this->storeManager->getStore()->getId();
        $localSkusNotReorderable = $this->scopeConfig->getValue('Epicor_Comm/epicor_sku/reorderable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $currentStore);
        $excludeLocalSkus = strpos($localSkusNotReorderable, $sku);
        $trueFalse = true;
        // if nothing at store level, get global level
        if ($excludeLocalSkus === false) {
            $globalSkusNotReorderable = $this->scopeConfig->getValue('Epicor_Comm/epicor_sku/reorderable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 0);
            $excludeGlobalSkus = strpos($globalSkusNotReorderable, $sku);
            // if not excluded globally return false
            if ($excludeGlobalSkus === false) {
                $trueFalse = false;
            }
        }
        return $trueFalse;
    }

    /**
     * Adds a product to a quote in a way that allows multiple lines per product
     *
     * @param \Epicor\Comm\Model\Quote $quote
     * @param \Epicor\Comm\Model\Product $product
     * @param integer $qty
     * @param float $price
     */

    public function processQuoteLine(&$quote, $product, $lineOptionsData)
    {

        $locHelper = $this->commLocationsHelper;
        /* @var $locHelper Epicor_Comm_Helper_Locations */

        $listHelper = $this->listsFrontendProductHelper;
        /* @var $listHelper Epicor_Lists_Helper_Frontend_Product */

        $listsEnabled = $listHelper->listsEnabled();
        $locationCode = false;

        if (is_numeric($lineOptionsData)) {
            $lineOptions = $this->dataObjectFactory->create(array('qty' => $lineOptionsData));
        } elseif (!$lineOptionsData instanceof \Magento\Framework\DataObject) {
            $lineOptions = $this->dataObjectFactory->create();
            $lineOptions->setData($lineOptionsData);
        } elseif ($lineOptionsData instanceof \Magento\Framework\DataObject) {
            $lineOptions = $lineOptionsData;
        }

        $request = $lineOptions->getRequest();

        $qty = $lineOptions->getQty() ? $lineOptions->getQty() : 1;

        if ($product->getIsObjectNew() || !$product->getId()) {
            throw new \Exception('Non-existent product added');
        }

        $options = $this->dataObjectFactory->create();
        if($lineOptions->getCustomOptions()){
            $lineoptionsData = ($lineOptions->getOptions() ? $lineOptions->getOptions() : []);
            $lineoptionsData = $lineoptionsData+$lineOptions->getCustomOptions();
            $lineOptions->setOptions($lineoptionsData);
        }

        $options->setData(
            [
                'product' => $product->getId(),
                'qty' => $qty,
                'options' => $lineOptions->getOptions() ?: ($lineOptions->getOptions() ?: array())
            ]
        );
        if ($product->getTypeId() == 'bundle' || $lineOptions->getBundleOption()) {
            if (!$lineOptions->getBundleOption()) {
                $options->setBundleOption($this->getBundleOptions($product));
            } else {
                $options->setBundleOption($lineOptions->getBundleOption());
            }
        }

        if ($product->getTypeId() == 'grouped' && $lineOptions->getSuperGroup()) {
            $options->setSuperGroup($lineOptions->getSuperGroup());
        }

        if ($product->getTypeId() == 'configurable') {
            $options->setSuperAttribute($lineOptions->getSuperAttribute());
        }

        // some products may result in multiple products getting added to cart
        // I believe this pulls them all and sets the custom options accordingly
        $addAll = $product->getTypeInstance(true)->prepareForCartAdvanced($options, $product);

        if (is_string($addAll)) {
            throw new \Exception($addAll);
        }

        $parentItem = null;
        foreach ($addAll as $addMe) {
            $item = $quote->findItem($addMe, $lineOptions->getData());
            if (!$item->getId() || $lineOptions->getNewLine()) {
                if ($quote instanceof \Magento\Sales\Model\Order || $quote instanceof \Epicor\Comm\Model\Order) {
                    $item = $this->salesOrderItemFactory->create();
                    // @var $item Mage_Sales_Model_Order_Item //
                } else {
                    $item = $this->quoteQuoteItemFactory->create();
                    // @var $item Mage_Sales_Model_Quote_Item //
                }
            }

            $item->setStoreId($this->storeManager->getStore()->getId());
            $item->setQuote($quote);

            $item->setOptions($addMe->getCustomOptions())
                ->setProduct($addMe);

            if ($item->getId() && !isset($request['update_config_value'])) {
                $item->setQty($item->getQty() + $addMe->getCartQty());
            } else {
                $item->setQty($addMe->getCartQty());
            }

            $stickWithinParent = $addMe->getParentProductId() ? $parentItem : null;
            $addMe->setStickWithinParent($stickWithinParent);

            /**
             * As parent item we should always use the item of first added product
             */
            if (!$parentItem) {
                $parentItem = $item;
            }

            if ($parentItem && $addMe->getParentProductId()) {
                $item->setParentItem($parentItem);
            }

// note, if rounding is needed, use $quote->getStore()->roundPrice($price)
// previously setCustomPrice, setPrice and setRowTotal all had rounding on

            if ($lineOptions->getBsvValues()) {
                $bsvValues = $lineOptions->getBsvValues();
                $item->setEccBsvPrice($bsvValues['price']);
                $item->setEccBsvPriceInc($bsvValues['price_inc']);
                $item->setEccBsvLineValue($bsvValues['line_value']);
                $item->setEccBsvLineValueInc($bsvValues['line_value_inc']);
            }

            if ($lineOptions->getForcePrice()) {
                $item->setOriginalCustomPrice($lineOptions->getForcePrice());
                $item->setBasePrice($lineOptions->getForcePrice());
            }

            if ($lineOptions->getOriginalPrice()) {
                $item->setEccOriginalPrice($lineOptions->getOriginalPrice());
            } else {
                $item->setEccOriginalPrice($item->getBasePrice());
            }

            if ($listsEnabled) {
                $this->listsAndContractsCheck($product, $item);
            }

            if ($locHelper->isLocationsEnabled()) {
                $locationCode = $locationCode ?: $this->locationsCheck($product, $lineOptions);
                if (!$locationCode) {
                    return;
                }
                $item->setEccLocationCode($locationCode);
                $item->setEccLocationName($locHelper->getLocationName($locationCode));
            }

            if ($lineOptions->getGqrLineNumber()) {
                $item->setEccGqrLineNumber($lineOptions->getGqrLineNumber());
            }

            $item->getProduct()->setIsSuperMode(true);
            $quote->addItem($item);
        }
    }

    /**
     * Checks whether locations for product is valid
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param \Magento\Framework\DataObject $lineOptions
     *
     * @return string $locationCode
     */
    protected function locationsCheck(\Epicor\Comm\Model\Product $product, \Magento\Framework\DataObject $lineOptions)
    {
        $locHelper = $this->commLocationsHelper;
        /* @var $locHelper Epicor_Comm_Helper_Locations */

        $locationCode = null;
        if ($locHelper->isLocationsEnabled()) {
            $allowedCustomerLocations = $locHelper->getCustomerAllowedLocationCodes();
            $locationCode = $lineOptions->getLocationCode();

            $productLocations = array_keys($product->getLocations());
            $allowedLocations = array_intersect($allowedCustomerLocations, $productLocations);
            $displayedLocations = $locHelper->getCustomerDisplayLocationCodes();
            if (!$locationCode) {
                $locationCode = $locHelper->getDefaultLocationCode();
                if (count($allowedLocations) == 1) {
                    $getLocation = array_slice($allowedLocations, 0, 1);
                    $locationCode = array_shift($getLocation);
                    $lineOptions->setLocationCode($locationCode);
                } elseif (count($displayedLocations) == 1) {
                    $_sliced = array_slice($displayedLocations, 0, 1);
                    $locationCode = array_shift($_sliced);
                    $lineOptions->setLocationCode($locationCode);
                } else {
                    // if all source locations selected in config, allocate the default location
                    if (in_array($this->scopeConfig->getValue('epicor_comm_locations/global/stockvisibility', \Magento\Store\Model\ScopeInterface::SCOPE_STORE), array('default', 'all_source_locations'))) {
                        if ($locationCode) {
                            $lineOptions->setLocationCode($locationCode);
                        }
                    } else {
                        //Handle this conditon - If the location code was empty in lineoptions
                        //In this scenario, get the product locations and insert that in lineoptions for single product locations
                        $singleLocation = (count($product->getCustomerLocations()) == 1) ? true : false;
                        if ($singleLocation) {
                            $locationCode = key($product->getCustomerLocations());
                            $lineOptions->setLocationCode($locationCode);
                        }
                    }
                }
            }

            //M1 > M2 Translation Begin (Rule 55)
            /*if (is_null($locationCode) || $locationCode == '') {
                $string = $this->__("Product %s could not be added to cart, please choose a location", $product->getSku());
                throw new \Exception($string);
            } else if (!in_array($locationCode, $allowedLocations)) {
                $string = $this->__("Product %s could not be added to cart, location no longer available", $product->getSku());
                throw new \Exception($string);
            } else if (!$product->isValidLocation($locationCode)) {
                $string = $this->__("Product %s could not be added to cart as it is not currently available in the specified location", $product->getSku());
                throw new \Exception($string);
            }*/
            if (is_null($locationCode) || $locationCode == '') {
                $string = __("Product %1 could not be added to cart, please choose a location.", $product->getSku());
                $this->commProductHelper->addErrorMessageArray($string);
                return false;
            } else if (!in_array($locationCode, $allowedCustomerLocations)) {
                $string = __("Product %1 could not be added to cart, location no longer available.", $product->getSku());
                $this->commProductHelper->addErrorMessageArray($string);
                return false;
            } else if (!$product->isValidLocation($locationCode)) {
                $string = __("Product %1 could not be added to cart as it is not currently available in the specified location.", $product->getSku());
                $this->commProductHelper->addErrorMessageArray($string);
                return false;

            }
            //M1 > M2 Translation End
        }

        return $locationCode;
    }

    /**
     * Checks whether lists and contracts for product is valid
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param \Magento\Framework\DataObject $item
     *
     * @return bool
     */
    protected function listsAndContractsCheck(\Epicor\Comm\Model\Product $product, \Magento\Framework\DataObject $item)
    {
        $listHelper = $this->listsFrontendProductHelper;
        /* @var $listHelper Epicor_Lists_Helper_Frontend_Product */

        $contractHelper = $this->listsFrontendContractHelper;
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */

        if ($listHelper->listsEnabled()) {
            if ($contractHelper->contractsEnabled()) {
                $lineContractCode = $item->getEccContractCode();
                $canBeAdded = $listHelper->productIsValidForCart($item->getProductId(), $lineContractCode);

                if ($canBeAdded === false) {
                    if ($item->isObjectNew() == false) {
                        $item->delete();
                    }
                    //M1 > M2 Translation Begin (Rule 55)
                    //$string = $this->__('Product %s could not be added to cart, product no longer available', $product->getSku());
                    $string = __('Product %1 could not be added to cart, product no longer available', $product->getSku());
                    //M1 > M2 Translation End
                    throw new \Exception($string);
                }
            } else {
                $item->setEccContractCode(null);
            }

            if ($listHelper->hasFilterableLists()) {
                $ids = $listHelper->getActiveListsProductIds();
                $productIds = explode(',', $ids);
                if (!in_array($item->getProductId(), $productIds)) {
                    //M1 > M2 Translation Begin (Rule 55)
                    //$string = $this->__("Product %s could not be added to cart, product no longer available", $product->getSku());
                    $string = __("Product %1 could not be added to cart, product no longer available", $product->getSku());
                    //M1 > M2 Translation End
                    throw new \Exception($string);
                }
            }
        }

        return true;
    }

    public function getBundleOptions($product)
    {
        $options = array();
        $optionsCollection = $this->bundleOptionFactory->create()->getResourceCollection()
            ->setProductIdFilter($product->getId())
            ->setPositionOrder();
        $product->getTypeInstance(true)->setStoreFilter($product->getStoreId(), $product);
        $storeId = $this->_getStoreFilter($product);
        if ($storeId instanceof \Magento\Store\Model\Store) {
            $storeId = $storeId->getId();
        }

        $optionsCollection->joinValues($storeId);
        $productOptionIds = $optionsCollection->getAllIds();

        // NOte: this section populates the default sleections for the bundle options
        // this will only work for products where the default has been selected
        // if you need to specify what selecitons were previously selected, this will not work!!!

        $selectionCollection = $this->bundleSelectionFactory->create()->getResourceCollection();
        /* @var $selectionCollection Mage_Bundle_Model_Resource_Selection_Collection */
        $selectionCollection->setOptionIdsFilter($productOptionIds);
        $selectionCollection->getSelect()->where('is_default = 1');
        $selectionDefaults = array();

        foreach ($selectionCollection->getItems() as $selection) {
            $selectionDefaults[$selection->getOptionId()] = $selection->getSelectionId();
        }

        foreach ($productOptionIds as $optionId) {
            $options[$optionId] = @$selectionDefaults[$optionId];
        }

        return $options;
    }

    /**
     * Retrive store filter for associated products
     *
     * @return int|\Magento\Store\Model\Store
     */
    private function _getStoreFilter($product = null)
    {
        $cacheKey = '_cache_instance_store_filter';
        return $product->getData($cacheKey);
    }

    /**
    * @param  \Epicor\Comm\Model\Quote $quote
    */
    public function updateExistingCart($quote)
    {
        $cartClearConfirm = $this->request->getParam('cartClearConfirm');
        $massAction = $this->request->getParam('massaction');
        $this->reorderOption = $this->scopeConfig->getValue(self::REORDER_OPTION, ScopeInterface::SCOPE_STORE);
        if ($cartClearConfirm == '1' || $this->reorderOption == 'clear') {
            //if massaction, only clear cart for first product
            if ($massAction) {
                if ($massAction == 'n') {
                    return;
                } else {
                    $this->request->setParam('massaction', 'n');
                }
            }
            $quote->removeAllItems();
        }
    }
}
