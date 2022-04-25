<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\Message\Request;


use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Request MSQ - Multiple Stock Query
 *
 * Get the current stock information for the requested products
 *
 *
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 *
 * @method setSaveProductDetails()
 * @method setDisallowNonErpProducts()
 * @method setStockOnly(boolean)
 * @method setTrigger(string)
 *
 * @method boolean getAllowPriceRules()
 * @method setAllowPriceRules(boolean $allow)
 *
 * @method boolean getLocations()
 * @method setLocations(array $location)
 */
class Msq extends \Epicor\Comm\Model\Message\Request
{


    /**
     * Message Type
     */
    const MESSAGE_TYPE = 'MSQ';
    const CURRENCIES_UPDATE = 'epicor_comm_enabled_messages/msq_request/currencies_update';
    const LEAD_TIME_DAYS_UPDATE = 'epicor_comm_enabled_messages/msq_request/lead_time_days_update';
    const LEAD_TIME_TEXT_UPDATE = 'epicor_comm_enabled_messages/msq_request/lead_time_text_update';
    const FREE_STOCK_UPDATE = 'epicor_comm_enabled_messages/msq_request/free_stock_update';
    const PRODUCT_MANAGE_STOCK_UPDATE = 'epicor_comm_enabled_messages/msq_request/product_manage_stock_update';
    const PRODUCT_MAX_ORDER_QTY_UPDATE = 'epicor_comm_enabled_messages/msq_request/product_max_order_qty_update';
    const PRODUCT_MIN_ORDER_QTY_UPDATE = 'epicor_comm_enabled_messages/msq_request/product_min_order_qty_update';
    const LOCATIONS_UPDATE = 'epicor_comm_enabled_messages/msq_request/locations_update';
    //location pricing is currencies
    const LOCATION_PRICING = 'epicor_comm_enabled_messages/msq_request/location_pricing';
    const LOCATION_LEAD_TIME_DAYS = 'epicor_comm_enabled_messages/msq_request/location_lead_time_days';
    const LOCATION_LEAD_TIME_TEXT = 'epicor_comm_enabled_messages/msq_request/location_lead_time_text';
    const LOCATION_FREE_STOCK = 'epicor_comm_enabled_messages/msq_request/location_free_stock';
    const LOCATION_PRODUCT_MANAGE_STOCK = 'epicor_comm_enabled_messages/msq_request/location_product_manage_stock';
    const LOCATION_MAX_ORDER_QTY = 'epicor_comm_enabled_messages/msq_request/location_maximum_order_qty';
    const LOCATION_MIN_ORDER_QTY = 'epicor_comm_enabled_messages/msq_request/location_minimum_order_qty';
    const LOCATION_SUPPLIER_BRAND = 'epicor_comm_enabled_messages/msq_request/location_supplier_brand';
    const LOCATION_TAX_CODE = 'epicor_comm_enabled_messages/msq_request/location_tax_code';
    const LOCATION_MANUFACTURERS = 'epicor_comm_enabled_messages/msq_request/location_manufacturers';
    const LOCATION_STOCK_STATUS = 'epicor_comm_enabled_messages/msq_request/location_stock_status';



    protected $_productSkus = array();
    protected $_products = array();
    protected $_locations = [];
    protected $_cachedProductSkus = array();
    protected $_cachedProducts = array();
    protected $_productsCache;
    protected $_uomSeparator;
    protected $_keys;
    protected $_locationStockLevels = array();
    protected static $_priceRulesData = array();

    protected $catalogResourceModelProductFactoryExist = null;
    protected $catalogRuleRuleFactoryExist = null;
    protected $groupProcessed = [];

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\ProductFactory
     */
    protected $catalogResourceModelProductFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Epicor\Common\Helper\Messaging\Cache
     */
    protected $commonMessagingCacheHelper;

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\ItemFactory
     */
    protected $catalogInventoryStockItemFactory;

    /**
     * @var \Epicor\Comm\Model\Location\Product\CurrencyFactory
     */
    protected $commLocationProductCurrencyFactory;

    /**
     * @var \Magento\CatalogRule\Model\RuleFactory
     */
    protected $catalogRuleRuleFactory;

    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\RuleFactory
     */
    protected $catalogRuleResourceModelRuleFactory;

    /**
     * @var \Epicor\Common\Model\XmlvarienFactory
     */
    protected $commonXmlvarienFactory;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $catalogInventoryApiStockRegistryInterface;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\ActionFactory
     */
    protected $catalogResourceModelProductActionFactory;

    /**
     * @var \Epicor\SalesRep\Helper\Pricing\Rule\Product
     */
    protected $salesRepPricingRuleProductHelper;


    protected $arpaymentsHelper;

    /**
     * \Epicor\Comm\Model\IndexerFactory
     */
    protected $commIndexerFactory;

    /**
     * @var \Magento\Customer\Api\GroupManagementInterface
     */
    protected $_groupManagement;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPool;

    /**
     * @var \Epicor\BranchPickup\Helper\DataFactory
     */
    protected $branchPickupHelperFactory;

    /**
     * @var \Epicor\Comm\Model\Location\Relatedlocations
     */
    protected $relatedLocations;

    /**
     * @var \Epicor\Comm\Model\Location\Groups
     */
    protected $locationGroups;

    /**
     * @var int
     */
    private $pricePrecision;

    /**
     * @var mixed
     */
    private $currentBranch;
    /**
     * @var \Epicor\Comm\Model\Location\Product\Currency\CollectionFactory
     */
    private $commLocationProductCurrencyCollectionFactory;

    /**
     * @var array
     */
    private $locationscache = [];

