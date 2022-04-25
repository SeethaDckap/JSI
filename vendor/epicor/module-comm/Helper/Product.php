<?php
/**
 * Copyright ? 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Helper;

use Magento\Catalog\Model\ResourceModel\Product\Collection;

class Product extends \Epicor\Comm\Helper\Data
{

    protected $catalogRuleRuleFactoryExist=null;
    protected $getRuleActionsExist=false;
    protected $catalogRuleResourceModelRuleFactoryExist = null;

    /**
     * @var \Epicor\Comm\Helper\File
     */
    protected $commFileHelper;

    /**
     * @var \Epicor\Comm\Model\IndexerFactory
     */
    protected $commIndexerFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Epicor\Common\Helper\CartFactory
     */
    protected $commonCartHelperFactory;

    /**
     * @var \Epicor\Comm\Model\LocationFactory
     */
    protected $commLocationFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\CollectionFactory
     */
    protected $commResourceLocationCollectionFactory;

    /**
     * @var \Magento\Wishlist\Model\ItemFactory
     */
    protected $wishlistItemFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory
     */
    protected $catalogResourceModelProductOptionCollectionFactory;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Epicor\SalesRep\Helper\Pricing\Rule\Product
     */
    protected $salesRepPricingRuleProductHelper;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\ItemFactory
     */
    protected $catalogInventoryStockItemFactory;

    /**
     * @var \Magento\CatalogRule\Model\RuleFactory
     */
    protected $catalogRuleRuleFactory;

    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\RuleFactory
     */
    protected $catalogRuleResourceModelRuleFactory;

    /**
    /**
     * @var \Magento\Checkout\Model\SessionFactory
     */
    protected $checkoutSessionFactory;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $catalogInventoryApiStockRegistryInterface;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    protected $csvProcessor;

    protected $iosystem;

    protected $attributeRepository;

    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $_localeFormat;
    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    protected $productMetadata;

    /**
     * @var \Magento\Framework\App\Cache\StateInterface $_cacheState
     */
    protected $_cacheState;

    public function __construct(
        \Epicor\Comm\Helper\Context $context,
        \Epicor\Comm\Helper\File $commFileHelper,
        \Epicor\Comm\Model\IndexerFactory $commIndexerFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Epicor\Common\Helper\CartFactory $commonCartHelperFactory,
        \Epicor\Comm\Model\LocationFactory $commLocationFactory,
        \Epicor\Comm\Model\ResourceModel\Location\CollectionFactory $commResourceLocationCollectionFactory,
        \Magento\Wishlist\Model\ItemFactory $wishlistItemFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory $catalogResourceModelProductOptionCollectionFactory,
        \Magento\Framework\View\LayoutInterface $layout,
        \Epicor\SalesRep\Helper\Pricing\Rule\Product $salesRepPricingRuleProductHelper,
        \Magento\CatalogInventory\Model\Stock\ItemFactory $catalogInventoryStockItemFactory,
        \Magento\CatalogRule\Model\RuleFactory $catalogRuleRuleFactory,
        \Magento\CatalogRule\Model\ResourceModel\RuleFactory $catalogRuleResourceModelRuleFactory,
        \Magento\Checkout\Model\SessionFactory $checkoutSessionFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $catalogInventoryApiStockRegistryInterface,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\Filesystem\Io\File $iosystem,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Framework\App\Cache\StateInterface $cacheState
    ) {
        $this->customerSession = $context->getCustomerSessionFactory()->create();
        $this->commFileHelper = $commFileHelper;
        $this->commIndexerFactory = $commIndexerFactory;
        $this->eavConfig = $eavConfig;
        $this->commonCartHelperFactory = $commonCartHelperFactory;
        $this->commLocationFactory = $commLocationFactory;
        $this->commResourceLocationCollectionFactory = $commResourceLocationCollectionFactory;
        $this->wishlistItemFactory = $wishlistItemFactory;
        $this->catalogResourceModelProductOptionCollectionFactory = $catalogResourceModelProductOptionCollectionFactory;
        $this->layout = $layout;
        $this->salesRepPricingRuleProductHelper = $salesRepPricingRuleProductHelper;
        $this->catalogInventoryStockItemFactory = $catalogInventoryStockItemFactory;
        $this->catalogRuleRuleFactory = $catalogRuleRuleFactory;
        $this->catalogRuleResourceModelRuleFactory = $catalogRuleResourceModelRuleFactory;
        $this->checkoutSessionFactory = $checkoutSessionFactory;
        $this->catalogInventoryApiStockRegistryInterface=$catalogInventoryApiStockRegistryInterface;
        $this->messageManager = $context->getMessageManager();
        $this->eventManager = $context->getEventManager();
        $this->resourceConnection = $resourceConnection;
        $this->csvProcessor = $csvProcessor;
        $this->iosystem=$iosystem;
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_localeFormat = $localeFormat;
        $this->productMetadata = $context->getProductMetadata();
        $this->_cacheState = $cacheState;
        parent::__construct($context);
    }
    public function getMoreInfoUrl($filename)
    {
        //M1 > M2 Translation Begin (Rule p2-5.3)
        //return Mage::getBaseUrl() . 'assets/product/' . $filename;
        return $this->_urlBuilder->getBaseUrl() . 'assets/product/' . $filename;
        //M1 > M2 Translation End
    }

    /**
     * @param $request
     * @return bool
     */
    public function isCartConfigureUpdate($request)
    {
        if ($request instanceof \Magento\Framework\App\Request\Http) {
            return $request->getModuleName() . '-' . $request->getActionName() === 'checkout-configure';
        }
    }

    /**
     * Processes the related docutmens for a product, filtering out the ones that don't exist
     *
     * @param array $related
     *
     * @return array
     */
    public function processRelated($related)
    {
        $helper = $this->commFileHelper;
        /* @var $helper Epicor_Comm_Helper_File */

        $existing = array();

        if (!empty($related) && $related != 'no') {
            foreach ($related as $file) {
                //if filename is an URL.
                if (!filter_var($file['filename'], FILTER_VALIDATE_URL) === false) {
                    $file['link'] = $file['filename'];
                    $file['file_type'] = pathinfo($file['filename'], PATHINFO_EXTENSION);
                    $file['is_external'] = 1;
                    $existing[] = $file;
                }

                //M1 > M2 Translation Begin (Rule p2-5.5)
                //$relatedPath = Mage::getBaseDir() . DIRECTORY_SEPARATOR . str_replace('/', DS, $this->scopeConfig->getValue('Epicor_Comm/assets/product_related', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $relatedPath = $this->directoryList->getPath('media') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $this->scopeConfig->getValue('Epicor_Comm/assets/product_related', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                //M1 > M2 Translation End
                $relatedPath = rtrim($relatedPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR; // make sure there is a trailing slash

                if ($fileLocation = $helper->fileExists($relatedPath, $file['filename'], false)) {
                    $fileSections = explode(DIRECTORY_SEPARATOR, $fileLocation);
                    $file['filename'] = array_pop($fileSections);
                    $file['link'] = $this->getRelatedUrl(rawurlencode($file['filename']));
                    $file['file_type'] = pathinfo($file['filename'], PATHINFO_EXTENSION); //Varien_File_Object::getExt($file['filename']);
                    $file['is_external'] = 0;
                    $existing[] = $file;
                }
            }
        }

        return $existing;
    }

    /**
     * Gets the related document url for link display
     *
     * @param string $filename
     *
     * @return string
     */
    public function getRelatedUrl($filename)
    {
        $relatedPath = str_replace(DIRECTORY_SEPARATOR, '/', $this->scopeConfig->getValue('Epicor_Comm/assets/product_related', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $relatedPath = rtrim($relatedPath, '/') . '/'; // make sure there is a trailing slash
        $store = $this->storeManager->getStore();
        $mediaUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl. $relatedPath . $filename;
    }

    /**
     *
     * @param Mage_Catalog_Product $product
     */
    public function reindexProduct($product, $force = false)
    {
        $indexer = $this->commIndexerFactory->create()->indexProduct($product, $force);
    }

    /**
     *
     * @param Mage_Catalog_Product $product
     */
    public function reindexProductById($productId)
    {
        $indexer = $this->commIndexerFactory->create();
        /* @var $indexer \Epicor\Comm\Model\Indexer */
        $indexer->indexProductById($productId);
    }

    /**
     * Gets the option value for price type from the provided price type value
     *
     * @param integer $priceType
     *
     * @return string
     */
    public function getPriceDisplayTypeName($priceType)
    {
        if (!is_null($priceType)) {
            $attributeModel = $this->eavConfig->getAttribute('catalog_product', 'ecc_price_display_type');
            $priceType = $attributeModel->getSource()->getOptionText($priceType);
        } else {
            $priceType = $this->scopeConfig->getValue('Epicor_Comm/units_of_measure/price_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        return $priceType;
    }

    public function processProductCsvUpload($file)
    {
        $listHelper = $this->listsFrontendProductHelper;
        /* @var $listHelper Epicor_Lists_Helper_Frontend_Product */
        $contractHelper = $this->listsFrontendContractHelper;
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */

        $uomSeparator = $this->commMessagingHelper->getUOMSeparator();
        $activeListsProductIds = array();
        $filterProductsForLists = false;
        if ($listHelper->listsEnabled() && ($listHelper->hasFilterableLists() || $contractHelper->mustFilterByContract())) {
            $filterProductsForLists = true;
            $activeListsProductIds = $listHelper->getActiveListsProductIds(true);
        }

        $fileContents = fopen($file, "rb");
        $file = '';
        // get last line of comments to determine position of sku, qty and uom
        do {
            $file = fgets($fileContents);
            $sample = substr($file, 0, 5);
        } while (!feof($fileContents) && strpos($sample, '/**') > -1);

        $titles = explode(',', strtoupper(trim($file)));
        $processedTitles = array();
        foreach ($titles as $x => $title) {
            $processedTitles[$x] = trim($title);
        }

        $skuCol = array_search('SKU', $processedTitles);
        $uomCol = array_search('UOM', $processedTitles);
        $qtyCol = array_search('QTY', $processedTitles);
        $locCol = array_search('LOCATION', $processedTitles);
        //$contractCol = array_search('CONTRACT', $processedTitles);

        $products = array();
        $errors = array();

        // if the csv file is invalid send message and exit
        if (($skuCol === false) || ($uomCol === false) || ($qtyCol === false)) {
            $errors['general'] = __('Invalid headers in file - must contain SKU, UOM and QTY');
        } else {
            while ($row = fgets($fileContents)) {
                $product = explode(',', $row);
                // only continue with this row if an sku and qty is supplied
                if ($product[$skuCol]) {
                    if (!is_numeric(trim($product[$qtyCol])) || trim($product[$qtyCol] == 0)) {
                        //M1 > M2 Translation Begin (Rule 55)
                        //$errors[] = $this->__('Product %s could not be added to basket as the quantity supplied is invalid', $product[$skuCol]);
                        $errors[] = __('Product %1 could not be added to basket as the quantity supplied is invalid', $product[$skuCol]);
                        //M1 > M2 Translation End
                    } else {
                        $location = $locCol !== false && isset($product[$locCol]) ? trim($product[$locCol]) : '';

                        $contract = '';

                        $wishlistItemId = false;

                        $product = $this->createCsvProductList(
                            trim($product[$skuCol]), trim($product[$uomCol]), trim($product[$qtyCol]), $location, $wishlistItemId, $contract
                        );
                        if (!empty($product['errors'])) {
                            $errors = array_merge($errors, $product['errors']);
                        } else {
                            $productObject = $product['product']['product_added'];
                            $productId = $productObject->getId();
                            $productSku = str_replace($uomSeparator, ' ', $productObject->getSku());
                            if ($filterProductsForLists && in_array($productId, $activeListsProductIds) == false) {
                                //M1 > M2 Translation Begin (Rule 55)
                                //$errors[] = $this->__('Product %s is not currently available', $productSku);
                                $errors[] = __('Product %1 is not currently available', $productSku);
                                //M1 > M2 Translation End
                            } else {
                                $products[] = $product['product'];
                            }
                        }
                    }
                }
            }
            fclose($fileContents);
        }

        return array(
            'errors' => $errors,
            'products' => $products
        );
    }

    public function createCsvProductList($sku, $uom, $qty, $location = null, $wishlistItemId = false, $contract = null)
    {
        // remember also to check the javascript about running ajax in the call - if you can get the input file to submit properly
        $productInfo = array();
        $errors = array();
        $connection = $this->resourceConnection->getConnection('core_read');
        $select = $connection->select()->from('catalog_product_entity')
            ->reset(\Zend_Db_Select::COLUMNS)->columns(['type_id'])->where('sku=?', $sku);
        $type_id = $connection->fetchOne($select);

        if($type_id && $type_id == 'grouped'){
            //$product = $this->findProductBySku($sku, '', false);
            $childProduct = $this->findProductBySku($sku, $uom, false);
            $decimalPlaces = $this->commFileHelper->getDecimalPlaces($childProduct);

            $productInfo = array(
                'product' => $childProduct,
                'qty' => $this->commFileHelper->qtyRounding($qty, $decimalPlaces),
                'super_group' => null,
                'location_code' => $location,
                //'contract_code' => $contract,
                'product_added' => $childProduct
            );



            if ($wishlistItemId) {
                $productInfo['wishlist_item_id'] = $wishlistItemId;
            }

            if (!$childProduct || $childProduct->isObjectNew() || !$childProduct->isSaleable()) {
                //M1 > M2 Translation Begin (Rule 55)
                //$errors[] = $this->__('Product %s is not currently available', $sku . ' ' . $uom);
                $errors[] = __('Product %1 is not currently available', $sku . ' ' . $uom);
                //M1 > M2 Translation End
            } else if ($childProduct->getSku() != $sku) {
                $add = true;
                $productInfo['super_group'] = array($childProduct->getId() => $qty);
                $productInfo['product_added'] = $childProduct;

            }
        }else{
            // $productModel = $this->catalogProductFactory->create();
             $product = $this->findProductBySku($sku, '', false);

             /* @var $product Mage_Catalog_Model_Product */
             $add = false;

             if (!$product || $product->isObjectNew() || !$product->isSalable()) {
                 //M1 > M2 Translation Begin (Rule 55)
                 //$errors[] = $this->__('Product %s could not be added to basket as it is not currently available', $sku);
                 $errors[] = __('Product %1 could not be added to basket as it is not currently available', $sku);
                 //M1 > M2 Translation End
             } else {
                 $productInfo = array(
                     'product' => $product,
                     'qty' => $qty,
                     'super_group' => null,
                     'location_code' => $location,
                     //'contract_code' => $contract,
                     'product_added' => $product
                 );

                 if ($wishlistItemId) {
                     $productInfo['wishlist_item_id'] = $wishlistItemId;
                 }
             }
        }
        return array(
            'errors' => $errors,
            'product' => $productInfo
        );
    }

    public function addCsvProductToCart($products, $emptyCart = null, $submittedFromWishlist = null)
    {
        $configureProducts = array(
            'products' => array(),
            'qty' => array()
        );

        try {
            $helper = $this->commonCartHelperFactory->create();

            $productHelper = $this;
            /* @var $productHelper \Epicor\Comm\Helper\Product */

            $cart = $this->checkoutCart;
            $quote = $this->checkoutSession->getQuote();
            /* @var $quote Epicor_Comm_Model_Quote */

            if ($emptyCart) {
                $quote->removeAllItems();
                $this->customerSession->unsetData('configure_products');
            }
            $quote->setEccQuoteId(null);
            $quote->setEccIsDdaDate(false);
            $quote->setAllowSaving(true);
            $quote->save();
            if (!$this->registry->registry('send_msq')) {
                $this->registry->register('send_msq', true);
            }
            $skuslimit = $this->scopeConfig->getValue('epicor_comm_enabled_messages/msq_request/quickpad_max_sku_in_msq', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if($skuslimit){
                $this->registry->register('csv_quickpad_send_msq', true);
            }else{
                $this->registry->register('csv_quickpad_send_msq', false);
            }
            $cart->setQuote($quote);

            $locHelper = $this->commLocationsHelper;
            /* @var $locHelper Epicor_Comm_Helper_Locations */

            $locNames = array();
            $locCodes = array();
            $successMsg=false;

            $locEnabled = $locHelper->isLocationsEnabled();
            $successCount=0;
            foreach ($products as $line) {

                $productVisible = true;

                $product = $line['product'];
                /* @var $product Epicor_Comm_Model_Product */
                $configure = false;

                if (/* $submittedFromWishlist || */ $product->getEccConfigurator() || $product->getTypeId() == 'configurable' || $productHelper->productHasCustomOptions($product) || ($product->getTypeId() == 'grouped' && empty($line['super_group']))) {
                    $configure = true;
                }
                $locationCode = '';
                if (!$configure && $locEnabled) {
                    $locationCode = $line['location_code'];
                    if (empty($locationCode)) {
                        $locations = $product->getStockedLocations();
                        if (count($locations) == 1) {
                            $location = array_pop($locations);
                            $locationCode = $location->getLocationCode();
                        } else {
                            $configure = true;
                        }
                    } else if (in_array($locationCode, $locCodes)) {
                        $locationCode = $locationCode;
                    } else if (isset($locNames[$locationCode])) {
                        $locationCode = $locNames[$locationCode];
                    } else {
                        // check location code is a code or is a name (then grab the code)
                        $location = $this->commLocationFactory->create();
                        /* @var $location Epicor_Comm_Model_Location */
                        $location->load($locationCode, 'code');
                        if (!$location->isObjectNew()) {
                            $locCodes[] = $locationCode;
                        } else {
                            $locationName = $locationCode;
                            $locations = $this->commResourceLocationCollectionFactory->create();
                            /* @var $locations Epicor_Comm_Model_Resource_Location_Collection */
                            $locations->addFieldToFilter('name', $locationName);
                            $location = $locations->getFirstItem();
                            if (!$location->isObjectNew()) {
                                $locNames[$locationName] = $location->getCode();
                                $locationCode = $location->getCode();
                            }
                        }
                    }
                    $line['location_code'] = $locationCode;
                }
                if (isset($line['super_group']) && ($line['super_group'] != "")) {
                    $id = key($line['super_group']);

                    if (isset($line['product_added'])){
                        $_product = $line['product_added'];
                    }else{
                        $_product = $this->catalogProductFactory->create()->load($id);
                    }

                    $newQty = $this->getCorrectOrderQty($_product, $line['qty'], $locEnabled, $locationCode,false,$quote);
                    $line['super_group'][$id] = $newQty['qty'];
                } else {
                    $newQty = $this->getCorrectOrderQty($product, $line['qty'], $locEnabled, $locationCode,false,$quote);
                }
                if ($newQty['qty'] != $line['qty']) {
                    $line['qty'] = $newQty['qty'];
                    $message = $newQty['message'];
                    $this->messageManager->addSuccessMessage($message);
                }
//                //Check contract column in the line request
//                $contractCodes = $this->contractCheckCsv($configure, $line, $product->getId());
//
//                // Assign contract id to contract_code
//                if (($contractCodes['id']) && ($contractCodes['status'] == "success")) {
//                    $line['contract_code'] = $contractCodes['id'];
//                }


                if ($configure && $locEnabled) {
                    $productVisible = $locHelper->isProductVisibleInDisplayedLocations($product->getId());
                }
                if ($configure && !$productVisible) {
                    $message = __('Product %1 requires configuration but is not available in current filtered locations', $helper->removeUOMSeparator($line['product_added']->getSku()));
                    $this->messageManager->addErrorMessage($message);
                } else if ($configure) {
                    $configureProducts['products'][] = $line['product']->getId();
                    $configureProducts['qty'][$line['product']->getId()] = $line['qty'];
//                } else if ($contractCodes['status'] == "failed") {
//                    Mage::getSingleton('core/session')->addError($this->__('Product %s  is not available for the contract', $helper->removeUOMSeparator($line['product_added']->getSku())));
                } else {
                    try {
                        $quote->addOrUpdateLine($line['product'], $line);
                        if (!$quote->hasProductId($line['product_added']->getId())) {
                            // Product is not in the shopping cart so show an error

                            if (!$this->isAllowToShowMessageArray()) {
                                $message = __('Product %1 could not be added to cart', $helper->removeUOMSeparator($line['product_added']->getSku()));
                                $this->messageManager->addErrorMessage($message);
                            }
                        } else {
                            if (isset($line['wishlist_item_id']) && $line['wishlist_item_id']) {
                                $this->wishlistItemFactory->create()->load($line['wishlist_item_id'])->delete();
                            }
                            $successMsg=true;
                            $successCount++;
                            //$message = __('Product %1 %2 %3 added to basket', $helper->getSku($line['product_added']->getSku()), $line['qty'], $helper->getUom($line['product_added']->getSku()));
                           // $this->messageManager->addSuccessMessage($message);
                        }
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage($e->getMessage());
                    } catch (Mage_Exception $e) {
                        $this->messageManager->addErrorMessage($e->getMessage());
                    }
                }
            }

            $this->checkoutSession->setCartWasUpdated(true);
            $cart->save();
            if($successMsg && !$cart->getQuote()->getHasError()){
                $sucmessage = __('%1 Products successfully added to basket', $cart->getQuote()->getItemsCount());
                $this->messageManager->addSuccessMessage($sucmessage);
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Mage_Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $configureProducts;
    }

    /**
     * @return bool
     */
    public function isAllowToShowMessageArray()
    {
        $allErrors = $this->checkoutSession->getQopErrors();
        return ($allErrors === null) || (is_array($allErrors) && count($allErrors) > 0);
    }

    /**
     * @param string
     *
     * @return bool
     */
    public function addErrorMessageArray($message)
    {
        $productsError = $this->checkoutSession->getQopErrors();
        if ($this->isAllowToShowMessageArray()) {
            if ($productsError === null) {
                $productsError = [$message];
                $this->checkoutSession->setQopErrors($productsError);
            } else {
                $productsError[] = $message;
                $this->checkoutSession->setQopErrors($productsError);
            }
        }
    }

    /**
     *
     * @param \Epicor\Comm\Model\Product $product
     *
     * @return type
     */
    public function getProductUrl($product, $query = array(), $configCheck = false)
    {
        if ($configCheck) {
            if ($this->isProductInConfigureList($product->getId()) && $this->hasProductConfigureListQty($product->getId())) {
                $query['qty'] = $this->getProductConfigureListQty($product->getId());
            }
        }

        $url = $product->getProductUrl();

        $urlParams = array();

        foreach ($query as $name => $value) {
            $urlParams[] = $name . '=' . urlencode($value);
        }

        $urlParams = implode('&', $urlParams);

        if ($urlParams) {
            $glue = '?';
            if (strpos($url, $glue) !== false) {//this should never happen - but just in case
                $glue = '&';
            }
            $url .= $glue . $urlParams;
        }

        return $url;
    }

    /**
     *
     * @param \Epicor\Comm\Model\Product $product
     *
     * @return type
     */
    public function productHasCustomOptions($product, $required = true)
    {
        $optionCache = $this->registry->registry('product_options_cache');

        if (empty($optionCache) || !isset($optionCache[$product->getId()])) {
            $options = $this->catalogResourceModelProductOptionCollectionFactory->create()
                ;
            /* @var $options Mage_Catalog_Model_Resource_Product_Option */

            $options->addFieldToFilter('product_id', $product->getId());

            $requiredCount = 0;

            $rows = $options->getItems();
            foreach ($rows as $option) {
                if ($option->getIsRequire()) {
                    $requiredCount++;
                }
            }

            $optionCache[$product->getId()] = array(
                'required' => $requiredCount,
                'total' => count($rows)
            );

            $this->registry->unregister('product_options_cache');
            $this->registry->register('product_options_cache', $optionCache);
        }

        if ($required) {
            $hasOptions = $optionCache[$product->getId()]['required'] > 0 ? true : false;
        } else {
            $hasOptions = $optionCache[$product->getId()]['total'] > 0 ? true : false;
        }

        return $hasOptions;
    }

    /**
     * gets the options html for a product
     * @param \Epicor\Comm\Model\Product $product
     * @param \Epicor\Comm\Model\Product $child
     * @param array $options
     * @return string
     */
    public function getProductOptionsHtml(\Magento\Catalog\Model\Product $product, $child = null, $options = null)
    {
        $blockOptionsHtml = '';
        if ($product->getTypeId() == 'grouped') {
            $blockGrouped = $this->layout->createBlock('Epicor\Comm\Block\Catalog\Product\View\Type\Grouped');
            $blockGrouped->setTemplate('epicor_comm/catalog/product/view/type/grouped.phtml');
            $blockGrouped->setProduct($product);
            $blockGrouped->setHidePrices(true);
            $blockGrouped->setHideLocations(true);

            $blockOptionsHtml .= $blockGrouped->toHtml();
        } else {
            $blockOption = $this->layout->createBlock("Magento\Catalog\Block\Product\View\Options");
            $blockOption->addChild("default", "Magento\Catalog\Block\Product\View\Options\Type\DefaultType", ['template' => "Magento_Catalog::product/view/options/type/default.phtml"]);
            $blockOption->addChild("text", "Magento\Catalog\Block\Product\View\Options\Type\Text", ['template' => "Magento_Catalog::product/view/options/type/text.phtml"]);
            $blockOption->addChild("file", "Magento\Catalog\Block\Product\View\Options\Type\File", ['template' => "Magento_Catalog::product/view/options/type/file.phtml"]);
            $blockOption->addChild("select", "Magento\Catalog\Block\Product\View\Options\Type\Select", ['template' => "Magento_Catalog::product/view/options/type/select.phtml"]);
            $blockOption->addChild("date", "Magento\Catalog\Block\Product\View\Options\Type\Date", ['template' => "Magento_Catalog::product/view/options/type/date.phtml"]);
            $blockOption->addChild("ecc_text", "Epicor\Comm\Block\Catalog\Product\View\Options\Type\Ecc\Text", ['template' => "Epicor_Comm::epicor_comm/catalog/product/view/options/type/ecc/text.phtml"]);

            if ($product->getTypeId() == "simple" || $product->getTypeId() == "configurable") {
                $blockOption->setProduct($product);
                if ($product->getOptions()) {
                    foreach ($product->getOptions() as $o) {
                        if ($options && isset($options[$o->getTitle()])) {
                            $preConfigData = $this->dataObjectFactory->create();
                            $preConfigData->setData(array('options' => array($o->getId() => $options[$o->getTitle()])));
                            //$product->setData('preconfigured_values', $this->dataObjectFactory->create(array('options' => array($o->getId() => $options[$o->getTitle()]))));
                            $product->setPreconfiguredValues($preConfigData);
                            $blockOption->setProduct($product);
                        }
                        $blockOptionsHtml .= $blockOption->getOptionHtml($o);
                    };
                }
            }

            if ($product->getTypeId() == "configurable") {

                if ($child) {
                    $atts = $product->getTypeInstance(true)
                        ->getConfigurableAttributes($product);
                    $preConfigData = null;
                    foreach ($atts as $att) {
                        /* @var $child Epicor_Comm_Model_Product */
                        /* @var $att Mage_Catalog_Model_Product_Type_Configurable_Attribute */
                        $code = $att->getProductAttribute()->getAttributeCode();
                        if ($child->getData($code)) {
                            if ($preConfigData == null) {
                                $preConfigData = $this->dataObjectFactory->create();
                                $preConfigData->setSuperAttribute($this->dataObjectFactory->create());
                            }
                            $product->setData($code, $child->getData($code));
                            $product->setAttributeDefaultValue($code, $child->getData($code));
                            $preConfigData->getSuperAttribute()->setData($att->getProductAttribute()->getId(), $child->getData($code));
                        }
                    }
                    $product->setPreconfiguredValues($preConfigData);
                }

                $blockViewType = $this->layout->createBlock("Magento\ConfigurableProduct\Block\Product\View\Type\Configurable");
                $blockViewType->setProduct($product);
                $blockViewType->setTemplate("Epicor_Comm::epicor_comm/catalog/product/view/type/configurable.phtml");
                $blockViewType->setChild('attr_renderers', $blockOption);
                $blockOptionsHtml .= $blockViewType->toHtml();
            }
        }
        return $blockOptionsHtml;
    }

    /**
     * Adds products
     *
     * @param array $products
     * @param boolean $overwrite
     */
    public function addConfigureListProducts($products, $overwrite = false)
    {
        if ($overwrite) {
            $this->clearConfigureList();
        }

        $oldList = $this->getConfigureListProducts();
        $newList = array_merge($oldList, $products);

        $this->customerSessionFactory()->setConfigureProducts($newList);
    }

    /**
     * Adds products
     *
     * @param array $products
     * @param boolean $overwrite
     */
    public function addConfigureListQtys($qtys)
    {
        $oldList = $this->getConfigureListQtys();
        $newList = array_merge($oldList, $qtys);

        $this->customerSessionFactory()->setConfigureProductsQtys($newList);
    }

    /**
     * Removes a product from the configure list
     *
     * @param integer $productId
     */
    public function removeConfigureListProduct($productId)
    {
        $oldList = $this->getConfigureListProducts();

        # need to use this when we change it to store better data
        #if (isset($oldList[$productId])) {
        #    unset($oldList[$productId]);
        #}

        $configureList = array();
        foreach ($oldList as $confId) {
            if ($confId != $productId) {
                $configureList[] = $confId;
            }
        }

        $this->customerSessionFactory()->setConfigureProducts($configureList);
    }

    public function getConfigureListQtys()
    {
        return $this->customerSessionFactory()->getConfigureProductsQtys() ?: array();
    }

    public function getConfigureListProducts()
    {
        return $this->customerSessionFactory()->getConfigureProducts() ?: array();
    }

    public function getConfigureListProductIds()
    {
        $configureList = $this->getConfigureListProducts();

        #return array_keys($configureList);

        return $configureList;
    }

    public function clearConfigureList()
    {
        $this->customerSessionFactory()->setConfigureProducts(false);
    }

    public function sessionHasConfigureList()
    {
        $configureList = $this->getConfigureListProducts();
        return !empty($configureList);
    }

    public function isProductInConfigureList($productId)
    {
        $configureList = $this->getConfigureListProducts();
        return in_array($productId, $configureList);
    }

    public function hasProductConfigureListQty($productId)
    {
        $qtys = $this->getConfigureListQtys();
        return isset($qtys[$productId]);
    }

    public function getProductConfigureListQty($productId)
    {
        $qtys = $this->getConfigureListQtys();
        return isset($qtys[$productId]) ? $qtys[$productId] : 0;
    }

    public function getProductPrice($product, $qty = 1)
    {
        $price = $product->getPrice();

        $tierPrice = $product->getTierPrice($qty);

        if (is_array($tierPrice) && !empty($tierPrice) && isset($tierPrice[0]['website_price'])) {
            $tierPrice = $tierPrice[0]['website_price'];
        }

        if (!is_null($tierPrice)) {
            $price = $price > 0 ? min($price, $tierPrice) : $tierPrice;
        }

        $special = $product->getSpecialPrice();

        if (!is_null($special)) {
            $price = $price > 0 ? min($price, $special) : $special;
        }

        return $price;
    }

    /**
     * Gets an array of info for a product for use with json encoding
     *
     * @param \Epicor\Comm\Model\Product $product
     */
    public function getProductInfoArray($product)
    {
        $infoArray = array();
        $infoArray['is_custom'] = htmlentities($product->getIsCustom());
        $infoArray['entity_id'] = htmlentities($product->getId());
        $infoArray['configurator'] = htmlentities($product->getEccConfigurator());
        $infoArray['ewa_attributes'] = htmlentities($product->getEwaAttributes());
        $infoArray['ewa_code'] = htmlentities($product->getEwaCode());
        $infoArray['ewa_description'] = htmlentities($product->getEwaDescription());
        $infoArray['ewa_short_description'] = htmlentities($product->getEwaShortDescription());
        $infoArray['ewa_sku'] = htmlentities($product->getEwaSku());
        $infoArray['ewa_title'] = htmlentities($product->getEwaTitle());
        $infoArray['sku'] = $product->getSku();
        $infoArray['uom'] = htmlentities($product->getEccUom());
        $infoArray['name'] = htmlentities($product->getName());
        $infoArray['type_id'] = htmlentities($product->getTypeId());
        $infoArray['stk_type'] = htmlentities($product->getEccStkType());
        $infoArray['use_price'] = $product->getUsePrice();
        $infoArray['ecc_product_type'] = ($product->getEccProductType())? $product->getEccProductType() : '';
        $infoArray['dealer_price'] = $product->getDealerPrice();
        $infoArray['formatted_price'] = $product->getMsqFormattedPrice();
        $infoArray['formatted_total'] = $product->getMsqFormattedTotal();
        $infoArray['decimal_place'] = $this->getDecimalPlaces($product);
        $infoArray['qty'] = $product->getMsqQty();

        $infoArray['has_options'] = htmlentities($this->productHasCustomOptions($product, false));
        $infoArray['has_required_options'] = htmlentities($this->productHasCustomOptions($product));

        $infoArray['error'] = 0;

        if ($product->getOptionValues()) {
            $infoArray['option_values'] = $product->getOptionValues();
            $infoArray['option_values_encoded'] = base64_encode(serialize($product->getOptionValues()));
        }

        $customerSession = $this->customerSessionFactory();

        $customer = $customerSession->getCustomer();
        /* @var $customer \Epicor\Comm\Model\Customer */

        if ($customer->isSalesRep() && ($product instanceof \Epicor\Comm\Model\Product && !$product->isObjectNew())) {
            $pricingRuleProductHelper = $this->salesRepPricingRuleProductHelper;

            $rulePrice = $pricingRuleProductHelper->getRuleBasePrice($product, $infoArray['use_price'], $product->getMsqQty());
            $discountPercent = $pricingRuleProductHelper->getDiscountAmount($infoArray['use_price'], $rulePrice);

            if (!empty($discountPercent)) {
                $infoArray['salesrep_discount_value'] = $discountPercent;
            }

            $infoArray['salesrep_rule_price'] = $rulePrice;
            $infoArray['salesrep_min_price'] = $pricingRuleProductHelper->getMinPrice($product, $infoArray['use_price'], $product->getMsqQty());
            $infoArray['salesrep_max_discount'] = $pricingRuleProductHelper->getMaxDiscount($product, $infoArray['use_price'], $product->getMsqQty());
            $infoArray['salesrep_price_title'] = __('Price');
            $infoArray['salesrep_discount_title'] = __('Discount');
        }elseif ($customer->isDealer() && ($product instanceof \Epicor\Comm\Model\Product && !$product->isObjectNew())) {
            $infoArray['dealer_price_title'] = __('Price');
            $infoArray['dealer_discount_title'] = __('Discount');
        }


        return $infoArray;
    }

    public function massiveAddFromSku($skus = array(), $msqTrigger = '')
    {
        $configureProducts = array();

        if (!is_array($skus)) {
            $skus = array($skus);
        }

        $products = array();
        foreach ($skus as $sku) {
            $qty = isset($sku['qty']) ? $sku['qty'] : 1;
            $wishlistItemId = isset($sku['wishlist_item_id']) ? $sku['wishlist_item_id'] : false;
            $csvProduct = $this->createCsvProductList(trim($sku['sku']), '', $qty, $wishlistItemId);
            if (!empty($csvProduct['product'])) {
                $products[] = $csvProduct['product'];
            } elseif (!empty($csvProduct['errors'])) {
                foreach ($csvProduct['errors'] as $error) {
                    $this->messageManager->addError(__($error));
                }
            }
        }

        $msgHelper = $this->commMessagingHelper;
        /* @var $msgHelper Epicor_Comm_Helper_Messaging */

        $msqProducts = array();
        foreach ($products as $product) {
            $msqProducts[] = $product['product'];
        }
        $msgHelper->sendMsq($msqProducts, $msqTrigger);

        if (count($products) > 0) {
            $configureProducts = $this->addCsvProductToCart($products);
        }

        return $configureProducts;
    }

    /**
     * Check the contract code is valid or not (based on that return contract list id)
     * @return Contract Id & status(success or failed)
     */
    public function contractCheckCsv($configure, $line, $productId)
    {
        $contractValues = array();
        $contractHelper = $this->listsFrontendContractHelper;
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */
        if ($configure == false && $contractHelper->listsEnabled()) {
            if (empty($line['contract_code']) == false) {
                $contractName = $line['contract_code'];
                $contracts = $this->listsResourceListModelCollectionFactory->create();
                $contracts->addFieldToFilter('erp_code', array(
                    'eq' => $contractName
                ));
                $contract = $contracts->getFirstItem();
                $contractId = $contract->getId();
                if (!empty($contractId) && !empty($productId)) {
                    $contractValues['id'] = $contract->getId();
                    $valid = $contractHelper->isProductValidForContract($contract->getId(), $productId);
                    $contractValues['status'] = $valid ? 'success' : 'failed';
                } else {
                    $contractValues['status'] = 'failed';
                }
                return $contractValues;
            }
        }
    }

    /**
     * Sets a product to prices taken form an msq formatted message
     *
     * @param \Epicor\Comm\Model\Product $eccProduct
     * @param \Epicor\Common\Model\Xmlvarien $productCurrency
     */
    public function setProductToMsqPrices(&$eccProduct, $productCurrency)
    {
        $this->setProductPrices($eccProduct, $productCurrency->getBasePrice(), $productCurrency->getCustomerPrice(), $productCurrency->getBreaks(), 4, false);
    }

    /**
     * Sets a product to stock taken form an msq formatted message
     *
     * @param \Epicor\Comm\Model\Product $eccProduct
     * @param \Epicor\Common\Model\Xmlvarien $erpProduct
     */
    public function setProductToMsqContractStock(&$eccProduct, $erpProduct)
    {
        $helper = $this->commMessagingHelper;
        /* @var $helper Epicor_Comm_Helper_Messaging */

        $stockData = $eccProduct->getStockData();
        $max = $erpProduct->getMaximumContractQty();
        if (!empty($max)) {
            $stockData['max_sale_qty'] = $max;
        }
        $stockData['qty'] = $erpProduct->getMaximumContractQty();

        $eccProduct->setStockData($stockData);

        // Set availability

        $in_stock = $stockData['qty'] > 0 ? true : $this->scopeConfig->isSetFlag('epicor_comm_enabled_messages/msq_request/products_always_in_stock', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $eccProduct->setData('is_salable', $in_stock);
        $eccProduct->setErpStock($stockData['qty']);
        //M1 > M2 Translation Begin (Rule 23)
        // $stockItem = $eccProduct->getStockItem();
        $stockItem = $this->catalogInventoryApiStockRegistryInterface->getStockItem($eccProduct->getId(), $eccProduct->getStore()->getWebsiteId());
        //M1 > M2 Translation End

        if (!$stockItem || !$stockItem->getProductId()) {
            $stockItem = $this->catalogInventoryStockItemFactory->create();
            /* @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
            //M1 > M2 Translation Begin (Rule 6)
            //$stockItem->loadByProduct($eccProduct->getId());
            $stockItem->getResource()->loadByProductId($stockItem, $eccProduct->getId(), $stockItem->getStockId());
            //M1 > M2 Translation End
        }
        $stockItem->addData($stockData);
        $eccProduct->setStockItem($stockItem);
    }

    protected function _getLeadTime($leadTime, $leadTimeText)
    {
        if (preg_match("/[0-9]/", $leadTimeText)) {
            return $leadTimeText;
        } else {
            return $leadTime . ' ' . $leadTimeText;
        }
    }

    public function catalogRuleRuleFactory()
    {
        if (!$this->catalogRuleRuleFactoryExist) {
            $this->catalogRuleRuleFactoryExist = $this->catalogRuleRuleFactory->create();
        }
        return $this->catalogRuleRuleFactoryExist;
    }

    /**
     * Sets a product to prices taken form different sources
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param float $basePrice
     * @param float $customerPrice
     * @param array breaks
     * @param int   $roundingDecimals
     * @param bool  $allowPriceRules
     */
    public function setProductPrices(
        \Magento\Catalog\Model\Product $product,
        $basePrice,
        $customerPrice,
        $breaks,
        $roundingDecimals = 4,
        $allowPriceRules = true
    )
    {
        $store = $this->storeManager->getStore();
        /* @var $store Epicor_Comm_Model_Store */

        $rule = $this->catalogRuleRuleFactory();
        /* @var $rule Mage_Catalogrule_Model_Rule */

        $useCustomerPrice = $this->scopeConfig->getValue('epicor_comm_enabled_messages/msq_request/cusomterpriceused', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        //Getting the rules for the product
        $pricerules = $this->getRuleActions($product, $customerPrice);
        //Hide tier price if the action is to_fixed

        if ($allowPriceRules
            && isset($pricerules['action'])
            && $pricerules['action'] == "to_fixed"
        ) {
            //In Core Magento calcPriceRule case 'to_fixed':
            //$priceRule = min($ruleAmount, $price); - So Instead of overiding the rule, we are passing the discountAmount and adding 1 to the discount anount
            $fixedPrice = $rule->calcProductPriceRule($product, $pricerules['discountAmount'] + 1);
            //setHigherDiscount = true if the final price is higher than the customer price
            if ($pricerules['discountAmount'] > $customerPrice) {
                $product->setHigherDiscount(true);
            }
            $product->setPromotionalAmount($fixedPrice);
            $product->setOrdinaryCustomerAmount($customerPrice);
            $customerPrice = $fixedPrice;
            $tierPricing = array();
            $minimalPrice = '';
        } else {
            if (!$customerPrice) {
                $customerPrice = $product->getPrice();
            }
            $minimalPrice = $customerPrice * 10;
            // if Msq supports array then it will call getTierPricingArray
            if(in_array(\Epicor\Comm\Model\Message\Request\Msq::MESSAGE_TYPE,$this->commHelper->getArrayMessages())){
                $tierPricing = $this->getTierPricingArray($product, $breaks, $customerPrice, $minimalPrice, $roundingDecimals, $allowPriceRules);
            }else{
                $tierPricing = $this->getTierPricing($product, $breaks, $customerPrice, $minimalPrice, $roundingDecimals, $allowPriceRules);
            }
            if (!$allowPriceRules || !($minPrice = $rule->calcProductPriceRule($product, $customerPrice))) {
                $minPrice = $customerPrice;
            }
            $minimalPrice = min($minPrice, $minimalPrice);
        }
        if(isset($pricerules['action'])
            && $pricerules['action']
            && isset($pricerules['discountAmount'])
            && $pricerules['discountAmount']
        ) {
            $product->setDiscountApplied(true);
        }


        $product->unsFinalPrice();
        if($this->productMetadata->getVersion()<'2.2.0') {
            $product->cleanCache();
        }

        if ($useCustomerPrice || $customerPrice > $basePrice) {
            $product->setPrice($store->roundPrice($customerPrice, $roundingDecimals));
        }
        if (!$useCustomerPrice && $customerPrice > $basePrice) {
            $product->setPrice($store->roundPrice($customerPrice, $roundingDecimals));
            $product->setSpecialPrice($store->roundPrice($customerPrice, $roundingDecimals));
        } else {
            $product->setPrice($store->roundPrice($basePrice, $roundingDecimals));
            $this->registry->unregister('special_price_from_msq');
            if ($product->getTypeId() == 'bundle') {
                $this->registry->register('special_price_from_msq', '1');
            }
            $product->setSpecialPrice($store->roundPrice($customerPrice, $roundingDecimals));
        }

        if (!$allowPriceRules || !($finalPrice = $rule->calcProductPriceRule($product, $customerPrice))) {
            $finalPrice = $customerPrice;
        }

        if ($useCustomerPrice || $finalPrice > $basePrice) {
            $product->setPrice($store->roundPrice($finalPrice, $roundingDecimals));
            if($product->getTypeId() === 'configurable' && (($finalPrice < $basePrice) || $basePrice == 0)){
                $product->setSpecialPrice($store->roundPrice($finalPrice, $roundingDecimals));
            }
        } else {
            $product->setPrice($store->roundPrice($basePrice, $roundingDecimals));
            $this->registry->unregister('special_price_from_msq');
            if ($product->getTypeId() == 'bundle') {
                $this->registry->register('special_price_from_msq', '1');
            }
            $product->setSpecialPrice($store->roundPrice($finalPrice, $roundingDecimals));
            if ($product->getTypeId() == 'configurable') {
                $specialPrice = $this->processConfigurablePrice($product);
                $product->setSpecialPrice($specialPrice);
                $product->setRegularPrice($store->roundPrice($finalPrice, $roundingDecimals));
            }
        }

        if ($finalPrice == $minimalPrice) {
            $minimalPrice = '';
        }

        if($allowPriceRules && isset($pricerules['action']) && !empty($pricerules['action'])) {
            $product->setCatalogRulePrice($store->roundPrice($finalPrice, $roundingDecimals));
        }

        $product->setBasePrice($store->roundPrice($basePrice, $roundingDecimals));
        $product->setFinalPrice($store->roundPrice($finalPrice, $roundingDecimals));
        $product->setMinPrice($store->roundPrice($minimalPrice, $roundingDecimals));
        $product->setMinimalPrice($store->roundPrice($minimalPrice, $roundingDecimals));
        $product->setCustomerPrice($store->roundPrice($customerPrice, $roundingDecimals));
        $product->setTierPrice($tierPricing);

        $set = false;
        if (!$this->registry->registry('msq-processing')) {
            $this->registry->register('msq-processing', true);
            $set = true;
        }
        $product->getFinalPrice();
        if ($set) {
            $this->registry->unregister('msq-processing');
        }

        $product->setTierPrice($tierPricing);

        if ($this->productMetadata->getVersion() < '2.2.0') {
            $product->cleanCache();
            $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $product]);
        }
    }

    /**
     * Process MSQ Array and breaks and returns Magento tierPrices
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param \Epicor\Common\Model\Xmlvarien $priceBreaks
     * @param float $customerPrice
     * @param float $minimalPrice
     * @param int   $roundingDecimals
     * @param bool  $allowPriceRules
     */
    public function getTierPricingArray($product, $priceBreaks, &$customerPrice, &$minimalPrice, $roundingDecimals = 4, $allowPriceRules = true)
    {
        $store = $this->storeManager->getStore();
        /* @var $store Epicor_Comm_Model_Store */

        $rule = $this->catalogRuleRuleFactory();
        /* @var $rule Mage_Catalogrule_Model_Rule */

        $tierPricing = array();
        if (!isset($priceBreaks) &&
                !isset($priceBreaks['break']) ) {
            return $tierPricing;
        }

        $breaks = $priceBreaks;
        $breaksArray = (isset($breaks['break']))?$breaks['break']:[];
        if(!empty($breaksArray) && !isset($breaksArray[0])){
            $temp = $breaksArray;
            $breaksArray = [];
            $breaksArray[0] = $temp;
        }

        foreach ($breaksArray as $break) {
            if (is_array($break['price']) && empty($break['price'])) {
                continue;
            }
            $breakPrice = $break['price'];
            $breakQty = $break['quantity'];
            if (!$allowPriceRules || !($promoPrice = $rule->calcProductPriceRule($product, $breakPrice))) {
                $promoPrice = $breakPrice;
            }
            if ($breakQty > 1) {
                $tierPricing[] = array(
                    'website_id' => 0,
                    'cust_group' => 32000, // All groups
                    'price_qty' => $breakQty,
                    'price' => $store->roundPrice($promoPrice, $roundingDecimals),
                    'website_price' => $store->roundPrice($promoPrice, $roundingDecimals),
                );
            } elseif ($breakQty == 1) {
                $customerPrice = $breakPrice;
            }
            $minimalPrice = min($minimalPrice, $promoPrice);
        }
        return $tierPricing;
    }

    /**
     * Process MSQ Objects breaks and returns Magento tierPrices
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param \Epicor\Common\Model\Xmlvarien $priceBreaks
     * @param float $customerPrice
     * @param float $minimalPrice
     * @param int   $roundingDecimals
     * @param bool  $allowPriceRules
     */

    public function getTierPricing($product, $priceBreaks, &$customerPrice, &$minimalPrice, $roundingDecimals = 4, $allowPriceRules = true)
    {
        $store = $this->storeManager->getStore();
        /* @var $store Epicor_Comm_Model_Store */

        $rule = $this->catalogRuleRuleFactory->create();
        /* @var $rule Mage_Catalogrule_Model_Rule */

        $tierPricing = array();
        if (!($priceBreaks instanceof \Magento\Framework\DataObject) || (!($priceBreaks->getBreak() instanceof \Magento\Framework\DataObject) && !is_array($priceBreaks->getBreak()))) {
            return $tierPricing;
        }

        $breaks = $priceBreaks->getasarrayBreak();
        foreach ($breaks as $break) {
            $breakPrice = $break->getPrice();
            $breakQty = $break->getQuantity();
            if (!$allowPriceRules || !($promoPrice = $rule->calcProductPriceRule($product, $breakPrice))) {
                $promoPrice = $breakPrice;
            }
            if ($breakQty > 1) {
                $tierPricing[] = array(
                    'website_id' => 0,
                    'cust_group' => 32000, // All groups
                    'price_qty' => $breakQty,
                    'price' => $store->roundPrice($promoPrice, $roundingDecimals),
                    'website_price' => $store->roundPrice($promoPrice, $roundingDecimals),
                );
            } elseif ($breakQty == 1) {
                $customerPrice = $breakPrice;
            }
            $minimalPrice = min($minimalPrice, $promoPrice);
        }

        return $tierPricing;
    }


    public function catalogRuleResourceModelRuleFactory()
    {
        if (!$this->catalogRuleResourceModelRuleFactoryExist) {
            $this->catalogRuleResourceModelRuleFactoryExist = $this->catalogRuleResourceModelRuleFactory->create();
        }
        return $this->catalogRuleResourceModelRuleFactoryExist;
    }

    /* Get actions using catalog price rule of product
     *
     * @param Mage_Catalog_Model_Product $product
     * @param float $price
     * @return float|null
     */

    public function getRuleActions(\Magento\Catalog\Model\Product $product, $price=0)
    {
        if ($this->getRuleActionsExist === false) {
            $this->getRuleActionsExist = null;
            $productId = $product->getId();
            $storeId = $product->getStoreId();
            $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
            if ($product->hasCustomerGroupId()) {
                $customerGroupId = $product->getCustomerGroupId();
            } else {
                $customerGroupId = $this->customerSessionFactory()->getCustomerGroupId();
            }
            //M1 > M2 Translation Begin (Rule p2-6.4)
            //$dateTs = Mage::app()->getLocale()->date()->getTimestamp();
            $dateTs = $this->timezone->date()->getTimestamp();
            //M1 > M2 Translation End
            $discount = array();

            $rulesData = $this->catalogRuleResourceModelRuleFactory()
                ->getRulesFromProduct($dateTs, $websiteId, $customerGroupId, $productId);
            foreach ($rulesData as $ruleData) {
                $discount['action'] = $ruleData['action_operator'];
                $discount['discountAmount'] = $ruleData['action_amount'];
                $this->getRuleActionsExist =  $discount;
            }
        }
        return $this->getRuleActionsExist;
    }


    /**
     * Gets Correct Qty that can be added to cart
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param int $qty
     * @param bool $locEnabled
     * @param string $locationCode
     * @param bool $update
     * @return array
     */
    public function getCorrectOrderQty($product, $qty, $locEnabled = false, $locationCode = NULL, $update = false, $quote = false)
    {
        $qtyValidator = $this->scopeConfig->isSetFlag('cataloginventory/options/enable_qty_validator',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!$qtyValidator) {
            return array('qty' => $qty, 'message' => '');
        }
        $quoteQty = $this->_localeFormat->getNumber(0);
        $qty = $this->_localeFormat->getNumber($qty);
        $productId = $product->getId();
        if(!$quote){
            $quote = $this->checkoutSessionFactory->create()->getQuote();
        }
        if (isset($locationCode) && $locationCode != "" && $product->getLocation($locationCode)) {
            $minQty = $product->getLocation($locationCode)->getData('minimum_order_qty');
            $maxQty = $product->getLocation($locationCode)->getData('maximum_order_qty');
            $hasProductQut = $quote->hasProductQty($productId, $locationCode);
            if ($hasProductQut && !$update) {
                $quoteQty = $hasProductQut;
            }
        }
        $newQty = $qty + $quoteQty;

        $message = "";
        switch (true) {
            case ($locEnabled && isset($minQty) && ($newQty > 0) && ($minQty > 0) && ($newQty < $minQty)):
                $qty = $minQty;
                $message = __('The order quantity for %1 is below the minimum. The order quantity has been increased to the minimum allowed.', $product->getName());
                break;
            case ($locEnabled && isset($maxQty) && ($newQty > 0) && ($maxQty > 0) && ($newQty > $maxQty)):
                $qty = $maxQty - $quoteQty;
                $message = __('The order quantity for %1 is greater than the maximum allowed. The order quantity has been decreased to the maximum allowed.', $product->getName());
                break;
        }
        return array('qty' => $qty, 'message' => $message);
    }

    public function getDealerPrice($product, $qty = 1)
    {
        $price = $product->getPrice();

        $tierPrice = $product->getTierPrice($qty);

        if (is_array($tierPrice) && !empty($tierPrice) && isset($tierPrice[0]['website_price'])) {
            $tierPrice = $tierPrice[0]['website_price'];
        }

        if (!is_null($tierPrice)) {
            $price = $price > 0 ? min($price, $tierPrice) : $tierPrice;
        }
        return $price;
    }

    /**
     * Lazy Load
     * get Products collection
     *
     * @param array $productIds
     * @param string $type
     * @return Collection
     */
    public function getProductCollectionByIds($productIds = array(), $type = "product_list")
    {
        $collection = $this->catalogResourceModelProductCollectionFactory->create();
        $collection->addAttributeToFilter('entity_id', ['in' => $productIds]);
        $collection->addAttributeToSelect('*');
        //$collection->addAttributeToSelect(['ecc_default_uom', 'ecc_configurator']);
        if($type == "compare"){
            $type = "compare_products";
        } else if (in_array($type, ["bestseller_product", "featured_product","newsale_product"])) {
            $type = $type;
        } else if($type != "product_list") {
            $type = "linked_products_" . $type;
        }
        $this->commMessagingHelper->sendMsq($collection, $type);

        return $collection;
    }

    /**
     * Lazy Load
     *
     * @param string $viewType
     * @return boolean
     */
    public function isLazyLoad($viewType = "list") {
        $isMsqEnableForView = false;
        $isFPC = $this->_cacheState->isEnabled(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER);
        switch ($viewType) {
            case "list":
                $isMsqEnableForView = $this->scopeConfig->getValue('epicor_comm_enabled_messages/msq_request/triggers_product_list', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                break;
            case "view":
                $isMsqEnableForView = $this->scopeConfig->getValue('epicor_comm_enabled_messages/msq_request/triggers_product_details', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                break;
        }
        $isLazyload = $this->scopeConfig->getValue('catalog/lazy_load/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $isMsqEnable = $this->scopeConfig->getValue('epicor_comm_enabled_messages/msq_request/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (($isLazyload && $isMsqEnable && $isMsqEnableForView) || $isFPC) {
            return true;
        }
        return false;
    }

    /**
     * lazy load
     * Remove element for PDP page when load page
     * And same element load from AJAX.
     *
     * @return string
     */

    public function getElemetRemovelist() {
        $elementList = array(
            "product.info.price",
            "customize.button",
            "product.price.tier",
            "product.info",
            "product.info.extrahint",
            "product.info.overview",
            "bundle.options.container"
        );
        return $elementList;
    }

    /*
     * method added for WSO-6196
     * GetAll list of products which creation went wrong for group product
     */

    public function getAllGroupedProductListToDelete($delete = true, $team = true) {
        try {
            $productchecklog = new \Zend\Log\Writer\Stream(BP . '/var/log/productcheck.log');
            $productchecklogger = new \Zend\Log\Logger();
            $productchecklogger->addWriter($productchecklog);
            $deletedProductlog = new \Zend\Log\Writer\Stream(BP . '/var/log/DeletedProduct.log');
            $deletedProductlogger = new \Zend\Log\Logger();
            $deletedProductlogger->addWriter($deletedProductlog);

            $productchecklogger->info(__('Starting to check for duplicate group products...'));

            $productIdList = array();
            $csvData = array();
            $firstline = array();
            $resource = $this->resourceConnection;
            $connection = $resource->getConnection();
            /* start query to read parent sku and child sku same retrieve only those entries */
            $sqlQuery = "SELECT
    parent.entity_id as parent_id,link.child_id,
    parent.type_id AS parent_product_type,
    parent.sku AS parent_sk,
    child.type_id AS child_product_type,
    child.sku,
    substring_index(child.sku,concat(parent.sku,'-'),-1) as isNumber

FROM
    catalog_product_entity AS parent
        JOIN
    catalog_product_relation AS link ON link.parent_id = parent.entity_id
        JOIN
    catalog_product_entity AS child ON child.entity_id = link.child_id
    where child.sku like concat(parent.sku,'-%')
    and substring_index(child.sku,concat(parent.sku,'-'),-1) REGEXP '^[0-9]+$'";
            $collectionData = $connection->fetchAll($sqlQuery);
            /* end query to read parent sku and child sku same retrieve only those entries */
            /* loop though and create list of array with productids to delete */
            if ($collectionData) {
                $deletelog = $this->directoryList->getPath('var') . '/log/DeletedProduct.log';
                if (file_exists($deletelog)) {
                    chmod($deletelog, 0777);
                }
                $productchecklog = $this->directoryList->getPath('var') . '/log/productcheck.log';
                if (file_exists($productchecklog)) {
                    chmod($productchecklog, 0777);
                }
                foreach ($collectionData as $coll) {
                    $productIdList[] = $coll['parent_id'];
                    $productIdList[] = $coll['child_id'];
                }
                foreach (array_unique(array_values($productIdList)) as $id) {
                    $product = $this->catalogProductFactory->create()->load($id);
                    if ($product->getEccStkType()) {
                        $pid = $product->getId();
                        $sku = $product->getSku();
                        $type = $product->getTypeId();
                        $name = $product->getName();
                        $uom = $product->getEccUom();
                        $csvData[] = array('PID' => $pid,
                            'SKU' => $sku,
                            'ProductType' => $type,
                            'Name:' => $name,
                            'Uom' => $uom
                        );

                        if ($delete) {
                            $product->delete();
                            $deletedProductlogger->info(__("Product Id $pid : Successfully deleted SKU $sku $uom"));
                        } else {
                            $productchecklogger->info(__("Processing product id $pid, $sku, $uom"));
                        }
                    }
                }

                $path = $this->directoryList->getPath('var') . DIRECTORY_SEPARATOR . 'log';
                $ioFile = $this->iosystem;
                $ioFile->checkAndCreateFolder($path);
                $date = date('ymdhms');
                $prefix = 'WSO-6196-' . $date;
                $deleteLogFileName = 'DeletedProduct-' . $date . '.log';
                if ($delete) {
                    $name = 'WSO-6196-DeletedProduct-' . $date;
                } else {
                    $name = $prefix;
                }
                $file = $path . DIRECTORY_SEPARATOR . $name . '.csv';
                $var_csv = $this->csvProcessor;
                if ($delete) {
                    $productchecklogger->info(__(count($csvData) . ' products deleted'));
                } else {
                    $productchecklogger->info(__(count($csvData) . ' products deleted'));
                }
                if ($csvData) {
                    if ($delete) {
                        $productchecklogger->info(__('Creating Log File…'));
                    } else {
                        $productchecklogger->info(__('Starting to import data to csv file…'));
                    }
                    $firstline[] = array('pid' => 'Product Id', 'sku' => 'SKU/ProductCode', 'PT' => 'Product Type', 'name' => 'Product Name', 'UOM' => 'Product UOM');
                    $csvData = array_merge($firstline, $csvData);
                }
                if ($delete) {
                    $deletedProductlogdate = new \Zend\Log\Writer\Stream(BP . '/var/log/' . 'DeletedProduct-' . $date . '.log');
                    $deletedProductloggerdate = new \Zend\Log\Logger();
                    $deletedProductloggerdate->addWriter($deletedProductlog);
                    $deletedProductloggerdate->info('');
                    $fileNameDeleteread = $this->directoryList->getPath('var') . '/log/DeletedProduct.log';
                    $fileNameDeleteWrite = $this->directoryList->getPath('var') . '/log/DeletedProduct-' . $date . '.log';
                    $readcontent = file_get_contents($fileNameDeleteread);
                    file_put_contents($fileNameDeleteWrite, $readcontent);
                    $productchecklogger->info(__("Successfully created the log file.. Go to Epicor > Advanced > System Logs and view $fileNameDeleteWrite"));
                } else {
                    $productchecklogger->info(__("Successfully created the csv file.. Go to Epicor > Advanced > System Logs to download $file"));
                }
                $var_csv->setEnclosure('"')->setDelimiter(',')->saveData($file, $csvData);
            } else {
                $firstline = array();
                $csvData = array();
                $path = $this->directoryList->getPath('var') . DIRECTORY_SEPARATOR . 'log';
                $ioFile = $this->iosystem;
                $ioFile->checkAndCreateFolder($path);
                $date = date('ymdhms');
                $prefix = 'WSO-6196-' . $date;
                $deleteLogFileName = 'DeletedProduct-' . $date . '.log';
                if ($delete) {
                    $name = 'WSO-6196-DeletedProduct-' . $date;
                } else {
                    $name = $prefix;
                }
                $file = $path . DIRECTORY_SEPARATOR . $name . '.csv';
                $var_csv = $this->csvProcessor;
                if ($delete) {
                    $deletedProductlogger->info(__(' *** No records found to delete'));
                    $fileNameDeleteread = $this->directoryList->getPath('var') . '/log/DeletedProduct.log';
                    $fileNameDeleteWrite = $this->directoryList->getPath('var') . '/log/DeletedProduct-' . $date . '.log';
                    $readcontent = file_get_contents($fileNameDeleteread);
                    file_put_contents($fileNameDeleteWrite, $readcontent);
                } else {
                    $firstline[] = array('pid' => 'Product Id', 'sku' => 'SKU/ProductCode', 'PT' => 'Product Type', 'name' => 'Product Name', 'UOM' => 'Product UOM', '000' => '000', ' ' => "No Products Found");
                    $csvData = array_merge($firstline, $csvData);
                    $var_csv->setEnclosure('"')->setDelimiter(',')->saveData($file, $csvData);
                }
            }
            $productchecklogger->info(__('Processing Complete'));
            if ($this->checkProductCheckLogFileExistOrNot()) {
                $deletelog = $this->directoryList->getPath('var') . '/log/DeletedProduct.log';
                if (file_exists($deletelog)) {
                    unlink($deletelog);
                }
                $productchecklog = $this->directoryList->getPath('var') . '/log/productcheck.log';
                unlink($productchecklog);
            }
            return true;
        } catch (\Exception $e) {
            $faultylog = new \Zend\Log\Writer\Stream(BP . '/var/log/faultyProducts.log');
            $faultyloglogger = new \Zend\Log\Logger();
            $faultyloglogger->addWriter($faultylog);
            $faultyloglogger->info(__("Something went wrong...$e->getMessage()"));
        }
    }

    public function checkProductCheckLogFileExistOrNot() {
        $productchecklog = $this->directoryList->getPath('log') . '/productcheck.log';
        if (file_exists($productchecklog)) {
            return true;
        } else {
            return false;
        }
    }

    public function getAdminSession() {
        $connection = $this->resourceConnection->getConnection('core_read');
        $select = $connection->select()->from('admin_user_session')->where('ip=?', $_SERVER['REMOTE_ADDR'])->order('id desc');
        $adminsession = $connection->fetchRow($select);
        return $adminsession;
    }

    public function getAttributeGroupId() {
        $attrGroupIdValue = null;
        $connection = $this->resourceConnection->getConnection('core_read');
        $select = $connection->select()->from('eav_entity_attribute'); //->where('attribute_id='.$attrId.' AND attribute_set_id='.$setId);
        $attrGroupId = $connection->fetchAll($select);
        foreach ($attrGroupId as $attrGroup) {
            $attrGroupIdValue[$attrGroup['attribute_id'] . 'setId' . $attrGroup['attribute_set_id']] = $attrGroup['attribute_group_id']; //$attrGroupId;
        }
        return $attrGroupIdValue;
    }

    public function getAllProductAttribute() {
        $attribute = [];
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $attributeRepository = $this->attributeRepository->getList(
                'catalog_product', $searchCriteria
        );

        foreach ($attributeRepository->getItems() as $items) {
            $attribute[$items->getAttributeCode()] = $items->getAttributeId();
        }
        return $attribute;
    }


    /**
     * Get Products collection for widgets.
     *
     * @param array  $productIds Product IDs.
     * @param string $type       View type.
     *
     * @return Collection
     */
    public function getWidgetCollectionByIds($productIds=[], $type='product_list')
    {
        $collection = $this->catalogResourceModelProductCollectionFactory->create();
        $collection->addAttributeToFilter('entity_id', ['in' => $productIds]);
        $collection->addAttributeToSelect('*');
        $select = $collection->getSelect();
        $select->order(new \Zend_Db_Expr('FIELD(entity_id, '.implode(',', $productIds).')'));
        $this->commMessagingHelper->sendMsq($collection, $type);

        return $collection;

    }//end getWidgetCollectionByIds()

    /**
     * Calculate Special price for configurable product
     * @param $product
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function processConfigurablePrice($product)
    {
        $rule = $this->catalogRuleRuleFactory();
        $price = $product->getPrice();
        $special = $product->getSpecialPrice();
        $alreadyApplied =  $product->getStaticLocationPrice();
        if (!is_null($special)) {
            $price = min($price, $special);
        }
        $rulePrice =false;
        if(!$alreadyApplied) {
            if ($pricingSku = $product->getEccPricingSku()) {
                $pricingProdId = $product->getIdBySku($pricingSku);
                $pricingProd = $this->catalogProductFactory->create()->load($pricingProdId);
                $price = $pricingProd->getPrice();
                $rulePrice = $rule->calcProductPriceRule($pricingProd, $price);
            } else {
                $rulePrice = $rule->calcProductPriceRule($product, $price);
            }
        }
        if (!$rulePrice) {
            $rulePrice = $price;
        }
        $store = $this->storeManager->getStore();
        /* @var $store Epicor_Comm_Model_Store */

        $rulePrice = $store->roundPrice($rulePrice, 4);
        return $rulePrice;
    }
}
