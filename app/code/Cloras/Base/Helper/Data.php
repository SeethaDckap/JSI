<?php

namespace Cloras\Base\Helper;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
//use Magento\Customer\Model\SessionFactory;
use Magento\Customer\Model\Customer;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Pricing\Helper\Data as PricingHelperData;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Client\Curl as ClientCurl;
use Psr\Log\LoggerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Bundle\Model\Product\Type;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    private $logger;

    const ERP_SYSTEM = 'P21';

    protected $configurableProduct;

    protected $bundleProduct;

    public function __construct(
        LoggerInterface $logger,
        DirectoryList $dir,
        File $io,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        Customer $customer,
        ClientCurl $curl,
        ScopeConfigInterface $scopeConfig,
        PricingHelperData $priceHelper,
        CollectionFactory $productCollectionFactory,
        ProductFactory $catalogProductFactory,
        ProductRepository $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StockRegistryInterface $stockRegistry,
        Configurable $configurableProduct,
        Type $bundleProduct
    ) {
        $this->logger                = $logger;
        $this->dir                   = $dir;
        $this->io                    = $io;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->customer        = $customer;
        $this->curlClient            = $curl;
        $this->scopeConfig         = $scopeConfig;
        $this->priceHelper        = $priceHelper;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->productRepository        = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->stockRegistry = $stockRegistry;
        $this->configurableProduct = $configurableProduct;
        $this->bundleProduct = $bundleProduct;
    }

    public function makeDir($folderName = 'cloras')
    {
        try {
            $logFolder = BP . '/var/log/' . $folderName;
            if ($logFolder != '') {
                $this->io->mkdir($logFolder, 0777);
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }//end makeDir()

    public function clientCurl($method, $serviceUrl, $headers = [], $requestData = '')
    {
        try {

            $response = '';

            $client = new \Zend_Http_Client();

            $client->setUri($serviceUrl);

            $client->setConfig(['timeout'=>3000]);
            
            if ($requestData) {
                $client->setRawData($requestData);
            }
              
            $client->setHeaders($headers);

            $response = $client->request($method);
            
            $this->logger->info('Curl Response Status : ', (array)$response->getStatus());
            
            return $response;
        } catch (\Exception $e) {
            
            $this->logger->info('Curl Error : ', (array)$e->getMessage());
        }
        return $response;
    }

    /**
     * Get refresh token
     *
     * @param $serviceUrl
     * @param $batchId
     * @return boolean
     * @deprecated 3.0
     */
    public function refreshToken($serviceUrl, $batchId)
    {
        try {
            $serviceUrl .= '/token/asap/' . $batchId;
            $response    = $this->clientCurl($method = 'GET', $serviceUrl, $headers = [], $requestData = []);
            if ($response->getStatus() == 200) {
                $results = json_decode($response->getBody(), true);
                if (!empty($results)) {
                    if (strtolower($results['status']) == 'success') {
                        $this->logger->info('Cloras response : ', $results);
                        return true;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->info('Cloras response : ', (array) $e->getMessage());
            return false;
        }
        return false;
    }


    public function fetchAPIData(
        $productIds,
        $type,
        $sessionPrices = [],
        $qty = 0,
        $isCurrency = 1
    ) {

        $finalResponse = [];
        if (!empty($productIds)) {
            
            $productItems = $this->getProductItems(
                $productIds,
                $sessionPrices,
                $qty
            );

            //print_r($productItems);exit();

            $responseData = [];

            $prices = [];
            $payloadItem = [];

            $erpSystem = 'P21';
            $responseFilterKey = 'erp_product_id';//change this based on ERP

            $payloadData = [];

            $baseURL = $this->getConfigValue('clorasbase/general/baseurl');

            $token = $this->getConfigValue('clorasbase/general/token');

            if ($type == 'fetch_inventory') {
                $apiPath = $this->getConfigValue('clorasbase/dynamic_inventory/api_path');
            } elseif ($type == 'fetch_price') {
                $apiPath = $this->getConfigValue('clorasbase/dynamic_pricing/api_path');
            }



            $serviceUrl = trim($baseURL.$apiPath);


            if (!empty($serviceUrl) && !empty($token)) {

                /*Modify the payload based on ERP system*/
                $payloadData = $this->getPayloadData(
                    $productItems,
                    $responseFilterKey,
                    $erpSystem
                );

                $authValue = 'Bearer ' . $token;

                $headers = ['Authorization' => $authValue,'Content-Type' =>'application/json'];

                $method = 'POST';

                $requestData = json_encode($payloadData);

                $response = $this->clientCurl($method, $serviceUrl, $headers, $requestData);

                if (is_object($response)) {
                   
                    if ($response->getStatus() == 200) {

                        $results = json_decode($response->getBody(), true);

                        if (!empty($results)) {
                            /*Modify the responseData based on ERP*/
                            $responseData = $this->getResponseData(
                                $results,
                                $type,
                                $responseFilterKey,
                                $erpSystem
                            );
                        }
                                   
                    }
                }
            }


            $finalResponse = $this->getMatchedProducts(
                $productItems,
                $responseData,
                $responseFilterKey,
                $isCurrency
            );
            
        }

        return $finalResponse;
    }

    private function getPayloadData($productItems, $responseFilterKey, $erp = 'P21')
    {

        $payloadData = [];
        $sku = '';
        foreach ($productItems as $item) {
            if (array_key_exists('item_id', $item)) {
                $sku = $item['item_id'];
                break;
            }
        }

        $customerData = $this->getCustomerData();

        if ($erp === 'P21') {
            $payloadItem = [];
            foreach ($productItems as $item) {
                $sku = $item['item_id'];
                if (!empty($item)) {
                    $productStock = $stockRegistry->getStockItem($product->getId());

                    $payloadItem[] = $item;
                }
            }

            $payloadData['products'] = $payloadItem;
            if (array_key_exists('erp_customer_id', $customerData)) {
                $payloadData['customer_id'] = $customerData['erp_customer_id'];
            }
            

        } elseif ($erp == 'Eclipse') {
            if (!empty($customerData)) {
                if (array_key_exists('erp_customer_id', $customerData)) {
                    $payloadData['CustomerId'] = $customerData['erp_customer_id'];
                }
            }
            $payloadData['CatalogNumber'] = $sku;
        }

        return $payloadData;
    }

    private function getResponseData($results, $type, $responseFilterKey, $erp = 'P21')
    {
        
        if ($erp == 'P21') {
            if ($type == 'fetch_price') {
                if (!empty($results['data'])) {
                    foreach ($results['data'] as $key => $resultsData) {
                      
                        if (array_key_exists('unit_price', $resultsData)) {

                            $unitPrice = (($resultsData['unit_price'] != 0) ? $resultsData['unit_price'] : 0);
                            $responseData[$resultsData[$responseFilterKey]]['price'] = $unitPrice;
                        }

                    }
                }
            }
        } elseif ($erp == 'Eclipse') {
            if ($type == 'fetch_price') {

                if (!empty($results['data'])) {
                
                    $resultsData = $results['data'];
                    if (array_key_exists('productUnitPrice', $resultsData)) {
                        
                        if (array_key_exists('value', $resultsData['productUnitPrice'])) {
                            
                            $unitPrice = (($resultsData['productUnitPrice']['value'] != 0) ? $resultsData['productUnitPrice']['value'] : 0);

                            $responseData[$resultsData['productId']]['price'] = $unitPrice;
                           
                            /**/
                            //$this->updateProductDetails($sku, $resultsData['productId'], $filterKey);
                           
                        }
                    }
                
                }
            } elseif ($type == 'fetch_inventory') {
                if (!empty($results['data'])) {

                    if (array_key_exists('data', $results['data'])) {
                        foreach ($results['data'] as $resultsData) {

                            $totalWarehouseQty = (($resultsData['totalWarehouseQty'] != 0) ? $resultsData['totalWarehouseQty'] : 0);

                            $responseData[$resultsData['productId']]['qty'] = $totalWarehouseQty;
                            break;
                        
                        }
                    }
                }
            }
        }

        return $responseData;
    }

    /**
     * Get System config value
     *
     * @param $configPath
     * @return string
     */
    public function getConfigValue($configPath)
    {
        
        $configValue = '';
                
        if (!empty($configPath)) {
            $configValue = $this->scopeConfig->getValue(
                $configPath,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }

        return $configValue;
    }


    public function getMatchedProducts(
        $productItems,
        $responseData,
        $filterBy = 'item_id',
        $isCurrency
    ) {
        
        $products = [];
        foreach ($productItems as $key => $value) {
            $productId = $value['product_id'];
            $qty = $value['qty'];
            //$qty = '10000';
            $api_price = $value['price'];

            if (array_key_exists($filterBy, $value)) {
                if (!empty($value[$filterBy])) {
                    /*PRICE*/
                    if (array_key_exists($value[$filterBy], $responseData)) {
                        if (array_key_exists('price', $responseData[$value[$filterBy]])) {
                            if ($responseData[$value[$filterBy]]['price'] != 0) {
                                $api_price = $responseData[$value[$filterBy]]['price'];
                            }
                        }
                    }

                    /*Inventory*/
                    if (array_key_exists($value[$filterBy], $responseData)) {
                        if (array_key_exists('qty', $responseData[$value[$filterBy]])) {
                            if ($responseData[$value[$filterBy]]['qty'] != 0) {
                                $qty = $responseData[$value[$filterBy]]['qty'];
                            }
                        }
                    }
                }
            }

            $products[] = [
                'productId' => $productId,
                'price'     => $this->priceHelper->currency($api_price, $isCurrency, false),
                'qty'  => $qty
            ];
        }

        return $products;
    }

    
    public function getProductItems($productIds, $sessionPrices, $qty, $filterBy = 'sku')
    {
        $productItems = [];
        if (!empty($productIds)) {

            $productCustomAttributes = ['inv_mast_uid', 'uom', 'erp_product_id'];

            $this->searchCriteriaBuilder->addFilter(
                'entity_id',
                $productIds,
                'in'
            );

            $products = $this->productRepository->getList(
                $this->searchCriteriaBuilder->create()
            )->getItems();
           
            if (!empty($products)) {
                foreach ($products as $product) {
                    $price = 0;
                    $qty = 1;
                    $sku       = $product->getSku();
                    $productId = $product->getId();
                    

                    // Get quantity of product.
                    $productStock = $this->stockRegistry->getStockItem($product->getId());
                
                    $qty = $productStock->getQty();

                    $productItems[$sku]['item_id'] = $sku;
                    $productItems[$sku]['product_id'] = $productId;
                
                    foreach ($productCustomAttributes as $attributeCode) {
                        $productItems[$sku][$attributeCode] = $this->getCustomAttributeValue($product, $attributeCode);
                    }
                    
                    if (array_key_exists('price', $product->getData())) {
                        $price = $product->getData()['price'];
                    }

                    if (array_key_exists('special_price', $product->getData())) {
                        $price = $product->getData()['special_price'];
                    }
                    if ($product->getTypeId() == Configurable::TYPE_CODE){
                        $price = $this->getPriceRange($product);
                    }else if ($product->getTypeId() == Type::TYPE_CODE){
                        $price = $this->getBundlePriceRange($product);
                    }

                    $productItems[$sku]['price'] = $price;

                    $productItems[$sku]['qty'] = $qty;
                }
            }
        }
        
        return $productItems;
    }

    public function getCustomAttributeValue($product, $attribute_code)
    {
        
        $attributeValue = '';
        if (is_object($product->getCustomAttribute($attribute_code))) {
            $attributeText = $product->getAttributeText($attribute_code);
            if (empty($attributeText)) {
                $attributeValue = $product->getCustomAttribute($attribute_code)->getValue();
            }
        }
        return $attributeValue;
    }

    public function getCustomerData()
    {
        $customerData = [];
        try {
            $customerSession = $this->customerSessionFactory->create();
            if ($customerSession->isLoggedIn()) {
                $id = $customerSession->getCustomerId();
                if (!$customerSession->getData('cloras_erp_customer_id')) {
                    $customerData = $this->getCustomerDataById($id, $customerData);
                }
            }
        } catch (\Exception $e) {
            $this->logger->info('Cloras Error : ', (array)$e->getMessage());
        }//end try

        return $customerData;
    }

    private function getCustomerDataById($id, $customerData)
    {
        $customAttributes = [
            'cloras_erp_customer_id' => 'erp_customer_id',
            'cloras_erp_shipto_id' => 'erp_shipto_id',
            'cloras_erp_contact_id' => 'erp_contact_id'
        ];

        $customerData = [];
        $customer = $this->customer->load($id);
        foreach ($customAttributes as $attributeCode => $label) {
            if (array_key_exists($attributeCode, $customer->getData())) {
                $customerData[$label] = $customer->getData($attributeCode);
            }
        }
        
        return $customerData;
    }
   
    public function updateProductDetails($sku, $value, $filterKey)
    {
        $catalogProduct = $this->catalogProductFactory->create();
        try {
            $productId = $catalogProduct->getIdBySku($sku);
            $catalogProduct->load($productId);
            if (is_object($catalogProduct->getCustomAttribute($filterKey))) {
                $catalogProduct->setCustomAttribute($filterKey, $value);
                if ($catalogProduct->save()) {
                    return true;
                } else {
                    return false;
                }
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    public function customizedCustomers($customer)
    {
    
        foreach ($customer->__toArray() as $key => $value) {
        //$customerData[$key] = $value;
            if ($key != 'custom_attributes') {
                $customerData[$key] = $value;
            }
        }
    
        foreach ($customer->getCustomAttributes() as $attributeKey => $attributeValue) {
            $customerData[$attributeValue->getAttributeCode()] = $attributeValue->getValue();
        }

        //$customerDatas = json_decode (json_encode ($customerData), FALSE);
        $obj_merged = (object) $customerData;
             
        //print_r($customerData);exit();
        return $customerData;
    }

    private function getPriceRange($product) {
        $childProductPrice = [];
        $childProducts = $this->configurableProduct->getUsedProducts($product);

        foreach($childProducts as $child) {
            $price = number_format($child->getPrice(), 2, '.', '');
            $finalPrice = number_format($child->getFinalPrice(), 2, '.', '');
            if($price == $finalPrice) {
                $childProductPrice[] = $price;
            } else if($finalPrice < $price) {
                $childProductPrice[] = $finalPrice;
            }
        }
        $min = min($childProductPrice);
        return $min;
    }

    private function getBundlePriceRange($product){
        $childProductPrice = [];
        $childProductsIds = $this->bundleProduct->getChildrenIds($product->getId());

        foreach ($childProductsIds as $childIds){
            if(is_array($childIds)){
                $onlyPra = [];
                foreach ($childIds as $cid){
                    $onlyPra[] = $this->getPriceById($cid);
                }
                $childProductPrice[] = min($onlyPra);
            }
        }

        $ddPrice = '0.00';
        foreach ($childProductPrice as $liPrice){
            $ddPrice += $liPrice;
        }

        $min = $ddPrice;
        return $min;
    }

    private function getPriceById($id)
    {
        $product = $this->catalogProductFactory->create();
        $productPriceById = $product->load($id)->getPrice();
        return number_format($productPriceById, 2, '.', '');;
    }
}//end class