    /**
     * @var boolean
     */
    private $locationsEnabled;

    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Epicor\SalesRep\Helper\Pricing\Rule\Product $salesRepPricingRuleProductHelper,
        \Epicor\Customerconnect\Helper\Arpayments $arpaymentsHelper,
        \Magento\Catalog\Model\ResourceModel\Product\ActionFactory $catalogResourceModelProductActionFactory,
        \Epicor\Common\Model\XmlvarienFactory $commonXmlvarienFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $catalogResourceModelProductFactory,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Common\Helper\Messaging\Cache $commonMessagingCacheHelper,
        \Magento\CatalogRule\Model\RuleFactory $catalogRuleRuleFactory,
        \Magento\CatalogInventory\Model\Stock\ItemFactory $catalogInventoryStockItemFactory,
        \Epicor\Comm\Model\Location\Product\CurrencyFactory $commLocationProductCurrencyFactory,
        \Magento\CatalogRule\Model\ResourceModel\RuleFactory $catalogRuleResourceModelRuleFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $catalogInventoryApiStockRegistryInterface,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Epicor\BranchPickup\Helper\DataFactory $branchPickupHelperFactory,
        \Epicor\Comm\Model\Location\Relatedlocations $relatedLocations,
        \Epicor\Comm\Model\Location\Groups $locationGroups,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Epicor\Comm\Model\ResourceModel\Location\Product\Currency\CollectionFactory $commLocationProductCurrencyCollectionFactory,
        array $data = [])
    {
        $this->commonXmlvarienFactory = $commonXmlvarienFactory;
        $this->commonHelper =$commonHelper;
        $this->registry = $context->getRegistry();
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->catalogResourceModelProductFactory = $catalogResourceModelProductFactory;
        $this->catalogProductFactory = $context->getCatalogProductFactory();
        $this->scopeConfig = $context->getScopeConfig();
        $this->commMessagingHelper = $commMessagingHelper;
        $this->commonMessagingCacheHelper = $commonMessagingCacheHelper;
        $this->commProductHelper = $context->getCommProductHelper();
        $this->catalogInventoryStockItemFactory = $catalogInventoryStockItemFactory;
        $this->commLocationProductCurrencyFactory = $commLocationProductCurrencyFactory;
        $this->catalogProductFactory = $context->getCatalogProductFactory();
        $this->catalogRuleRuleFactory = $catalogRuleRuleFactory;
        $this->catalogRuleResourceModelRuleFactory = $catalogRuleResourceModelRuleFactory;
        $this->catalogInventoryApiStockRegistryInterface=$catalogInventoryApiStockRegistryInterface;
        $this->_localeResolver = $localeResolver;
        $this->catalogResourceModelProductActionFactory = $catalogResourceModelProductActionFactory;
        $this->salesRepPricingRuleProductHelper = $salesRepPricingRuleProductHelper;
        $this->arpaymentsHelper = $arpaymentsHelper;
        $this->commIndexerFactory = $context->getCommIndexerFactory();
        $this->_groupManagement = $groupManagement;
        $this->branchPickupHelperFactory = $branchPickupHelperFactory->create();
        $this->relatedLocations = $relatedLocations;
        $this->locationGroups = $locationGroups;
        $this->pricePrecision  = $this->commMessagingHelper->getProductPricePrecision();
        $this->commLocationProductCurrencyCollectionFactory = $commLocationProductCurrencyCollectionFactory;
        parent::__construct($context, $resource, $resourceCollection, $data);

        $this->setMessageType(self::MESSAGE_TYPE);
        $this->setLicenseType(array('Consumer', 'Customer'));
        $this->setConfigBase('epicor_comm_enabled_messages/msq_request/');
        $this->_products = array();
        $this->_uomSeparator = $this->commonHelper->getUOMSeparator();
        $this->setAllowPriceRules(true);
//        $this->setUrl('http://hvw7z01.playground.local/2012RTest/ecc.svc/ecc');
        $this->registry->register('entity_register_update_product', true, true);
        $this->setCurrencies(array());
        $this->setLocations(array());

    }

    /**
     * Creates an array of cache keys for the message
     *
     * @return array
     */
    private function _getCacheKeys()
    {
        if (empty($this->_keys)) {
            $this->_keys = array();

            $this->_keys[] = $this->getAccountNumber(true);

            $brandKey = $this->_brand->getCompany()
                . $this->_brand->getSite()
                . $this->_brand->getWarehouse()
                . $this->_brand->getGroup();

            if (!empty($brandKey)) {
                $this->_keys[] = $brandKey;
            }

            $currencies = $this->getCurrencies();

            if (empty($currencies)) {
                $currencies = array($this->getHelper()->getCurrencyMapping());
            }

            $currenciesKey = implode('', $currencies);

            if (!empty($currenciesKey)) {
                $this->_keys[] = $currenciesKey;
            }

            $contractHelper = $this->listsFrontendContractHelper;
            /* @var $contractHelper \Epicor\Lists\Helper\Frontend\Contract */
            $eccSelectedContract = $contractHelper->getSelectedContractCode();
            if ($contractHelper->contractsEnabled() && $eccSelectedContract) {
                $this->_keys[] = $eccSelectedContract;
            }
        }
        return $this->_keys;
    }

    /**
     * Builds cache keys for the product to load / save the cache
     *
     * @param \Epicor\Comm\Model\Product $product
     *
     * @return array
     */
    public function getProductCacheKey($product)
    {
        $attributeKeys = $this->getAttributeCacheKeys($product);
        $keys = array_merge($this->_getCacheKeys(), $attributeKeys);
        $storeId = $this->storeManager->getStore()->getStoreId();
        $productId=$product->getId();
        $pricing_sku = $this->catalogResourceModelProductFactory()
            ->getAttributeRawValue($productId, 'ecc_pricing_sku', $storeId);
        $sku = ($pricing_sku) ? $pricing_sku : $product->getSku();
       // $keys[] = $product->getSku();
        $keys[] = $sku;

        $attributeString = $this->getAttributeString($product);
        if (!empty($attributeString)) {
            $keys[] = $attributeString;
        }

        $contractHelper = $this->listsFrontendContractHelper;
        /* @var $contractHelper \Epicor\Lists\Helper\Frontend\Contract */
        if ($contractHelper->contractsEnabled() && $product->getEccContracts()) {
            $contracts = (array) $product->getEccContracts();
            $keys[] = implode('|', $contracts);
        }

        return $keys;
    }

    /**
     * Add Locattions to the MSQ request
     *
     * @param array $locationss
     */
    public function addLocations($locations)
    {
        if (!$this->getLocationsEnabled()) {
            return;
        }

        if (is_string($locations)) {
            $locations = [$locations];
        }
        foreach ($locations as $location) {
            if (!isset($this->_locations[$location])) {
                $this->_locations[$location] = $location;
            }
        }

    }

    /**
     * Adds products to the MSQ request
     *
     * @param array $products
     * @param bool $useCache
     */
    public function addProducts($products, $useCache = true)
    {
        foreach ($products as $product) {
            /* @var $product \Epicor\Comm\Model\Product */
            $this->addProduct($product, $product->getQty(), $useCache);
        }
    }

    public function catalogResourceModelProductFactory()
    {
        if (!$this->catalogResourceModelProductFactoryExist) {
            $this->catalogResourceModelProductFactoryExist = $this->catalogResourceModelProductFactory->create();
        }
        return $this->catalogResourceModelProductFactoryExist;
    }

    /**
     * Adds a product to the MSQ request
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param int $rawQty
     * @param bool $useCache
     */
    public function addProduct($product, $rawQty = 1, $useCache = true)
    {

        $arPaymentsPage = $this->arpaymentsHelper->checkArpaymentsPage();
        if($arPaymentsPage) {
            return false;
        }


        $storeId = $this->storeManager->getStore()->getStoreId();
        $stkType = true;
        // if flat products on and 'use in product listing' set to no, the sku will not be included in the collection, so needs to be pulled in separately
        if ($product->getSku()=="") {
            $product->setSku($this->catalogResourceModelProductFactory()->getAttributeRawValue($product->getId(), 'sku', $storeId));
        }
        $sku = $product->getSku();
        $pricing_sku = null;
        $productId = $product->getId();
        if($productId) {
            $stkType = $product->getEccStkType();
            $pricing_sku = $product->getEccPricingSku();
            if(!$stkType) {
                $stkType = $this->catalogResourceModelProductFactory()->getAttributeRawValue($productId, 'ecc_stk_type', $storeId);
            }
            if($pricing_sku === false){
              $pricing_sku = $this->catalogResourceModelProductFactory()->getAttributeRawValue($productId, 'ecc_pricing_sku', $storeId);
            }
            if (!is_null($pricing_sku)) {
                $sku = $pricing_sku;
            }
        }
        //Send Non-ERP products in MSQ - If the product was created in Magento
        $msqForNonErp = $this->scopeConfig->isSetFlag('epicor_comm_enabled_messages/msq_request/msq_for_non_erp_products', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ((!$msqForNonErp) && (!$stkType) && (!$pricing_sku) ) {
            return;
        }
        $contractHelper = $this->listsFrontendContractHelper;
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */
        if ($product->getEccContracts() == false) {
            $eccSelectedContract = $contractHelper->getSelectedContractCode();
            $contracts = array();
            if ($eccSelectedContract) {
                $contracts[] = $eccSelectedContract;
            }
            $product->setEccContracts($contracts);
        }

        $helper = $this->getHelper();
        $decimalPlaces = $helper->getDecimalPlaces($product);
        $roundedQty = $helper->qtyRounding($rawQty, $decimalPlaces);
        $product->setData('msq_quantity', $roundedQty);
        $key = $sku . $helper->getUOMSeparator() . $this->getAttributeString($product);
        if ($useCache) {
            if (!isset($this->_productsCache[$key])) {
                $cacheKeys = $this->getProductCacheKey($product);
                $cacheProduct = $this->getCachedProducts($product, $cacheKeys, $key);
                if ($cacheProduct) {
                    $cacheKeys = 'MSQ' . '_' . implode('_', $cacheKeys);
                    $product->setMsqRequestCacheKey($cacheKeys);
                    $this->_productsCache[$key] = $cacheProduct;
                }
            }
        }

        if (isset($this->_products[$key])) {
            if (is_object($this->_products[$key])) {
                $oldProduct = $this->_products[$key];
                $this->_products[$key] = array($oldProduct);
            }
            $this->_products[$key][] = $product;
        } else {
            $this->_products[$key] = $product;

            if (isset($this->_productsCache[$key])) {
                $this->_cachedProductSkus[] = $sku;
            } else {
                $this->_productSkus[] = $sku;
            }
        }
    }

    /**
     * Adds a currency to the message
     *
     * @param string|array $currency
     */
    public function addCurrency($newCurrencies)
    {
        $currencies = $this->getCurrencies();

        if (!is_array($newCurrencies)) {
            $newCurrencies = array($newCurrencies);
        }

        foreach ($newCurrencies as $currency) {
            if (!in_array($currency, $currencies)) {
                $currencies[] = $currency;
            }
        }

        $this->setCurrencies($currencies);
    }

    /**
     * Removes a currency from the message
     *
     * @param string|array $currency
     */
    public function removeCurrency($delCurrencies)
    {
        $currencies = $this->getCurrencies();

        if (!is_array($delCurrencies)) {
            $delCurrencies = array($delCurrencies);
        }

        foreach ($delCurrencies as $currency) {
            if (in_array($currency, $currencies)) {
                unset($currencies[array_search($currency, $currencies)]);
            }
        }

        $this->setCurrencies($currencies);
    }

    /**
     * Create a request
     *
     * @param array $data
     * @return
     */
    function buildRequest()
    {

        // Get ERP code (accountNumber)
        $helper = $this->getHelper();
        $accountNumber = $this->getAccountNumber();
        $hidePrice = $helper->getEccHidePrice();
        $showDefault = $hidePrice && $hidePrice == 2;
        $isPunchout  = $this->getIsPunchout();
        $shippingAddress = 'default';
        if($showDefault){
            $storeId = $this->storeManager->getStore()->getStoreId();
            $erpAccountId = $this->scopeConfig->getValue(
                'customer/create_account/default_erpaccount',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $accountNumber = $helper->getDefaultAccount($erpAccountId);
            $shippingAddress = $helper->getDefaultShippingAddress($erpAccountId);
        } else if ($isPunchout && $this->getCustomerObj()) {
            $shippingAddress = $this->getCustomerObj()->getDefaultShippingAddress();
        }
        $productSkus = str_replace($helper->getUOMSeparator(), ' - ', implode(', ', $this->_productSkus));
        $cachedSkus = str_replace($helper->getUOMSeparator(), ' - ', implode(', ', $this->_cachedProductSkus));

        $subject = 'Total Products: ' . (count($this->_productSkus) + count($this->_cachedProductSkus)) . "\n"
            . 'Requested: <a title="SKUs requested from ERP: ' . $productSkus . '">' . count($this->_productSkus) . "</a>\n"
            . 'Cached: <a title="SKUs loaded from cache: ' . $cachedSkus . '">' . count($this->_cachedProductSkus) . '</a>' . "\n"
            . 'Trigger: ' . str_replace('_', ' ', $this->getTrigger());

        $this->setMessageSecondarySubject($subject);

        if ($accountNumber) {


//            $shippingAddress = $helper->formatAddress(' ','shipping');
//            $customer = Mage::getSingleton('customer/session')->getCustomer();
//            /* @var $customer Mage_Customer_Model_Customer */
//            $shippingAddress = $customer->getDefaultShippingAddress();

            $requestProducts = array();
            $cachedProducts = array();
            $tmpLocations = array();
            foreach ($this->_products as $key => $product) {

                if (is_array($product)) {
                    $product = $product[0];
                }

                $productRequest = $this->buildProductRequest($product);

                if (isset($this->_productsCache[$key])) {
                    $productRequest['cacheKey'] = $product->getMsqRequestCacheKey();
                    $cachedProducts[] = $productRequest;
                } else {
                    $requestProducts[] = $productRequest;
                }

                if ($this->getLocationsEnabled()) {
                    if(!empty($this->_locations)){
                        $tmpLocations = array_merge($tmpLocations, $this->_locations);
                        $tmpLocations = array_unique($tmpLocations);
                    }else{
                        $tmpLocations = array_merge($tmpLocations, $product->getCustomerLocations());
                    }
                }
            }

            if (empty($requestProducts) && empty($cachedProducts)) {
                //return 'No Products requested';
                return false;
            }
            if (count($requestProducts) > 0) {
                $message = $this->getMessageTemplate();
                $addressToSend = ($showDefault || $isPunchout) ? $shippingAddress : 'default';
                $message['messages']['request']['body'] = array_merge($message['messages']['request']['body'], array(
                    'accountNumber' => $accountNumber,
                    'languageCode' => $this->getHelper()->getLanguageMapping($this->scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId())),
                    'currencies' => array(
                        'currency' => $this->_getCurrencies()
                    ),
                    'locations' => $this->_getLocations($tmpLocations),
                    'deliveryAddress' => $helper->formatAddress($addressToSend, 'shipping', $this->getCustomerId()),
//                'deliveryAddress' => $customer_helper->formatCustomerAddress($shippingAddress, true),
                    'parts' => array(
                        '_attributes' => array(
                            'stockOnly' => $this->getStockOnly() ? 'Y' : 'N',
                            'includeQuantityBreaks' => ($this->scopeConfig->isSetFlag('epicor_comm_enabled_messages/msq_request/msq_quantity_breaks', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ? 'Y' : 'N'),
                        ),
                        'part' => $requestProducts
                    ),
                ));
                $this->setOutXml($message);
            }

            if (count($cachedProducts) > 0) {
                $this->_cachedRequest = $cachedProducts;
                $this->_cached = (count($requestProducts) > 0) ? false : true;
                $this->_cachedStatus = (count($requestProducts) > 0) ? 'partial' : 'full';
            }
            return true;
        } else {
            return 'Missing Account Number';
        }
    }

    public function getProducts()
    {
        return $this->_products;
    }

    /**
     * Builds product data for request
     *
     * @param \Epicor\Comm\Model\Product $product
     *
     * return array()
     */
    protected function buildProductRequest($product)
    {
        $contractHelper = $this->listsFrontendContractHelper;
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */
        $storeId = $this->storeManager->getStore()->getStoreId();
        $productId = $this->catalogProductFactory->create()->getIdBySku($product->getSku());
        $pricing_sku = $this->catalogResourceModelProductFactory()->getAttributeRawValue($productId, 'ecc_pricing_sku', $storeId);
        $sku = ($pricing_sku) ? $pricing_sku : $product->getSku();

        $uomArr = $contractHelper->splitProductCode($sku);
        $productSku = $uomArr[0];
        $uomCode = $uomArr[1] ?:  $pricing_sku = $this->catalogResourceModelProductFactory()
                                    ->getAttributeRawValue($productId, 'ecc_default_uom', $storeId);

        $decimalPlaces = $contractHelper->getDecimalPlaces($product);

        $contracts = $product->getEccContracts();

        $useContracts = ($contracts) ? 'Y' : 'N';

        $productRequest = array(
            'productCode' => $productSku,
            'unitOfMeasureCode' => $uomCode,
            'decimalPlaces' => $decimalPlaces,
            'quantity' => $product->getMsqQuantity(),
            'attributes' => $this->buildProductAttributes($product),
            'contract' => array(
                '_attributes' => array(
                    'useContracts' => $useContracts,
                ),
                'contractCode' => $contracts
            ),
        );

        return $productRequest;
    }

    private function buildProductAttributes($product)
    {
        $attributes = array();
        if ($product->getMsqAttributes()) {
            foreach ($product->getMsqAttributes() as $key => $value) {
                $val = $this->getConfigFlag('att_cdata') ? '<![CDATA[' . $value . ']]>' : $value;

                $attributes['attribute'][] = array(
                    'description' => $key,
                    'value' => $val
                );
            }
        }
        return $attributes;
    }

    /**
     * Returns currencies array to use with this request array
     *
     * @return array
     */
    private function _getCurrencies()
    {
        $currencyArray = array();
        $currencies = $this->getCurrencies();

        // if no currencies specified, use the current store one
        if (empty($currencies)) {
            #        $currencies = array(Mage::app()->getStore()->getCurrentCurrencyCode());
            $currencies = array($this->getHelper()->getCurrencyMapping());
        }

        foreach ($currencies as $currency) {
            $currencyArray[] = array(
                'currencyCode' => $currency
            );
        }

        return $currencyArray;
    }

    /**
     * Returns locations array to use with this request array
     *
     * @return array
     */
    private function _getLocations($partLocations = [], $reload = false)
    {
        if (!$this->getLocationsEnabled()) {
            return array();
        }
        $controllerName = $this->request->getControllerName();
        $actionName     = $this->request->getActionName();
        $locData = ($this->getLocations() && !$reload) ? $this->getLocations() : [];
        if (empty($locData)) {
            $locations = array();
            foreach ($partLocations as $partloc) {
                if (is_string($partloc)) {
                    $locations[] = $partloc;
                } else if(is_numeric($partloc)){
                    $locations[] = (string) $partloc;
                } else if (!in_array($partloc->getLocationCode(), $locations)) {
                    $locations[] = $partloc->getLocationCode();
                }
            }
            $selectedBranch = $this->getCurrentBranch();
            if ($selectedBranch && !$this->registry->registry('inventory_view_locations')) {
                $allowed = $this->getLocationHelper()->getCustomerAllowedLocations();
                $allowed = array_keys($allowed);
                $_relatedLocations = $this->relatedLocations->getRelatedLocationsByCode($selectedBranch);
                $_groupLocations = $this->locationGroups->getGroupLocationCodes($selectedBranch);
                $locations = array_merge($locations, $_relatedLocations, $_groupLocations);
                $locations = array_unique($locations);
                $locations = array_intersect($locations, $allowed);
                $this->registry->unregister('inventory_view_locations');
                $this->registry->register('inventory_view_locations', $locations);
            } else if ($selectedBranch && $this->registry->registry('inventory_view_locations')) {
                $locations = $this->registry->registry('inventory_view_locations');
            } else if (!$selectedBranch && $controllerName == 'pickup' && $actionName == 'removebranchpickup') {
                $locations = (array)$this->commLocationsHelper->create()->getDefaultLocationCode();
            }

            if (!empty($locations)) {
                foreach ($locations as $location) {
                    $locData['locationCode'][] = array(
                        '_attributes' => array(
                            'include' => 'Y',
                            'pricing' => $this->getStockOnly() ? 'N' : 'Y'
                        ),
                        $location
                    );
                }
            }
            if (!$reload) {
                $this->setLocations($locData);
            }
        }
        return $locData;
    }

    /**
     * Returns any base message body needed for the message
     * @return array
     */
    public function getCachedResponseBaseBody()
    {
        $data = parent::getCachedResponseBaseBody();
        $data['parts'] = array(
            'part' => array()
        );
        return $data;
    }

    /**
     * Updates the response object from any cached data
     */
    public function updateResponseFromCache()
    {
        $response = $this->getResponse();
        /* @var $response \Epicor\Common\Model\Xmlvarien */

        if (in_array($this->_cachedStatus, array('full', 'partial'))) {
            if (in_array(self::MESSAGE_TYPE, $this->commHelper->getArrayMessages())) {
                $cachedInfo['parts']['part'] = array_values($this->_productsCache);
                if ($this->_cachedStatus == 'full') {
                    $response = $cachedInfo;
                } else if ($this->_cachedStatus == 'partial') {
                    $responseParts = $this->_getGroupedDataArray('parts', 'part', $response);
                    $mergedParts = array_merge($responseParts, $this->_productsCache);
                    $response['parts']['part'] = $mergedParts;
                }

            } else {
                $cachedInfo = $this->commonXmlvarienFactory->create(['data' => array('parts' => $this->commonXmlvarienFactory->create(['data' => array('part' => array_values($this->_productsCache))]))]);

                if ($this->_cachedStatus == 'full') {
                    $response = $cachedInfo;
                } else if ($this->_cachedStatus == 'partial') {
                    $responseParts = $this->_getGroupedData('parts', 'part', $response);
                    $mergedParts = array_merge($responseParts, $this->_productsCache);
                    $response->getParts()->setPart($mergedParts);
                }
            }

            $this->_cachedResponse = $cachedInfo;

            $this->setResponse($response);
        }

        parent::updateResponseFromCache();
    }

    protected function getAttributeCacheKeys($product)
    {
        $cacheable = array('groupSequence', 'Ewa Code');
        $attributeKeys = array();

        if ($product instanceof \Epicor\Comm\Model\Product) {
            if ($product->getMsqAttributes()) {
                foreach ($product->getMsqAttributes() as $key => $value) {
                    $key = trim($key);
                    if (in_array($key, $cacheable)) {
                        $attributeKeys[] = trim($key) . trim($value);
                    }
                }
            }
        } else {
            $attributeData = $this->_getGroupedData('attributes', 'attribute', $product);

            foreach ($attributeData as $attribute) {
                $desc = trim($attribute->getDescription());
                if (in_array($desc, $cacheable)) {
                    $attributeKeys[] = $desc . trim($attribute->getValue());
                }
            }
        }

        return $attributeKeys;
    }

    /**
     * Supports for Array MSQ
     *
     */
    protected function getAttributeArrayToString($product)
    {
        $attributeString = '';

        if (isset($product['msq_attributes'])) {
            foreach ($product['msq_attributes'] as $key => $value) {
                $attributeString .= $key . $value;
            }

        } else {
            $attributeData = $this->_getGroupedDataArray('attributes', 'attribute', $product);

            foreach ($attributeData as $attribute) {
                if (is_array($attribute['description']) && empty($attribute['description'])) {
                    $attribute['description'] = '';
                }
                if (is_array($attribute['value']) && empty($attribute['value'])) {
                    $attribute['value'] = '';
                }
                $attributeString .= $attribute['description'] . $attribute['value'];
            }
        }

        return md5($attributeString);
    }

    /**
     * Supports for Object MSQ
     *
     */
    protected function getAttributeString($product)
    {
        $attributeString = '';

        if ($product instanceof \Epicor\Comm\Model\Product) {
            if ($product->getMsqAttributes()) {
                foreach ($product->getMsqAttributes() as $key => $value) {
                    $attributeString .= $key . $value;
                }
            }
        } else {
            $attributeData = $this->_getGroupedData('attributes', 'attribute', $product);

            foreach ($attributeData as $attribute) {
                $attributeString .= $attribute->getDescription() . $attribute->getValue();
            }
        }

        return md5($attributeString);
    }

    /**
     * Process the message response To Array.
     */
    public function processResponseArray()
    {
        $success = false;
        if ($this->isSuccessfulStatusCode()) {
            $response = $this->getResponse();
            $parts = $this->_getGroupedDataArray('parts', 'part', $response);

            if (!isset($response['parts'])) {
                return false;
            }


            // $parts = $response['parts'];
            foreach ($parts as $erpProduct) {
                $key = $this->_getProductKeyArray($erpProduct);
                if (is_array($key)) {
                    foreach ($key as $_key) {
                        $this->processProducts($erpProduct, $_key);
                    }
                } else {
                    $this->processProducts($erpProduct, $key);
                }
            }

            $success = true;
        }
        return $success;
    }

    public function processProducts($erpProduct, $key)
    {
        if (isset($this->_products[$key])) {
            $processFunction = ($this->getSaveProductDetails()) ? '_processSaveResponseArray' : '_processDisplayResponseArray';

            $processingProducts = $this->_products[$key];

            if (is_object($processingProducts)) {
                $processingProducts = array($processingProducts);
            }

            foreach ($processingProducts as $product) {
                if ($product instanceof \Epicor\Comm\Model\Product) {
                    $statusCode = '';
                    if (isset($erpProduct['status']['code'])) {
                        $statusCode = $erpProduct['status']['code'];
                    }

                    if ($this->isSuccessfulStatusCode($statusCode) && !isset($this->_productsCache[$key])) {
                        $cacheHelper = $this->commonMessagingCacheHelper;
                        /* @var $cacheHelper \Epicor\Common\Helper\Messaging\Cache */
                        $cacheKeys = $this->getProductCacheKey($product);
                        $erpProduct['cache_key'] = 'MSQ' . '_' . md5(implode('_', $cacheKeys));
                        $cacheHelper->setCache('MSQ', $cacheKeys, $erpProduct);
                        if ($this->getLocationsEnabled() && !isset($this->locationscache[$key])) {
                            $locations = $this->_getGroupedDataArray('locations', 'location', $erpProduct);
                            foreach ($locations as $erpLocation) {
                                $locCacheKeys = $cacheKeys;
                                $locCacheKeys[] = $erpLocation['locationCode'];
                                $cacheHelper->setCache('MSQ', $locCacheKeys, $erpLocation);
                            }
                        }
                    }
                    $this->$processFunction($product, $erpProduct);
                    if ($this->getDisallowNonErpProducts()) {
                        if (!$product->hasErpStock()) {
                            $product->setData('is_salable', false);
                        }
                    }
                }
            }
        }
        return;
    }

    /**
     * Process the message response to Objects.
     */

    public function processResponse()
    {
        $success = false;
        if ($this->isSuccessfulStatusCode()) {
            $response = $this->getResponse();
            $parts = $this->_getGroupedData('parts', 'part', $response);

            if (empty($parts)) {
                return false;
            }

            foreach ($parts as $erpProduct) {
                $key = $this->_getProductKey($erpProduct);
                if (isset($this->_products[$key])) {
                    $processFunction = ($this->getSaveProductDetails()) ? '_processSaveResponse' : '_processDisplayResponse';

                    $processingProducts = $this->_products[$key];

                    if (is_object($processingProducts)) {
                        $processingProducts = array($processingProducts);
                    }

                    foreach ($processingProducts as $product) {
                        if ($product instanceof \Epicor\Comm\Model\Product) {
                            $statusCode = '';
                            if ($erpProduct->getStatus()) {
                                $statusCode = $erpProduct->getStatus()->getCode();
                            }

                            if ($this->isSuccessfulStatusCode($statusCode) && !isset($this->_productsCache[$key])) {
                                $cacheHelper = $this->commonMessagingCacheHelper;
                                /* @var $cacheHelper \Epicor\Common\Helper\Messaging\Cache */
                                $cacheKeys = $this->getProductCacheKey($product);
                                $erpProduct->setCacheKey('MSQ' . '_' . md5(implode('_', $cacheKeys)));
                                $cacheHelper->setCache('MSQ', $cacheKeys, $erpProduct);
                                if ($this->getLocationsEnabled() && !isset($this->locationscache[$key])) {
                                    $locations = $this->_getGroupedData('locations', 'location', $erpProduct);
                                    foreach ($locations as $erpLocation) {
                                        $locCacheKeys = $cacheKeys;
                                        $locCacheKeys[] = $erpLocation->getLocationCode();
                                        $cacheHelper->setCache('MSQ', $locCacheKeys, $erpLocation);
                                    }
                                }
                            }
                            $this->$processFunction($product, $erpProduct);
                            if ($this->getDisallowNonErpProducts()) {
                                if (!$product->hasErpStock()) {
                                    $product->setData('is_salable', false);
                                }
                            }
                        }
                    }
                }

                if ($this->getUpdateGroupedProducts()) {
                    $keyNoUom = $this->_getProductKeyNoUom($erpProduct);

                    if ($key != $keyNoUom && isset($this->_products[$keyNoUom])) {
                        $processFunction = ($this->getSaveProductDetails()) ? '_processSaveResponse' : '_processDisplayResponse';

                        $processingProducts = $this->_products[$keyNoUom];

                        if (is_object($processingProducts)) {
                            $processingProducts = array($processingProducts);
                        }

                        foreach ($processingProducts as $product) {
                            if ($product->getUom() == $erpProduct->getUnitOfMeasureCode()) {
                                $this->$processFunction($product, $erpProduct);
                                if ($this->getDisallowNonErpProducts() && !$product->hasErpStock()) {
                                    $product->setData('is_salable', false);
                                }
                            }
                        }
                    }
                }
            }

            $success = true;
        }
        return $success;
    }

    protected function _getProductKeyArray($erpProduct)
    {
        $partNumber = $erpProduct['productCode'];
        //don't try to process an empty product
        if ($partNumber == null) {
            return;
        }
        $attributeString = $this->getAttributeArrayToString($erpProduct);
        $uom = isset($erpProduct['unitOfMeasureCode']) ? $erpProduct['unitOfMeasureCode'] : '';
        if (is_array($uom)) {
            $uom = array_filter(array_map('trim', $uom));
            if (empty($uom)) {
                $uom = '';
            }
        }
        $key = $partNumber . $this->_uomSeparator . $uom . $this->_uomSeparator . $attributeString;
        $parentKey = $partNumber . $this->_uomSeparator . $attributeString;

        if (!isset($this->_products[$key])) {
            $key = $parentKey;
        }

        if (isset($this->_products[$key]) &&
            isset($this->_products[$parentKey]) &&
            !isset($this->groupProcessed[$parentKey]) &&
            !is_array($this->_products[$parentKey]) &&
            $uom == $this->_products[$parentKey]->getEccDefaultUom()) {
            $this->groupProcessed[$parentKey] = 1;
            return [$parentKey, $key];
        }
        return $key;
    }

    protected function _getProductKey($erpProduct)
    {
        $partNumber = $erpProduct->getProductCode();
        $attributeString = $this->getAttributeString($erpProduct);
        $uom = $erpProduct->getUnitOfMeasureCode() ?: '';
        $key = $partNumber . $this->_uomSeparator . $uom . $this->_uomSeparator . $attributeString;

        if (!isset($this->_products[$key])) {
            $key = $partNumber . $this->_uomSeparator . $attributeString;
        }

        return $key;
    }

    protected function _getProductKeyNoUom($erpProduct)
    {
        $partNumber = $erpProduct->getProductCode();
        $attributeString = $this->getAttributeString($erpProduct);
        $uom = $erpProduct->getUnitOfMeasureCode() ?: '';
        $key = $partNumber . $this->_uomSeparator . $attributeString;

        return $key;
    }

    /**
     * Deals with modifying stored product info  for display based on the ERP
     * array response
     *
     * @param \Epicor\Comm\Model\Product $eccProduct
     * @param \Epicor\Common\Model\Xmlvarien $erpProduct
     */
    private function _processDisplayResponseArray($eccProduct, $erpProduct)
    {
        $productHelper = $this->commProductHelper->create();
        /* @var $productHelper \Epicor\Comm\Helper\Product */

        $eccProduct->setMsqMessageData($erpProduct);
        $updateStock = $this->getConfig('update_prices_stock') != 'priceonly';
        $updatePrices = $this->getConfig('update_prices_stock') != 'stockonly';
        $helper = $this->commMessagingHelper->create();
        $hideTier = false;

        $statusCode = '';
        if (isset($erpProduct['status']['code'])) {
            $statusCode = $erpProduct['status']['code'];
        }
        //$alwaysInStock = Mage::getStoreConfig('epicor_comm_enabled_messages/msq_request/products_always_in_stock');
        $eccProduct->setData('is_salable', ($this->isSuccessfulStatusCode($statusCode) || $eccProduct->getTypeId() == 'grouped' || $eccProduct->getTypeId() == 'configurable'));

        if (!$eccProduct->getData('is_salable') || !$this->isSuccessfulStatusCode($statusCode)) {
            if ($statusCode === '011' && !$this->commHelper->isShowOutOfStock() && !$eccProduct->getIsEccNonStock()) {
                $configurator = $this->catalogResourceModelProductFactory()
                    ->getAttributeRawValue($eccProduct->getId(), 'ecc_pricing_sku', $this->storeManager->getStore()->getStoreId());
                $this->commHelper->handleOutofStock($erpProduct, $eccProduct, $configurator);
            }
            return;
        }

        if ($updatePrices) {
            $productCurrency = $this->getMsqCurrencyInfoArray($eccProduct, $erpProduct);

            if (isset($productCurrency['contractCode'])) {
                $eccProduct->setEccMsqContractCode($productCurrency['contractCode']);
            }
            if (isset($productCurrency['maximumContractQty'])) {
                $erpProduct['maximumOrderQty'] = $productCurrency['maximumContractQty'];
            }

            $basePrice = (isset($productCurrency['basePrice'])) ? $productCurrency['basePrice'] : '';
            $customerPrice = (isset($productCurrency['customerPrice'])) ? $productCurrency['customerPrice'] : '';
            $roundingDecimals = $this->getPreventRounding() ? '4' : $this->pricePrecision;
            $allowPricingRules = $this->getAllowPriceRules();
            $eccProduct->setEccMsqBasePrice($basePrice);

            $breaks = (isset($productCurrency['breaks'])) ? $productCurrency['breaks'] : [];
            //  $breaks =  $this->_getGroupedDataArray('breaks', 'break', $productCurrency);
            $productHelper->setProductPrices($eccProduct, $basePrice, $customerPrice, $breaks, $roundingDecimals, false);

            $epicorOriginalPrice = $eccProduct->calculateEpicorOriginalPrice($eccProduct->getMsqQuantity());
            $eccProduct->setEccOriginalPrice($epicorOriginalPrice);

            if ($allowPricingRules) {
                $breaks = (isset($productCurrency['breaks'])) ? $productCurrency['breaks'] : [];
                //$breaks =  $this->_getGroupedDataArray('breaks', 'break', $productCurrency);
                $productHelper->setProductPrices($eccProduct, $basePrice, $customerPrice, $breaks, $roundingDecimals, $allowPricingRules);
            }
        }

        if ($updateStock) {
            $stockData = $eccProduct->getStockData();

            $min = (isset($erpProduct['minimumOrderQty'])) ? $erpProduct['minimumOrderQty'] : false;
            $max = (isset($erpProduct['maximumOrderQty'])) ? $erpProduct['maximumOrderQty'] : false;

            if ($min) {
                $stockData['min_sale_qty'] = $min;
            }

            if ($max) {
                $stockData['max_sale_qty'] = $max;
            }

            //Process Locations array.
            $this->_processLocationsArray($eccProduct, $erpProduct);
            $stockVisibility  = $this->getLocationHelper()->getStockVisibilityFlag();
            $inStockLocations = [];
            $eccProductStockLevels = $this->registry->registry('aggregate_stock_levels_' . $eccProduct->getSku());

            if (!in_array($stockVisibility, ['all_source_locations', 'default']) && !is_null($eccProductStockLevels)) {
                $inStockLocations = array_filter(array_values($eccProductStockLevels), function ($v) {
                    return $v > 0;
                });
            }

            if ($this->locationsEnabled && in_array($stockVisibility, ['default'])) {
                $freestock = array_filter($eccProductStockLevels, function ($key) {
                    return $key == $this->getLocationHelper()->getDefaultLocationCode();
                }, ARRAY_FILTER_USE_KEY);
                $freestock = !empty($freestock) ? array_pop($freestock) : false;

            } else {
                $freestock = (isset($erpProduct['freeStock'])) ? $erpProduct['freeStock'] : false;
            }

            $stockData['qty'] = $helper->qtyRounding($freestock);

            $eccProduct->setStockData($stockData);
            $leadTimeText = '';
            if (!empty($erpProduct['leadTimeText'])) {
                $leadTimeText = $erpProduct['leadTimeText'];
            }
            $leadTime = '';
            if (!empty($erpProduct['leadTime'])) {
                $leadTime = $erpProduct['leadTime'];
            }
            $eccProduct->setEccLeadTime($this->_getLeadTime($leadTime, $leadTimeText));

            // Set availability
            $msqAlwaysInStock = $this->getConfigFlag('products_always_in_stock');

            /**
             * First check discontinued is true then return false else
             * second return msq config always in stock
             */
            $msqAlwaysInStock = $eccProduct->getIsEccDiscontinued() ? false : $msqAlwaysInStock; // discontinued item

            /**
             * First check non-stock is true then return true else
             * second return parent var alwaysInStock
             */
            $msqAlwaysInStock = $eccProduct->getIsEccNonStock() ?: $msqAlwaysInStock; // non-stock item
            $in_stock = ($stockData['qty'] > 0 || !empty($inStockLocations)) ? true : $msqAlwaysInStock;
            if ($eccProduct->getTypeId() == 'configurable') {
                $in_stock = true;
            }
            $eccProduct->setData('is_salable', $in_stock);
            $eccProduct->setErpStock($stockData['qty']);
            //M1 > M2 Translation Begin (Rule 23)
            // $stockItem = $eccProduct->getStockItem();
            $stockItem = $this->catalogInventoryApiStockRegistryInterface->getStockItem($eccProduct->getId(), $eccProduct->getStore()->getWebsiteId());
            //M1 > M2 Translation End
            if (!$stockItem || !$stockItem->getProductId()) {
                $stockItem = $this->catalogInventoryStockItemFactory->create();
                /* @var $stockItem \Magento\CatalogInventory\Model\Stock\Item */
                //M1 > M2 Translation Begin (Rule 6)
                //$stockItem->loadByProduct($eccProduct->getId());
                $stockItem->getResource()->loadByProductId($stockItem, $eccProduct->getId(), $stockItem->getStockId());
                //M1 > M2 Translation End
            }
            $stockItem->addData($stockData);
            $eccProduct->setStockItem($stockItem);
            if ((!$eccProduct->isSaleable() || $stockData['qty'] <= 0) && !$this->commHelper->isShowOutOfStock() && !$eccProduct->getIsEccNonStock()) {
                $configurator = $this->catalogResourceModelProductFactory()
                    ->getAttributeRawValue($eccProduct->getId(), 'ecc_pricing_sku', $this->storeManager->getStore()->getStoreId());
                $this->commHelper->handleOutofStock($erpProduct, $eccProduct, $configurator);
            }
        }

        /* Code for configurator Registry */
        if ($eccProduct->getEccConfigurator()) {
            if ($eccProduct->getMsqAttributes()) {
                foreach ($eccProduct->getMsqAttributes() as $key => $value) {
                    if ($key == 'groupSequence') {
                        $customekey = $eccProduct->getSku() . $value;
                        $basePrice = $this->salesRepPricingRuleProductHelper->getBasePrice($eccProduct, $eccProduct->getQty());
                        $rulePrice = $this->salesRepPricingRuleProductHelper->getRuleBasePrice($eccProduct, $basePrice, $eccProduct->getQty());
                        $minPrice = $this->salesRepPricingRuleProductHelper->getMinPrice($eccProduct, $basePrice);
                        $maxDiscount = $this->salesRepPricingRuleProductHelper->getMaxDiscount($eccProduct, $basePrice);

                        $salesrep_discount_pricevalues = array('basePrice' => $basePrice,
                            'rulePrice' => $rulePrice,
                            'minPrice' => $minPrice,
                            'maxDiscount' => $maxDiscount);

                        $this->registry->unregister('rfq_discount_product_' . $customekey);
                        $this->registry->register('rfq_discount_product_' . $customekey, $salesrep_discount_pricevalues);
                    }
                }
            }
        }
        /* Code for configurator Registry  END*/
    }

    /**
     * Deals with modifying stored product info  for display based on the ERP
     * Object response
     *
     * @param \Epicor\Comm\Model\Product $eccProduct
     * @param \Epicor\Common\Model\Xmlvarien $erpProduct
     */

    private function _processDisplayResponse($eccProduct, $erpProduct)
    {
        $productHelper = $this->commProductHelper->create();
        /* @var $productHelper \Epicor\Comm\Helper\Product */

        $eccProduct->setMsqMessageData($erpProduct);
        $updateStock = $this->getConfig('update_prices_stock') != 'priceonly';
        $updatePrices = $this->getConfig('update_prices_stock') != 'stockonly';
        $helper = $this->commMessagingHelper->create();
        $hideTier = false;

        $statusCode = '';
        if ($erpProduct->getStatus()) {
            $statusCode = $erpProduct->getStatus()->getCode();
        }
        //$alwaysInStock = Mage::getStoreConfig('epicor_comm_enabled_messages/msq_request/products_always_in_stock');
        $eccProduct->setData('is_salable', ($this->isSuccessfulStatusCode($statusCode) || $eccProduct->getTypeId() == 'grouped'));

        if (!$eccProduct->getData('is_salable') || !$this->isSuccessfulStatusCode($statusCode)) {
            return;
        }

        if ($updatePrices) {
            $productCurrency = $this->getMsqCurrencyInfo($eccProduct, $erpProduct);

            if ($productCurrency->getContractCode()) {
                $eccProduct->setEccMsqContractCode($productCurrency->getContractCode());
                $erpProduct->setMaximumOrderQty($productCurrency->getMaximumContractQty());
            }

            $basePrice = $productCurrency->getBasePrice();
            $customerPrice = $productCurrency->getCustomerPrice();
            $roundingDecimals = $this->getPreventRounding() ? '4' : $this->pricePrecision;
            $allowPricingRules = $this->getAllowPriceRules();
            $eccProduct->setEccMsqBasePrice($basePrice);

            $productHelper->setProductPrices($eccProduct, $basePrice, $customerPrice, $productCurrency->getBreaks(), $roundingDecimals, false);

            $epicorOriginalPrice = $eccProduct->calculateEpicorOriginalPrice($eccProduct->getMsqQuantity());
            $eccProduct->setEccOriginalPrice($epicorOriginalPrice);

            if ($allowPricingRules) {
                $productHelper->setProductPrices($eccProduct, $basePrice, $customerPrice, $productCurrency->getBreaks(), $roundingDecimals, $allowPricingRules);
            }
        }

        if ($updateStock) {
            $stockData = $eccProduct->getStockData();

            $min = $erpProduct->getMinimumOrderQty();
            $max = $erpProduct->getMaximumOrderQty();

            if (!empty($min)) {
                $stockData['min_sale_qty'] = $min;
            }

            if (!empty($max)) {
                $stockData['max_sale_qty'] = $max;
            }
            $stockData['qty'] = $helper->qtyRounding($erpProduct->getFreeStock());

            $eccProduct->setStockData($stockData);
            $eccProduct->setEccLeadTime($this->_getLeadTime($erpProduct->getLeadTime(), $erpProduct->getLeadTimeText()));

            // Set availability

            $in_stock = $stockData['qty'] > 0 ? true : $this->getConfigFlag('products_always_in_stock');
            $eccProduct->setData('is_salable', $in_stock);
            $eccProduct->setErpStock($stockData['qty']);
            //M1 > M2 Translation Begin (Rule 23)
            // $stockItem = $eccProduct->getStockItem();
            $stockItem = $this->catalogInventoryApiStockRegistryInterface->getStockItem($eccProduct->getId(), $eccProduct->getStore()->getWebsiteId());
            //M1 > M2 Translation End
            if (!$stockItem || !$stockItem->getProductId()) {
                $stockItem = $this->catalogInventoryStockItemFactory->create();
                /* @var $stockItem \Magento\CatalogInventory\Model\Stock\Item */
                //M1 > M2 Translation Begin (Rule 6)
                //$stockItem->loadByProduct($eccProduct->getId());
                $stockItem->getResource()->loadByProductId($stockItem, $eccProduct->getId(), $stockItem->getStockId());
                //M1 > M2 Translation End
            }
            $stockItem->addData($stockData);
            $eccProduct->setStockItem($stockItem);
        }

        $this->_processLocations($eccProduct, $erpProduct);
    }

    /**
     * Deals with modifying stored product info  for display based on the ERP
     * Array response
     *
     * @param \Epicor\Comm\Model\Product $eccProduct
     * @param \Epicor\Common\Model\Xmlvarien $erpProduct
     */
    protected function getMsqCurrencyInfoArray(&$eccProduct, &$erpProduct)
    {
        $contractHelper = $this->listsFrontendContractHelper;
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */

        $productContracts = $eccProduct->getEccContracts();
        $currency = (isset($erpProduct['currencies']['currency'])) ? $erpProduct['currencies']['currency'] : [];
        $productCurrency = (isset($erpProduct['currencies'])) ? $currency : [];
        $contractGroup = (isset($productCurrency['contracts']) && count($productCurrency['contracts']) > 0) ? $productCurrency['contracts'] : [];
        $contracts = $this->_getGroupedDataArray('contracts', 'contract', $productCurrency);
        $contracts = $this->validateContractArray($contracts);
        if (empty($contractGroup) == false) {
            $eccProduct->setEccMsqContractData($contracts);
        }


        if (
            $contractHelper->contractsEnabled() &&
            count($productContracts) == 1 &&
            (empty($contractGroup) == false)
        ) {
            $contracts = $this->_getGroupedDataArray('contracts', 'contract', $productCurrency);
            $contractCode = array_pop($productContracts);
            foreach ($contracts as $contract) {
                if ($contract['contractCode'] == $contractCode) {
                    $productCurrency = $contract;
                }
            }
        }

        return $productCurrency;
    }

    /**
     * Deals with modifying stored product info  for display based on the ERP
     * Object response
     *
     * @param \Epicor\Comm\Model\Product $eccProduct
     * @param \Epicor\Common\Model\Xmlvarien $erpProduct
     */
    protected function getMsqCurrencyInfo(&$eccProduct, &$erpProduct)
    {
        $contractHelper = $this->listsFrontendContractHelper;
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */

        $productContracts = $eccProduct->getEccContracts();
        $productCurrency = $erpProduct->getCurrencies()->getCurrency();
        $contractGroup = $productCurrency->getContracts();

        if ($contractGroup) {
            $contracts = $contractGroup->getasarrayContract();
            if (empty($contracts) == false) {
                $eccProduct->setEccMsqContractData($contractGroup->getasarrayContract());
            }
        }

        if (
            $contractHelper->contractsEnabled() &&
            count($productContracts) == 1 &&
            $contractGroup
        ) {
            $contracts = $contractGroup->getasarrayContract();
            $contractCode = array_pop($productContracts);
            foreach ($contracts as $contract) {
                if ($contract->getContractCode() == $contractCode) {
                    $productCurrency = $contract;
                }
            }
        }

        return $productCurrency;
    }

    protected function _getLeadTime($leadTime, $leadTimeText)
    {
        if (preg_match("/[0-9]/", $leadTimeText)) {
            return $leadTimeText;
        } else {
            return $leadTime . ' ' . $leadTimeText;
        }
    }

    /**
     * Updates location info based on the ERP
     * Array response
     *
     * @param \Epicor\Comm\Model\Product $eccProduct
     * @param \Epicor\Common\Model\Xmlvarien $erpProduct
     */
    protected function _processLocationsArray($eccProduct, $erpProduct)
    {
        if ($this->scopeConfig->isSetFlag('epicor_comm_locations/global/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $locations = $this->_getGroupedDataArray('locations', 'location', $erpProduct);
            $helper = $this->getHelper();
            $productLocations = $eccProduct->getLocations();
            $processedLocations = array();

            $contractHelper = $this->listsFrontendContractHelper;
            /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */

            $mappings = [];
            foreach ($locations as $erpLocation) {
                if (isset($productLocations[$erpLocation['locationCode']])) {
                    $processedLocations[] = $erpLocation['locationCode'];
                    $location = $productLocations[$erpLocation['locationCode']];
                    /* @var $location \Epicor\Comm\Model\Location\Product */
                    (isset($erpLocation['stockStatus'])) ? $location->setStockStatus($erpLocation['stockStatus']) : '';
                    (isset($erpLocation['freeStock'])) ? $this->_locationStockLevels[$erpLocation['locationCode']] = $erpLocation['freeStock'] : '';           // aggregage stock levels returned from different locations in msq
                    (isset($erpLocation['freeStock'])) ? $location->setFreeStock($erpLocation['freeStock']) : '';
                    (isset($erpLocation['minimumOrderQty'])) ? $location->setMinimumOrderQty($erpLocation['minimumOrderQty']) : '';
                    (isset($erpLocation['maximumOrderQty'])) ? $location->setMaximumOrderQty($erpLocation['maximumOrderQty']) : '';
                    (isset($erpLocation['leadTimeDays'])) ? $location->setLeadTimeDays($erpLocation['leadTimeDays']) : '';
                    (isset($erpLocation['leadTimeText'])) ? $location->setLeadTimeText($erpLocation['leadTimeText']) : '';

                    $currencies = $this->_getGroupedDataArray('currencies', 'currency', $erpLocation);
                    foreach ($currencies as $erpCurrency) {
                        if (!isset($mappings[$erpCurrency['currencyCode']])) {
                            $currencyCode = $helper->getCurrencyMapping($erpCurrency['currencyCode'], $helper::ERP_TO_MAGENTO);
                            $mappings[$erpCurrency['currencyCode']] = $currencyCode;
                        } else {
                            $currencyCode = $mappings[$erpCurrency['currencyCode']];
                        }

                        $currencyObj = $this->commLocationProductCurrencyFactory->create();

                        if ($contractHelper->contractsEnabled()) {
                            $productContracts = $eccProduct->getEccContracts();
//                            $contractGroup = $erpCurrency['contracts'];
                            $contractGroup = $this->_getGroupedDataArray('contracts', 'contract', $erpCurrency);
                            $contractGroup = $this->validateContractArray($contractGroup);
                            if ($contractGroup && count($productContracts) == 1) {
                                $contracts = $contractGroup;
                                if (empty($contracts) == false) {
                                    $eccProduct->setEccMsqContractData($contractGroup);
                                    $contractCode = array_pop($productContracts);

                                    foreach ($contracts as $contract) {
                                        if ($contract['contractCode'] == $contractCode) {
                                            $contractCurrency = $contract;
                                        }
                                    }

                                    $eccProduct->setEccMsqContractCode($contractCurrency['contractCode']);
                                    $location->setMaximumOrderQty($contractCurrency['maximumContractQty']);
                                    $erpCurrency['basePrice'] = $contractCurrency['basePrice'];
                                    $erpCurrency['customerPrice'] = $contractCurrency['customerPrice'];
                                    $erpCurrency['discount'] = (isset($contractCurrency['discount'])) ? $contractCurrency['discount'] : '';
                                    $erpCurrency['breaks'] = $contractCurrency['breaks'];
                                }
                            }
                        }

                        $currencyObj->setBasePrice($erpCurrency['basePrice']);
                        $currencyObj->setCustomerPrice($erpCurrency['customerPrice']);
                        $discount = (isset($erpCurrency['discount'])) ? $erpCurrency['discount'] : false;
                        $currencyObj->setDiscount($discount);
                        //$currencyObj->setDiscount($erpCurrency['discount']);
                        $locCurrbreaks = (isset($erpCurrency['breaks'])) ? $erpCurrency['breaks'] : '';
                        $currencyObj->setBreaks($locCurrbreaks);

                        $location->setCurrencyObject($currencyCode, $currencyObj);
                        unset($currencyObj);
                    }

                    $eccProduct->setLocation($erpLocation['locationCode'], $location);
                }
            }
            $sku = $eccProduct->getSku();
            $this->registry->unregister('aggregate_stock_levels_' . $sku);
            $this->registry->register('aggregate_stock_levels_' . $sku, $this->_locationStockLevels);             // save in location stock array registry if required 

            if (is_array($productLocations)) {
                foreach ($productLocations as $locationCode => $location) {
                    if (!in_array($locationCode, $processedLocations)) {
                        $eccProduct->deleteLocation($locationCode);
                    }
                }
            }
        }
    }

    /**
     * Updates location info based on the ERP
     * Object response
     *
     * @param \Epicor\Comm\Model\Product $eccProduct
     * @param \Epicor\Common\Model\Xmlvarien $erpProduct
     */
    protected function _processLocations($eccProduct, $erpProduct)
    {
        if ($this->scopeConfig->isSetFlag('epicor_comm_locations/global/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $locations = $this->_getGroupedData('locations', 'location', $erpProduct);
            $helper = $this->getHelper();
            $productLocations = $eccProduct->getLocations();
            $processedLocations = array();

            $contractHelper = $this->listsFrontendContractHelper;
            /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */

            $mappings = [];
            foreach ($locations as $erpLocation) {
                if (isset($productLocations[$erpLocation->getLocationCode()])) {
                    $processedLocations[] = $erpLocation->getLocationCode();
                    $location = $productLocations[$erpLocation->getLocationCode()];
                    /* @var $location \Epicor\Comm\Model\Location\Product */
                    $location->setStockStatus($erpLocation->getStockStatus());
                    $this->_locationStockLevels[$erpLocation->getLocationCode()] = $erpLocation->getFreeStock();           // aggregage stock levels returned from different locations in msq 
                    $location->setFreeStock($erpLocation->getFreeStock());
                    $location->setMinimumOrderQty($erpLocation->getMinimumOrderQty());
                    $location->setMaximumOrderQty($erpLocation->getMaximumOrderQty());
                    $location->setLeadTimeDays($erpLocation->getLeadTimeDays());
                    $location->setLeadTimeText($erpLocation->getLeadTimeText());

                    $currencies = $this->_getGroupedData('currencies', 'currency', $erpLocation);

                    foreach ($currencies as $erpCurrency) {
                        if (!isset($mappings[$erpCurrency->getCurrencyCode()])) {
                            $currencyCode = $helper->getCurrencyMapping($erpCurrency->getCurrencyCode(), $helper::ERP_TO_MAGENTO);
                            $mappings[$erpCurrency->getCurrencyCode()] = $currencyCode;
                        } else {
                            $currencyCode = $mappings[$erpCurrency->getCurrencyCode()];
                        }

                        $currencyObj = $this->commLocationProductCurrencyFactory->create();

                        if ($contractHelper->contractsEnabled()) {
                            $productContracts = $eccProduct->getEccContracts();
                            $contractGroup = $erpCurrency->getContracts();
                            if ($contractGroup && count($productContracts) == 1) {
                                $contracts = $contractGroup->getasarrayContract();
                                if (empty($contracts) == false) {
                                    $eccProduct->setEccMsqContractData($contractGroup->getasarrayContract());
                                    $contractCode = array_pop($productContracts);

                                    foreach ($contracts as $contract) {
                                        if ($contract->getContractCode() == $contractCode) {
                                            $contractCurrency = $contract;
                                        }
                                    }

                                    $eccProduct->setEccMsqContractCode($contractCurrency->getContractCode());
                                    $location->setMaximumOrderQty($contractCurrency->getMaximumContractQty());
                                    $erpCurrency->setBasePrice($contractCurrency->getBasePrice());
                                    $erpCurrency->setCustomerPrice($contractCurrency->getCustomerPrice());
                                    $erpCurrency->setDiscount($contractCurrency->getDiscount());
                                    $erpCurrency->setBreaks($contractCurrency->getBreaks());
                                }
                            }
                        }

                        $currencyObj->setBasePrice($erpCurrency->getBasePrice());
                        $currencyObj->setCustomerPrice($erpCurrency->getCustomerPrice());
                        $currencyObj->setDiscount($erpCurrency->getDiscount());
                        $currencyObj->setBreaks($erpCurrency->getBreaks());

                        $location->setCurrencyObject($currencyCode, $currencyObj);
                        unset($currencyObj);
                    }

                    $eccProduct->setLocation($erpLocation->getLocationCode(), $location);
                }
            }
            $sku = $eccProduct->getSku();
            $this->registry->unregister('aggregate_stock_levels_' . $sku);
            $this->registry->register('aggregate_stock_levels_' . $sku, $this->_locationStockLevels);             // save in location stock array registry if required 

            if (is_array($productLocations)) {
                foreach ($productLocations as $locationCode => $location) {
                    if (!in_array($locationCode, $processedLocations)) {
                        $eccProduct->deleteLocation($locationCode);
                    }
                }
            }
        }
    }

    /**
     * Get the data for a location (from STK / MSQ response)
     *
     * @param string $locationCode
     * @param \Epicor\Common\Model\Xmlvarien $data
     */
    protected function _getLocationData($data)
    {
        /* @var $location \Epicor\Comm\Model\Location\Product */

        $location = array(
            'location_code' => $data->getLocationCode(),
            'stock_status' => $data->getStockStatus(),
            'free_stock' => $data->getFreeStock(),
            'minimum_order_qty' => $data->getMinimumOrderQty(),
            'maximum_order_qty' => $data->getMaximumOrderQty(),
            'supplier_brand' => $data->getSupplierBrand(),
            'tax_code' => $data->getTaxCode(),
        );

        if ($data->getLeadTime()) {
            $location['lead_time_days'] = $data->getLeadTime()->getDays();
            $location['lead_time_text'] = $data->getLeadTime()->getText();
        }

        $manufacturerData = array();
        if ($data->getManufacturers()) {
            $manufacturers = $data->getManufacturers()->getasarrayManufacturer();

            foreach ($manufacturers as $manufacturer) {
                $att = $manufacturer->getData('_attributes');

                $manufacturerData[] = array(
                    'primary' => ($att) ? $att->getPrimary() : 'N',
                    'name' => $manufacturer->getName(),
                    'product_code' => $manufacturer->getProductCode()
                );
            }
        }

        $location['manufacturers'] = $manufacturerData;

        $currencies = array();

        if ($data->getCurrencies()) {
            $currencies = $data->getCurrencies()->getasarrayCurrency();
        }

        $location['currencies'] = $currencies;

        return $location;
    }

    /**
     * Deals with saving a product based on the ERP Array response
     *
     * @param \Epicor\Comm\Model\Product $eccProduct
     * @param \Epicor\Common\Model\Xmlvarien $erpProduct
     */

    private function _processSaveResponseArray($eccProduct, $erpProduct)
    {
        // if locations on, locations update is set and locations are available for product process locations update
        if ($this->scopeConfig->isSetFlag('epicor_comm_locations/global/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            && $this->scopeConfig->isSetFlag(self::LOCATIONS_UPDATE, ScopeInterface::SCOPE_STORE)
            && $eccProduct->getLocations()) {
            $this->_processLocationsUpdate($eccProduct, $erpProduct);
        }

        $reindexPrices = false;
        $reindexStock = false;

        $overwritePricesOnUpdate = $this->scopeConfig->isSetFlag(self::CURRENCIES_UPDATE,
                         ScopeInterface::SCOPE_STORE);
        $updateTime = date_create('now')->format('Y-m-d H:i:s');


        $updateStock = $this->getConfig('update_prices_stock') != 'priceonly';
        $updatePrices = $this->getConfig('update_prices_stock') != 'stockonly';

        $action = $this->catalogResourceModelProductActionFactory->create();
        $helper = $this->getHelper();

        $productId = $eccProduct->getId();

        $product = $this->catalogProductFactory->create()->setStore()->load($productId);
        /* @var $product Epicor_Comm_Model_Product */
        $productChanges = array(
            0 => array('ecc_last_msq_update' => $updateTime)
        );

        //$action->updateAttributes(array($productId), ["ecc_last_msq_update" => $updateTime] , $product->getStoreId());
        //check if lead time needs to be updated, if so add to store 0
        $overwriteLeadTime = $this->scopeConfig
                                  ->isSetFlag(self::LEAD_TIME_DAYS_UPDATE, ScopeInterface::SCOPE_STORE);
        $overwriteLeadTimeText = $this->scopeConfig
                                  ->isSetFlag(self::LEAD_TIME_TEXT_UPDATE, ScopeInterface::SCOPE_STORE);

        $oldLeadTime = $eccProduct->getData("ecc_lead_time");

        // lead time days
        $leadTime = "";
        if (isset($erpProduct['leadTime']) && !empty($erpProduct['leadTime']) && $overwriteLeadTime) {
            $leadTime = $erpProduct['leadTime'];
        }

        // lead time text
        $leadTimeText = "";
        if (isset($erpProduct['leadTimeText']) && !empty($erpProduct['leadTimeText']) && $overwriteLeadTimeText) {
            $leadTimeText = $erpProduct['leadTimeText'];
        }

        // OLD lead days time
        if ($oldLeadTime) {
            $leadTimeArray = explode(" ", $oldLeadTime, 2);
            if (!$overwriteLeadTime) {  // if not new and not updatable use exsiting value
                $leadTime = isset($leadTimeArray[0]) ? $leadTimeArray[0] : '';
            }
            if (!$overwriteLeadTimeText) {
                $leadTimeText = isset($leadTimeArray[1]) ? $leadTimeArray[1] : '';
            }
        }

        // update leadTime
        $leadDayTime = $this->_getLeadTime($leadTime, $leadTimeText);
        if ($product['ecc_lead_time'] != $leadDayTime) {
            $leadTime = array('ecc_lead_time' => $leadDayTime);
            $productChanges[0] = array_merge($productChanges[0], $leadTime);
        }
        //only update stock if updatable
        if ($updateStock) {
            $result = $this->_processSaveStock($product, $erpProduct);
            $reindexStock = $result['reindex'];
            if (empty($result['changes']) == false) {
                $productChanges[0] = array_merge($productChanges[0], $result['changes']);
            }
        }
        //only update prices if updatable
        if ($updatePrices && $overwritePricesOnUpdate) {
            $result = $this->_processSavePrices($product, $erpProduct);
            $reindexPrices = $result['reindex'];
            if (empty($result['changes']) == false) {
                foreach ($result['changes'] as $storeId => $changes) {
                    if (isset($productChanges[$storeId])) {
                        $productChanges[$storeId] = array_merge($productChanges[$storeId], $changes);
                    } else {
                        $productChanges[$storeId] = $changes;
                    }
                }
            }
        }

        //after all update checks
        if (empty($productChanges) == false) {
            //$action = Mage::getResourceModel('catalog/product_action');
            /* @var $action Mage_Catalog_Model_Resource_Product_Action */
            foreach ($productChanges as $storeId => $changes) {
                $action->updateAttributes(array($product->getId()), $changes, $storeId);
            }
            if ($reindexPrices || $reindexStock) {
                $indexer = $this->commIndexerFactory->create();
                $adapter = $product->getResource();
                /* @var $adapter \Epicor\Comm\Model\ResourceModel\Indexer */
                $adapter->beginTransaction();
                try {
                    $indexer->indexProductById($productId);

                    $adapter->commit();
                } catch (\Exception $e) {
                    $adapter->rollback();
                    $this->setStatusDescription('Indexing failed, please manually re-index');
                }
            }
        }
    }


    /**
     * Deals with saving a product based on the ERP Object response
     *
     * @param \Epicor\Comm\Model\Product $eccProduct
     * @param \Epicor\Common\Model\Xmlvarien $erpProduct
     */

    private function _processSaveResponse($eccProduct, $erpProduct)
    {
        $helper = $this->getHelper();
        //M1 > M2 Translation Begin (Rule 25)
        $updateTime = now();
        $updateTime = date('Y-m-d H:i:s');
        //M1 > M2 Translation End
        $updateStock = $this->getConfig('update_prices_stock') != 'priceonly';
        $updatePrices = $this->getConfig('update_prices_stock') != 'stockonly';

        $productId = $eccProduct->getId();

        $product = $this->catalogProductFactory->create()->setStore()->load($productId);
        $product->setEccLastMsqUpdate($updateTime);
        $product->save();

        //only update stock if updatable
        if ($updateStock) {
            $product = $this->catalogProductFactory->create()->setStore()->load($productId);
            // update product stock
            //M1 > M2 Translation Begin (Rule 23)
            //$stockItem = $product->getStockItem();
            $stockItem = $this->catalogInventoryApiStockRegistryInterface->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
            //M1 > M2 Translation End
            if ($stockItem->getQty() != $erpProduct->getFreeStock()) {
                $stockItem->setQty($erpProduct->getFreeStock());
                $stockItem->setIsInStock($stockItem->getQty() > 0 ? 1 : 0);
                // save Stock Item
                $stockItem->save();
            }

            $stockData = $product->getStockData();

            $min = $erpProduct->getMinimumOrderQty();
            $max = $erpProduct->getMaximumOrderQty();

            if (!empty($min)) {
                $stockData['min_sale_qty'] = $min;
            }

            if (!empty($max)) {
                $stockData['max_sale_qty'] = $max;
            }

            $product->setStockData($stockData);

            $product->setEccLeadTime($erpProduct->getLeadTime());

            $product->setMinOrderQty();

            $product->save();

            $product->clearInstance();
            unset($stockItem);
            $product->unsStockItem();
            unset($product);
        }

        if ($updatePrices) {

            $default_stores = $helper->getDefaultStores();
            $currencies = $erpProduct->getCurrencies()->getCurrency();

            $customerPriceUsed = $this->getConfig('cusomterpriceused');

            if (!is_array($currencies)) {
                $currencies = array($currencies);
            }

            foreach ($currencies as $productCurrency) {

                $currency = $helper->getCurrencyMapping($productCurrency->getCurrencyCode(), $helper::ERP_TO_MAGENTO);
                foreach ($default_stores as $store) {

                    if ($store->getDefaultCurrencyCode() == $currency) {

                        /* @var $store Mage_Core_Model_Store */
                        $product = $this->catalogProductFactory->create()
                            ->setStoreId($store->getId())
                            ->load($productId);

                        /* @var $product \Magento\Catalog\Model\Product */
                        foreach ($product->getTypeInstance(true)->getEditableAttributes($product) as $attribute) {
                            /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
                            //Unset data if object attribute has no value in current
                            if (!$attribute->isStatic() && !$product->getExistsStoreValueFlag($attribute->getAttributeCode()) && !$attribute->getIsGlobal()) {
                                $product->setData($attribute->getAttributeCode(), false);
                            }
                        }

                        $basePrice = $store->roundPrice($productCurrency->getBasePrice(), 4);
                        $breakInfo = $this->_processProductBreaks($productCurrency, $productCurrency->getCustomerPrice(), $eccProduct);
                        $customerPrice = $store->roundPrice($breakInfo['customerPrice'], 4);
                        $minimalPrice = $store->roundPrice($breakInfo['minimalPrice'], 4);

                        if ($customerPrice > $basePrice || $customerPriceUsed || $product->getTypeId() == 'bundle') {
                            // NOTe Bundle products cannot have special prices like other products as it's expecting a percentage, not a price!
                            $product->setPrice($customerPrice);
                        } else {
                            $product->setPrice($basePrice);
                            $product->setSpecialPrice($customerPrice);
                        }

                        $product->setMinimalPrice($minimalPrice);
                        $product->setMinPrice($minimalPrice);
                        $product->setCustomerPrice($customerPrice);
                        $product->setTierPrice($breakInfo['tierPrices']);

                        $product->save();
                        $product->clearInstance();
                        unset($product);
                    }
                }
            }
        }
    }

    /**
     * Processes saving stock information
     *
     * @param Epicor_Comm_Model_Product $product
     * @param Epicor_Common_Model_Xmlvarien $erpProduct
     * @return array
     */
    private function _processSaveStock($product, $erpProduct)
    {

        $erpProductArray = [];
        //if coming from stk, erpproduct is object, if from cron it is an array. standardise them  
        if (is_object($erpProduct)) {
            if ($erpProduct->getFreeStock()) {
                $erpProductArray['free_stock'] = $erpProduct->getFreeStock();
            }
            if ($erpProduct->getMaximumOrderQty()) {
                $erpProductArray['maximum_order_qty'] = $erpProduct->getMaximumOrderQty();
            }
            if ($erpProduct->getMinimumOrderQty()) {
                $erpProductArray['minimum_order_qty'] = $erpProduct->getMinimumOrderQty();
            }
        } else {
            if (isset($erpProduct['freeStock'])) {
                $erpProductArray['free_stock'] = $erpProduct['freeStock'];
            }
            if (isset($erpProduct['maximumOrderQty'])) {
                $erpProductArray['maximum_order_qty'] = $erpProduct['maximumOrderQty'];
            }
            if (isset($erpProduct['minimumOrderQty'])) {
                $erpProductArray['minimum_order_qty'] = $erpProduct['minimumOrderQty'];
            }
        }
        $changes = array();
        $reindex = false;

        $stockItem = $this->catalogInventoryStockItemFactory->create();
        $stockItem->getResource()->loadByProductId($stockItem, $product->getId(), $stockItem->getStockId());

        $stockDataArray = array();
        $updateFreeStock = $this->scopeConfig->isSetFlag(self::FREE_STOCK_UPDATE);
        //only update free stock if freestock update allowed and manage_stock in product == Y
        if ($updateFreeStock) {
            if (isset($erpProductArray['free_stock'])) {
                $qty = $erpProductArray['free_stock'];
                if ($qty != $stockItem->getQty()) {
                    $inStock = ($qty > 0) ? 1 : 0;
                    $stockDataArray['is_in_stock'] = $inStock;
                    $stockDataArray['qty'] = $qty;
                }
            }
        }
        $updateManageStock = $this->scopeConfig->isSetFlag(self::PRODUCT_MANAGE_STOCK_UPDATE);

        if ($updateManageStock) {
            $stockDataArray['manage_stock'] = $product->getTypeId() == 'grouped' ? 1 : 0;
        }

        $updateMaxQty = $this->scopeConfig->isSetFlag(self::PRODUCT_MAX_ORDER_QTY_UPDATE);
        if ($updateMaxQty) {
            $max = isset($erpProductArray['maximum_order_qty']) ? (float)$erpProductArray['maximum_order_qty'] : false;
            if ($max != $stockItem->getMaxSaleQty()) {
                $stockDataArray['max_sale_qty'] = $max;
                $stockDataArray['use_config_max_sale_qty'] = $max === false ? 1 : 0;
            }
        }

        $updateMinQty = $this->scopeConfig->isSetFlag(self::PRODUCT_MIN_ORDER_QTY_UPDATE);
        if ($updateMinQty) {
            $min = isset($erpProductArray['minimum_order_qty']) ? (float)$erpProductArray['minimum_order_qty'] : false;
            if ($min != $stockItem->getMinSaleQty()) {
                $stockDataArray['min_sale_qty'] = $min;
                $stockDataArray['use_config_min_sale_qty'] = $min === false ? 1 : 0;
            }
        }

        if (empty($stockDataArray) == false) {
            $stockItemId = $stockItem->getId();

            if (!$stockItemId) {
                $stockItem->setData('stock_id', 1);
            }

            $stockItem->setData('product_id', $product->getId());
            $stockItem->setProduct($product);

            foreach ($stockDataArray as $field => $value) {
                $stockItem->setData($field, $value ? $value : 0);
            }

            $stockItem->save();
            $reindex = true;
        }

        return array(
            'reindex' => $reindex,
            'changes' => $changes
        );
    }


    public function catalogRuleRuleFactory()
    {
        if (!$this->catalogRuleRuleFactoryExist) {
            $this->catalogRuleRuleFactoryExist = $this->catalogRuleRuleFactory->create();
        }
        return $this->catalogRuleRuleFactoryExist;
    }

    /**
     * Deals with break information in a product Array response
     *
     * @param $currencyInfo
     * @param $customerPrice
     * @param $product
     * @param null $hideTier
     * @param int $websiteId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function _processProductBreaksArray($currencyInfo, $customerPrice, $product, $hideTier = null, $websiteId = 0)
    {
        $store = $this->storeManager->getStore();
        /* @var $store \Epicor\Comm\Model\Store */
        $rule = $this->catalogRuleRuleFactory();
        /* @var $rule \Magento\CatalogRule\Model\Rule */
        $minimalPrice = $customerPrice;

        $tierPrices = array();

        //Rounding must be done in the msq before the BSV 
        //_sendMsqForBsvItems
        $preventRounding = ($this->getPreventRounding()) ? "4" : "2";

        if (isset($currencyInfo['breaks']) &&
            isset($currencyInfo['breaks']['break'])) {
            $qtyOne = false;
            $breaksArray = $this->_getGroupedDataArray('breaks', 'break', $currencyInfo);

            foreach ($breaksArray as $break) {
                if (is_array($break['price']) && empty($break['price'])) {
                    continue;
                }
                if ($this->getAllowPriceRules()) {
                    $price = $rule->calcProductPriceRule($product, $break['price']);
                    if (!$price) {
                        $price = $break['price'];
                    }
                } else {
                    $price = $break['price'];
                }

                if ($break['quantity'] > 1) {
                    $tierPrices[] = array(
                        'website_id' => $websiteId,
                        'cust_group' => 32000, // All groups
                        'price_qty' => $break['quantity'],
                        'price' => $store->roundPrice($price, $preventRounding),
                        'website_price' => $store->roundPrice($price, $preventRounding)
                    );

                    $minimalPrice = ($minimalPrice > $price) ? $price : $minimalPrice;
                } else if ($break['quantity'] == 1) {
                    if (!$hideTier) {
                        $customerPrice = $break['price'];
                    }
                }
            }
        }

        return array(
            'tierPrices' => ($hideTier) ? array() : $tierPrices, //If hidetier =1 then don't show tier prices
            'customerPrice' => $customerPrice,
            'minimalPrice' => $minimalPrice,
        );
    }

    /**
     * Deals with break information in a product Object response
     *
     * @param \Epicor\Common\Model\Xmlvarien $erpProduct
     * @param integer $customerPrice
     *
     * @return array
     */

    private function _processProductBreaks($currencyInfo, $customerPrice, $product, $hideTier = null)
    {
        $store = $this->storeManager->getStore();
        /* @var $store \Epicor\Comm\Model\Store */
        $rule = $this->catalogRuleRuleFactory->create();
        /* @var $rule \Magento\CatalogRule\Model\Rule */
        $minimalPrice = $customerPrice;

        $tierPrices = array();

        //Rounding must be done in the msq before the BSV 
        //_sendMsqForBsvItems
        $preventRounding = ($this->getPreventRounding()) ? "4" : "2";

        if ($currencyInfo->getBreaks() != null &&
            $currencyInfo->getBreaks()->getBreak() != null) {
            $qtyOne = false;
            $breaksArray = $currencyInfo->getBreaks()->getasarrayBreak();

            foreach ($breaksArray as $break) {
                if ($this->getAllowPriceRules()) {
                    $price = $rule->calcProductPriceRule($product, $break->getPrice());
                    if (!$price) {
                        $price = $break->getPrice();
                    }
                } else {
                    $price = $break->getPrice();
                }

                if ($break->getQuantity() > 1) {
                    $tierPrices[] = array(
                        'website_id' => 0,
                        'cust_group' => 32000, // All groups
                        'price_qty' => $break->getQuantity(),
                        'price' => $store->roundPrice($price, $preventRounding),
                        'website_price' => $store->roundPrice($price, $preventRounding)
                    );

                    $minimalPrice = ($minimalPrice > $price) ? $price : $minimalPrice;
                } else if ($break->getQuantity() == 1) {
                    if (!$hideTier) {
                        $customerPrice = $break->getPrice();
                    }
                }
            }
        }

        return array(
            'tierPrices' => ($hideTier) ? array() : $tierPrices, //If hidetier =1 then don't show tier prices
            'customerPrice' => $customerPrice,
            'minimalPrice' => $minimalPrice,
        );
    }

    /* Get actions using catalog price rule of product
     *
     * @param Mage_Catalog_Model_Product $product
     * @param float $price
     * @return float|null
     */

    public function getRuleActions(\Magento\Catalog\Model\Product $product, $price)
    {
        $priceRules = null;
        $productId = $product->getId();
        $storeId = $product->getStoreId();
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        if ($product->hasCustomerGroupId()) {
            $customerGroupId = $product->getCustomerGroupId();
        } else {
            $customerGroupId = $this->customerSession->getCustomerGroupId();
        }
        //M1 > M2 Translation Begin (Rule p2-6.4)
        //$dateTs = Mage::app()->getLocale()->date()->getTimestamp();
        $dateTs = $this->_localeResolver->getLocale()->date()->getTimestamp();
        //M1 > M2 Translation End
        $cacheKey = date('Y-m-d', $dateTs) . "|$websiteId|$customerGroupId|$productId|$price";
        $discount = array();

        if (!array_key_exists($cacheKey, self::$_priceRulesData)) {
            $rulesData = $this->catalogRuleResourceModelRuleFactory->create()->getRulesFromProduct($dateTs, $websiteId, $customerGroupId, $productId);
            foreach ($rulesData as $ruleData) {
                $discount['action'] = $ruleData['action_operator'];
                $discount['discountAmount'] = $ruleData['action_amount'];
                return $discount;
            }
        } else {
            return null;
        }
        return null;
    }

    /**
     * P21 maximumContractQty remove array and set false
     * when null tag should get from ERP
     *
     * @param array $contract
     * @return array
     */
    public function validateContractArray($contract)
    {
        if ($contract && is_array($contract) && count($contract) > 0) {
            if (isset($contract[0])) {
                foreach ($contract as $contractKey => $contractValue) {
                    if (isset($contractValue["maximumContractQty"]) && is_array($contractValue["maximumContractQty"])) {
                        $contract[$contractKey]["maximumContractQty"] = false;
                    }
                }
            }
        }

        return $contract;
    }

    /**
     * Processes saving stock information
     *
     * @param Epicor_Comm_Model_Product $product
     * @param Epicor_Common_Model_Xmlvarien $erpProduct
     * @return array
     */
    private function _processSavePrices($product, $erpProduct)
    {
        $helper = $this->getHelper();
        $default_stores = $this->getHelper()->getDefaultStores();
        //   $currencies = $this->_getGroupedData('currencies', 'currency', $erpProduct);
        $currencies = isset($erpProduct['currencies']) ? $erpProduct['currencies'] : [];
        $customerPriceUsed = $this->getConfig('cusomterpriceused');
        $priceScope = $this->scopeConfig->getValue("catalog/price/scope", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $reindex = false;
        $productChanges = array();
        $this->setPreventRounding(true);
        foreach ($currencies as $productCurrency) {
            $dataObject = new \Magento\Framework\DataObject();
            $productCurrency = $dataObject->setData($productCurrency);
            $currencyCode = $productCurrency->getData('currencyCode');
            $currency = $helper->getCurrencyMapping($currencyCode, $helper::ERP_TO_MAGENTO);
            foreach ($default_stores as $store) {
                $defaultCurrencyCode = $this->storeManager->getStore($store['store_id'])->getDefaultCurrencyCode();
                /* @var $store Mage_Core_Model_Store */
                if ($defaultCurrencyCode != $currency) {
                    continue;
                }

                $storeProduct = $this->catalogProductFactory->create()->setStoreId($store->getId())->load($product->getId());


                //      $storeProduct = Mage::getModel('catalog/product')->setStoreId($store->getId())->load($product->getId());
                /* @var $storeProduct Epicor_Comm_Model_Product */
                $basePrice = $store->roundPrice($productCurrency->getData('basePrice'), 4);
                $websiteId = $priceScope == 1 ? $store->getWebsiteId() : 0;
                $breakInfo = $this->_processProductBreaksArray($productCurrency, $productCurrency->getData('customerPrice'), $product, null, $websiteId);
                $customerPrice = $store->roundPrice($breakInfo['customerPrice'], 4);
                $minimalPrice = $store->roundPrice($breakInfo['minimalPrice'], 4);

                $changes = array();

                if ($minimalPrice != $storeProduct->getMinimalPrice()) {
                    $changes['minimal_price'] = $minimalPrice;
                }

                if ($customerPrice >= $basePrice || $customerPriceUsed || $product->getTypeId() == 'bundle') {
                    // NOTe Bundle products cannot have special prices like other products as it's expecting a percentage, not a price!
                    $price = $customerPrice;
                    $specialPrice = null;
                } else {
                    $price = $basePrice;
                    $specialPrice = $customerPrice;
                }

                if ($price != $storeProduct->getPrice()) {
                    $changes['price'] = $price;
                }

                if ($specialPrice != $storeProduct->getSpecialPrice()) {
                    $changes['special_price'] = $specialPrice;
                }

                if (count($changes) > 0) {
                    if (isset($productChanges[$store->getStoreId()])) {
                        $productChanges[$store->getStoreId()] = array_merge($productChanges[$store->getStoreId()], $changes);
                    } else {
                        $productChanges[$store->getStoreId()] = $changes;
                    }
                    $reindex = true;
                }

                $tierPriceChanges = $this->checkTierPriceChanges(
                    $breakInfo['tierPrices'],
                    $storeProduct->getTierPrice(),
                    $websiteId
                );
                if (empty($tierPriceChanges['delete']) == false && $product->getTypeId() != 'grouped') {
                    $this->updateTierPrices($storeProduct, $product, $tierPriceChanges['delete'], "delete");
                    $reindex = true;
                    $current = $storeProduct->getTierPrice();
                    foreach ($current as $key => $val) {
                        if (isset($tierPriceChanges['delete'][$key])) {
                            unset($current[$key]);
                        }
                    }
                    $storeProduct->setTierPrice($current);
                }

                if (empty($tierPriceChanges['new']) == false && $product->getTypeId() != 'grouped') {
                    $this->updateTierPrices($storeProduct, $product, $tierPriceChanges['new'], "new");
                    $reindex = true;
                }
            }
        }

        return array(
            'reindex' => $reindex,
            'changes' => $productChanges
        );
    }

    /**
     * Checks the tier prices to see if there are any new ones / ones to be deleted
     *
     * @param array $newBreaks
     * @param array $oldBreaks
     * @param integer $websiteId
     *
     * @return int
     */
    private function checkTierPriceChanges($newBreaks, $oldBreaks, $websiteId)
    {
        $oldBreaks = !empty($oldBreaks) ? $oldBreaks : array();

        foreach ($newBreaks as $newKey => $newBreak) {
            foreach ($oldBreaks as $oldKey => $oldBreak) {
                if (
                    $newBreak['website_id'] == $oldBreak['website_id'] &&
                    $newBreak['cust_group'] == $oldBreak['cust_group'] &&
                    $newBreak['price_qty'] == $oldBreak['price_qty'] &&
                    $newBreak['price'] == $oldBreak['price'] &&
                    $newBreak['website_price'] == $oldBreak['website_price']
                ) {
                    unset($newBreaks[$newKey]);
                    unset($oldBreaks[$oldKey]);
                    break;
                }
            }
        }

        $delete = array();

        // any breaks left here are old
        // need to be deleted if they're not for website id
        if (!empty($oldBreaks)) {
            foreach ($oldBreaks as $key => $oldBreak) {
                if ($oldBreak['website_id'] == $websiteId) {
                    $oldBreak['delete'] = 1;
                    $delete[$key] = $oldBreak;
                }
            }
        }

        return array(
            'new' => $newBreaks,
            'delete' => $delete
        );
    }

    /**
     * @param $storeProduct
     * @param $product
     * @param $tierPriceChanges
     * @param $status
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function updateTierPrices($storeProduct, $product, $tierPriceChanges, $status)
    {
        /* @var $product \Epicor\Comm\Model\Product */
        $current = $storeProduct->getTierPrice();
        if (empty($current) == true) {
            $product->setTierPrice($tierPriceChanges);
        } else {
            if ($status == "new") {
                $tier = array_merge($current, $tierPriceChanges);
            } else {
                $tier = array_replace($current, $tierPriceChanges);
            }
            $product->setTierPrice($tier);
        }
        $tirePriceAttr = $product->getResource()->getAttribute('tier_price');
        /* @var $tirePriceAttr \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
        $backend = $tirePriceAttr->getBackend();
        /* @var $backend \Magento\Catalog\Model\Product\Attribute\Backend\Tierprice */
        if ($backend->validate($product)) {
            $backend->beforeSave($product);
            //$backend->afterSave($product);
            $this->SaveTirePrice($product, $backend);
        }
    }

    /**
     * @return \Magento\Framework\EntityManager\MetadataPool|mixed
     */
    private function getMetadataPool()
    {
        if (null === $this->metadataPool) {
            $this->metadataPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\EntityManager\MetadataPool::class);
        }
        return $this->metadataPool;
    }

    /**
     * Save tier price <bracks>
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param \Magento\Catalog\Model\Product\Attribute\Backend\Tierprice $backend
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function SaveTirePrice($product, $backend): Msq
    {
        $websiteId = $this->storeManager->getStore($product->getStoreId())->getWebsiteId();
        $isGlobal = $backend->getAttribute()->isScopeGlobal() || $websiteId == 0;
        $priceRows = $product->getData($backend->getAttribute()->getName());
        if (null === $priceRows) {
            return $this;
        }
        $priceRows = array_filter((array)$priceRows);

        $old = [];
        $new = [];

        // prepare original data for compare
        $origPrices = $product->getOrigData($backend->getAttribute()->getName());
        if (!is_array($origPrices)) {
            $origPrices = [];
        }

        foreach ($origPrices as $data) {
            if ($data['website_id'] > 0 || $data['website_id'] == '0' && $isGlobal) {
                $key = implode(
                    '-',
                    array_merge(
                        [$data['website_id'], $data['cust_group']],
                        ["qty" => $data['price_qty'] * 1]
                    )
                );
                $old[$key] = $data;
            }
        }

        // prepare data for save
        foreach ($priceRows as $data) {
            $hasEmptyData = false;
            $additionalUniqueField = array("qty" => $data['price_qty'] * 1);
            foreach ($additionalUniqueField as $field) {
                if (empty($field)) {
                    $hasEmptyData = true;
                    break;
                }
            }

            if ($hasEmptyData || !isset($data['cust_group']) || !empty($data['delete'])) {
                continue;
            }
            if ($backend->getAttribute()->isScopeGlobal() && $data['website_id'] > 0) {
                continue;
            }
            if (!$isGlobal && (int)$data['website_id'] == 0) {
                continue;
            }

            $key = implode(
                '-',
                array_merge([$data['website_id'], $data['cust_group']], $additionalUniqueField)
            );


            $useForAllGroups = $data['cust_group'] == $this->_groupManagement->getAllCustomersGroup()->getId();
            $customerGroupId = !$useForAllGroups ? $data['cust_group'] : 0;
            $percentageValue = isset($data['percentage_value']) && is_numeric($data['percentage_value'])
                ? $data['percentage_value']
                : null;
            $additionalField = [
                'value' => $percentageValue ? null : $data['price'],
                'percentage_value' => $percentageValue ?: null,
            ];
            $new[$key] = array_merge(
                $additionalField,
                [
                    'website_id' => $data['website_id'],
                    'all_groups' => $useForAllGroups ? 1 : 0,
                    'customer_group_id' => $customerGroupId,
                    'value' => isset($data['price']) ? $data['price'] : null,
                ],
                $additionalUniqueField
            );
        }

        $delete = array_diff_key($old, $new);
        $insert = array_diff_key($new, $old);
        $update = array_intersect_key($new, $old);

        $isChanged = false;
        $productId = $product->getData($this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField());

        //Delete
        if (!empty($delete)) {
            foreach ($delete as $data) {
                $backend->getResource()->deletePriceData($productId, null, $data['price_id']);
                $isChanged = true;
            }
        }

        //Insert
        if (!empty($insert)) {
            foreach ($insert as $data) {
                $price = new \Magento\Framework\DataObject($data);
                $price->setData(
                    $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField(),
                    $productId
                );
                $backend->getResource()->savePriceData($price);

                $isChanged = true;
            }
        }

        //Update
        if (!empty($update)) {
            foreach ($update as $key => $value) {
                if ($old[$key]['price'] != $value['value']) {
                    $price = new \Magento\Framework\DataObject(
                        [
                            'value_id' => $old[$key]['price_id'],
                            'value' => $value['value']
                        ]
                    );
                    $backend->getResource()->savePriceData($price);
                    $isChanged = true;
                }
            }
        }

        if ($isChanged) {
            $valueChangedKey = $backend->getAttribute()->getName() . '_changed';
            $product->setData($valueChangedKey, 1);
        }

        return $this;
    }

    /**
     * Get current selected branch
     * @return mixed
     */
    private function getCurrentBranch()
    {
        if (is_null($this->currentBranch)) {
            $this->currentBranch = $this->branchPickupHelperFactory->getSelectedBranch();
        }
        return $this->currentBranch;
    }

    /**
     * Gets Product from cache
     * @param \Epicor\Comm\Model\Product $product
     * @param array $cacheKeys
     * @param string $key
     * @return false|mixed
     */
    private function getCachedProducts($product, $cacheKeys, $key)
    {
        $cacheProduct = $this->commonMessagingCacheHelper->getCache(self::MESSAGE_TYPE, $cacheKeys);
        $isMsgArray = in_array(self::MESSAGE_TYPE, $this->commHelper->getArrayMessages());
        if ($cacheProduct
            && $this->getLocationsEnabled()
            && !isset($this->locationscache[$key])
        ) {
            $locData = [];
            $locCodes = [];
            $stockVisibility = $this->getLocationHelper()->getStockVisibilityFlag();
            $selectedBranch = $this->getCurrentBranch();
            if (in_array($stockVisibility, ['default'])
                && is_null($selectedBranch)
            ) {
                $locCodes = [$this->getLocationHelper()->getDefaultLocationCode()];
            } else {
                $locations = $product->getCustomerLocations();
                $locations = $this->_getLocations($locations, true);
                foreach ($locations as $subDataArray) {
                    foreach ($subDataArray as $value) {
                        if (isset($value[0])) {
                            $locCodes[] = $value[0];
                        }
                    }
                }
            }
            foreach ($locCodes as $code) {
                $locCacheKeys = $cacheKeys;
                $locCacheKeys[] = $code;
                $cacheLoc = $this->commonMessagingCacheHelper->getCache(self::MESSAGE_TYPE, $locCacheKeys);
                if (!$cacheLoc) {
                    $cacheProduct = false;
                    break;
                }
                $locData[] = $cacheLoc;
            }
            if (!empty($locData) && $cacheProduct) {
                $this->locationscache[$key] = $locData;
                if ($isMsgArray) {
                    $cacheProduct['locations']['location'] = $locData;
                } else {
                    $cacheProduct->getParts()->getPart()->getLocations()->setLocation($locData);
                }
            }
        }
        return $cacheProduct;
    }

    /**
     * Updates location info based on the ERP
     * Array response
     *
     * @param \Epicor\Comm\Model\Product $eccProduct
     * @param \Epicor\Common\Model\Xmlvarien $erpProduct
     */
    protected function _processLocationsUpdate($eccProduct, $erpProduct)
    {
        $locations = $this->_getGroupedDataArray('locations', 'location', $erpProduct);
        $helper = $this->getHelper();
        $productLocations = $eccProduct->getLocations();
        $processedLocations = array();

        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */
        $updateFlags = array(
            'stock_status' => $this->scopeConfig->isSetFlag(self::LOCATION_STOCK_STATUS, ScopeInterface::SCOPE_STORE),
            'free_stock' => $this->scopeConfig->isSetFlag(self::LOCATION_FREE_STOCK, ScopeInterface::SCOPE_STORE),
            'minimum_order_qty' => $this->scopeConfig->isSetFlag(self::LOCATION_MIN_ORDER_QTY, ScopeInterface::SCOPE_STORE),
            'maximum_order_qty' => $this->scopeConfig->isSetFlag(self::LOCATION_MAX_ORDER_QTY, ScopeInterface::SCOPE_STORE),
            'lead_time_days' => $this->scopeConfig->isSetFlag(self::LOCATION_LEAD_TIME_DAYS, ScopeInterface::SCOPE_STORE),
            'lead_time_text' => $this->scopeConfig->isSetFlag(self::LOCATION_LEAD_TIME_TEXT, ScopeInterface::SCOPE_STORE),
            'supplier_brand' => $this->scopeConfig->isSetFlag(self::LOCATION_SUPPLIER_BRAND, ScopeInterface::SCOPE_STORE),
            'tax_code' => $this->scopeConfig->isSetFlag(self::LOCATION_TAX_CODE, ScopeInterface::SCOPE_STORE),
            'manufacturers' => $this->scopeConfig->isSetFlag(self::LOCATION_MANUFACTURERS, ScopeInterface::SCOPE_STORE),
            'currencies' => $this->scopeConfig->isSetFlag(self::LOCATION_PRICING, ScopeInterface::SCOPE_STORE),
        );
        $requiredUpdateFlags = [];
        $mappings = [];
        foreach ($locations as $erpLocation) {
            if (isset($productLocations[$erpLocation['locationCode']])) {
                $processedLocations[] = $erpLocation['locationCode'];
                $location = $productLocations[$erpLocation['locationCode']];

                /* @var $location \Epicor\Comm\Model\Location\Product */
                if (isset($erpLocation['freeStock']) && $updateFlags['stock_status']) {
                    $location->setStockStatus($erpLocation['freeStock']);
                    $requiredUpdateFlags['stock_status'] = true;
                }
                if (isset($erpLocation['freeStock']) && $updateFlags['free_stock']) {
                    $location->setFreeStock($erpLocation['freeStock']);
                    $requiredUpdateFlags['free_stock'] = true;
                }
                if (isset($erpLocation['minimumOrderQty']) && $updateFlags['minimum_order_qty']) {
                    $location->setMinimumOrderQty($erpLocation['minimumOrderQty']);
                    $requiredUpdateFlags['minimum_order_qty'] = true;
                }
                if (isset($erpLocation['maximumOrderQty']) && $updateFlags['maximum_order_qty']) {
                    $location->setMaximumOrderQty($erpLocation['maximumOrderQty']);
                    $requiredUpdateFlags['maximum_order_qty'] = true;
                }
                if (isset($erpLocation['leadTime']) && $updateFlags['lead_time_days']) {
                    if (is_array($erpLocation['leadTime'])) {
                        $location->setLeadTimeDays(implode(' ', $erpLocation['leadTime']));
                    } else {
                        $location->setLeadTimeDays($erpLocation['leadTime']);
                    }
                    $requiredUpdateFlags['lead_time_days'] = true;
                }
                if (isset($erpLocation['leadTimeText']) && $updateFlags['lead_time_text']) {
                    if (is_array($erpLocation['leadTimeText'])) {
                        $location->setLeadTimeText(implode(' ', $erpLocation['leadTimeText']));
                    } else {
                        $location->setLeadTimeText($erpLocation['leadTimeText']);
                    }
                    $requiredUpdateFlags['lead_time_text'] = true;
                }
                if (isset($erpLocation['currencies']) && $updateFlags['currencies']) {
                    $currencies = $this->_getGroupedDataArray('currencies', 'currency', $erpLocation);
                    foreach ($currencies as $erpCurrency) {
                        if (!isset($mappings[$erpCurrency['currencyCode']])) {
                            $currencyCode = $helper->getCurrencyMapping($erpCurrency['currencyCode'], $helper::ERP_TO_MAGENTO);
                            $mappings[$erpCurrency['currencyCode']] = $currencyCode;
                        } else {
                            $currencyCode = $mappings[$erpCurrency['currencyCode']];
                        }
                        $currencyObj = $this->commLocationProductCurrencyFactory->create();

                        //check if location currency exists, if so use the id to update it
                        $currencyCollection = $this->commLocationProductCurrencyCollectionFactory->create();
                        $currencyCollection->addFieldToFilter('product_id', $eccProduct->getId());
                        $currencyCollection->addFieldToFilter('location_code', $erpLocation['locationCode']);
                        $currencyCollection->addFieldToFilter('currency_code', $currencyCode);
                        $currencyItem = $currencyCollection->getFirstItem();
                        if ($currencyItem->getId()) {
                            $currencyObj->setId($currencyItem->getId());
                        }

                        $currencyObj->setBasePrice($erpCurrency['basePrice']);
                        $currencyObj->setCustomerPrice($erpCurrency['customerPrice']);
                        $locCurrbreaks = (isset($erpCurrency['breaks'])) ? $erpCurrency['breaks'] : '';
                        $currencyObj->setBreaks($locCurrbreaks);
                        $currencyObj->setProductId($eccProduct->getId());
                        $currencyObj->setLocationCode($location->getLocationCode());
                        $currencyObj->setCurrencyCode($currencyCode);
                        $currencyObj->setCostPrice($erpCurrency['costPrice']);

                        $location->setCurrencyObject($currencyCode, $currencyObj);
                        $currencyArray = [$currencyObj];
                        $location->setData('currencies', $currencyArray);
                        unset($currencyObj);
                    }
                }
                $locationModel = $eccProduct->setLocationData($location->getLocationCode(), $location, array($currencyCode), $updateFlags);
                $locationModel->save();
            }
        }
    }

    /**
     * Checks if locations enabled/disabled
     * @return boolean
     */
    private function getLocationsEnabled()
    {
        if (is_null($this->locationsEnabled)) {
            $this->locationsEnabled = $this->getLocationHelper()->isLocationsEnabled();
        }
        return $this->locationsEnabled;
    }
}
