<?php

namespace Cloras\Base\Repo;

use Cloras\Base\Api\Data\ProductInterfaceFactory;
use Cloras\Base\Api\ProductResultsInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Serialize\Serializer\Json;
use Cloras\Base\Api\Data\ResultsInterfaceFactory;
use Cloras\Base\Api\ProductIndexRepositoryInterface;

class Products implements ProductResultsInterface
{
    private $productInterfaceFactory;

    private $searchCriteriaBuilder;

    private $productRepository;

    private $stockRegistry;

    private $catalogProductFactory;

    private $productsFactory;

    private $storeManager;

    private $productCollectionFactory;

    public function __construct(
        ProductInterfaceFactory $productFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        StockRegistryInterface $stockRegistry,
        Json $jsonHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Store\Model\StoreManagerInterface $store,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Filesystem\DirectoryList $dir,
        ResultsInterfaceFactory $resultsFactory,
        ProductIndexRepositoryInterface $productIndexRepo
    ) {
        $this->productFactory           = $productFactory;
        $this->searchCriteriaBuilder    = $searchCriteriaBuilder;
        $this->productRepository        = $productRepository;
        $this->stockRegistry            = $stockRegistry;
        $this->jsonHelper               = $jsonHelper;
        $this->catalogProductFactory    = $catalogProductFactory;
        $this->storeManager             = $store;
        $this->productCollectionFactory = $productCollectionFactory;

        $this->logger = $logger;
        $this->dir = $dir;
        $this->resultsFactory = $resultsFactory;
        $this->productIndexRepo = $productIndexRepo;
    }//end __construct()

    /**
     * @return \Cloras\Base\Api\Data\ProductInterface
     */
    public function getProducts()
    {
        /*
            @var \Cloras\Addon\Api\Data\ProductItemInterface $products
        */
        $products    = $this->productFactory->create();
        $productData = [];
        try {
            $productFilters = $this->searchCriteriaBuilder->addFilter('status', '1', 'eq')->create();
            $productItems   = $this->productRepository->getList($productFilters)->getItems();
            $productData    = [];
            $partnumber     = '';
            $uom            = '';
            $total_count = count($productItems);
            if ($total_count) {
                foreach ($productItems as $product) {
                    if (is_object($product->getCustomAttribute('partnumber'))) {
                        $partnumber = $product->getCustomAttribute('partnumber')->getValue();
                    }

                    if (is_object($product->getCustomAttribute('uom'))) {
                        $uom = $product->getAttributeText('uom');
                        if (empty($uom)) {
                            $uom = $product->getCustomAttribute('uom')->getValue();
                        }
                    }

                    $productData[] = [
                        'sku'        => $product->getSku(),
                        'partnumber' => $partnumber,
                        'qty'        => 1,
                        'price_uom'  => $uom,
                        'uom'        => $uom,
                    ];
                }
            }
        } catch (\Exception $e) {
            $productData[] = [
                'status' => 'failure',
                'Error'  => $e->getMessage(),
            ];
        }//end try
        $products->setTotalProducts($total_count);
        $products->addProduct($productData);

        return $products;
    }//end getProducts()

    /**
     * @return \Cloras\Base\Api\ProductResultsInterface
     */
    public function updateProductsInventory($productInfo)
    {
       
        // product Inventory Update
        /*
         * @var \Cloras\Base\Api\Data\ProductItemInterface
         */

        $response = [];
        try {
          //  echo('test');
            $productInfo = $this->jsonHelper->unserialize($productInfo);
          //  print_r($productInfo);exit;
            $storeId     = $this->storeManager->getStore()->getId();
            if (array_key_exists('sku', $productInfo)) {
                $sku = $productInfo['sku'];
                if ($sku) {
                    $productId = $this->getProductId($sku);
                    if ($productId) {
                        $productStockInfo = $this->getStockDetails($productInfo);
                        if ($this->updateProductData($productId, $data = [], $storeId, $productStockInfo)) {
                            $response[] = [
                            'status'  => 'success',
                            'message' => $sku . ' updated',
                            ];
                        }
                    } else {
                        $response[] = [
                            'status'  => 'failure',
                            'message' => $sku . ' is not available',
                        ];
                    }//end if
                }//end if
            } else {
                $response[] = [
                    'status'  => 'failure',
                    'message' => 'SKU Key is not present',
                ];
            }//end if
        } catch (\Exception $e) {
            $response[] = [
                'status'  => 'failure',
                'message' => $e->getMessage(),
            ];
        }//end try

        $products->setResponseMessage($response);

        return $products;
    }//end updateProductsInventory()

    /**
     * @return \Cloras\Base\Api\Data\ProductResultsInterface
     */
    public function createProducts($data)
    {
       
        $products     = $this->productFactory->create();
        $messages     = [];
        $output       = [];
        $failed_skus  = [];
        $updatedSku   = [];
        $successCount = 0;
        $failureCount = 0;

        try {
            $this->logger->log(600, print_r($this->jsonHelper->serialize($data), true));
            
            $productData = $this->jsonHelper->unserialize($data);


            $is_price_update = 2;
            $productInfoCount = count($productData);
        
            $filterBy = 'sku';

             $filterData = ['filterby', 'is_price_update'];

            // $reduce = 0;
        //    foreach ($productData as $countKey => $productsDetails) {
                // foreach ($filterData as $filterKey) {
                    // if (array_key_exists($filterKey, $productData[$countKey])) {
                        // $filterBy = $productData[$countKey][$filterKey];
                        // $reduce++;
                    // }
                // }
            // }

            // array_splice($productData, -$reduce);
             

            $countKey = count($productData);
                    
            $storeId = $this->storeManager->getStore()->getId();

            $storeIds = [];
            $stores   = $this->storeManager->getStores(true);
            foreach ($stores as $key => $value) {
                $storeIds[] = $value->getStoreId();
            }

            if (count($storeIds) == 0) {
                $storeIds[] = $storeId;
            }
        
            
            
            list($output, $updatedSku, $successCount, $failureCount, $failed_skus) = $this->getProductResults(
                $productData,
                $output,
                $updatedSku,
                $successCount,
                $failureCount,
                $failed_skus,
                $filterBy,
                $is_price_update,
                $storeIds
            );

            $messages = [
                'sku'           => $output,
                'updated_sku'   => $updatedSku,
                'total_count'   => $countKey,
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'failed_skus'   => $failed_skus,
            ];
        } catch (\Exception $e) {
            $failure_reason = ['Error' => $e->getMessage()];
            $messages       = ['failure_reason' => $failure_reason];
        }//end try

        $products->createProduct($messages);

        return $products;
    }//end createProducts()

    public function getProductResults(
        $productData,
        $output,
        $updatedSku,
        $successCount,
        $failureCount,
        $failed_skus,
        $filterBy,
        $is_price_update,
        $storeIds
    ) {
       
        foreach ($productData as $key => $data) {
      
            $flag = '';
            $filterValue = '';
            $sku = '';
         
            if (array_key_exists('sku', $data)) {
              
                $sku = $data['sku'];
            }
            $selectedColumns = ['sku'];
          
            if (array_key_exists($filterBy, $data)) {
              
                $filterValue = $data[$filterBy];
              
                if (empty($sku)) {
                    $sku = $filterValue;
                }
                
            }
            if (!empty($filterBy)) {
             
                array_push($selectedColumns, $filterBy);
            }
            


            if (!empty($filterValue)) {
              
                try {
                    
                    list($output, $updatedSku, $successCount) = $this->createProductDetails(
                        $data,
                        $updatedSku,
                        $filterBy,
                        $filterValue,
                        $successCount,
                        $storeIds,
                        $output,
                        $sku,
                        $is_price_update,
                        $selectedColumns,
                        $flag
                    );
                } catch (\Exception $e) {
                    $catalogProduct = false;
                    if ($sku != $flag) {
                        ++$failureCount;
                        $flag          = $sku;
                        $failed_skus[] = [
                            'sku'   => $sku,
                            'Error' => $e->getMessage(),
                        ];
                    }
                }//end try
            } else {
                if ($sku != $flag) {
                    ++$failureCount;
                    $flag          = $sku;
                    $failed_skus[] = [
                        'sku'   => $sku,
                        'Error' => 'value is empty',
                    ];
                }
            }//end if
        }//end foreach

        return [
            $output,
            $updatedSku,
            $successCount,
            $failureCount,
            $failed_skus
        ];
    }

    public function createProductDetails(
        $data,
        $updatedSku,
        $filterBy,
        $filterValue,
        $successCount,
        $storeIds,
        $output,
        $sku,
        $is_price_update,
        $selectedColumns,
        $flag
    ) {
      
        $catalogProduct = $this->catalogProductFactory->create();
        $collection = $this->getProductCollection($filterBy, $filterValue, $selectedColumns);
        //echo $collection->getSize();exit();
        if ($collection->getSize() != 0) {
            foreach ($storeIds as $key => $value) {
                $stockData = [];
                $productId = $this->getProductId($collection->getData()[0]['sku']);

                list($data, $stockData) = $this->constructProductData($data, $is_price_update, $filterBy);

                if ($this->updateProductData($productId, $data, $value, $stockData)) {
                   
                    if (!in_array($sku, $updatedSku)) {
                        $updatedSku[] = $sku;
                    }
                        
                    if ($sku != $flag) {
                        ++$successCount;
                        $flag = $sku;
                    }
                }
            }
        } else {
            if ($this->addProductData($data, $catalogProduct, $storeIds, $filterBy)) {
                if (!in_array($sku, $output)) {
                    $output[] = $sku;
                }

                if ($sku != $flag) {
                    ++$successCount;
                    $flag = $sku;
                }
            }
        }//end if
     
        return [
            $output,
            $updatedSku,
            $successCount
        ];
    }

    public function constructProductData($data, $is_price_update, $filterBy)
    {
        $stockData = [];
        unset($data['store_id']);
        unset($data['store_ids']);
      // unset($data['sku']);
        unset($data['attribute_set_id']);

            // unset($data['status']);

        // if (array_key_exists('status', $data)) {
        //     if ($data['status'] == 2) {
        //         $data['status'] = 2; // disable status
        //     }
        // }

            
        if ($filterBy != 'sku') {
            if (array_key_exists($filterBy, $data)) {
                unset($data[$filterBy]);
            }
        }

        if (array_key_exists('price', $data)) {
            if (round($data['price']) == 0) {
                if ($is_price_update == 2) {
                    unset($data['price']);
                }
            }
        }

        $stockData = $this->getStockData($data);
        
        return [
            $data,
            $stockData
        ];
    }

    private function getStockData($data)
    {

        $stockData = [ 'qty' => 1, 'is_in_stock' => 1]; //default stockdata

        if (array_key_exists('qty', $data)) {
            $qty = $data['qty'];
        }

        if (array_key_exists('extension_attributes', $data)) {
            
            if (array_key_exists('qty', $data['extension_attributes'])) {
                $qty = $data['extension_attributes']['qty'];
            }

            if (array_key_exists('stock_item', $data['extension_attributes'])) {
                if (array_key_exists('qty', $data['extension_attributes']['stock_item'])) {
                    $qty = $data['extension_attributes']['stock_item']['qty'];
                }
            }
        }

        if ($qty > 0) {
            $stockStatus = 1;
        } else {
            $stockStatus = 0;
        }

        $stockData = [ 'qty' => $qty, 'is_in_stock' => $stockStatus];
        return $stockData;
    }
    

    private function getPriceData($data)
    {
        $price = 0;
        if (array_key_exists('price', $data)) {
            if (round($data['price']) != 0) {
                $price = $data['price'];
            } else {
                /*if ($is_price_update = 1) {
                    $price = $data['price'];
                }*/
            }
        }
        return $price;
    }

    private function checkDataExists($key, $data)
    {
        if (array_key_exists($key, $data)) {
            if (!empty($data[$key])) {
                return true;
            }
        }
        return false;
    }

    private function productCustomAttributes($catalogProduct, $data)
    {
     

        /*Custom attributes*/
        if (array_key_exists('po_cost', $data)) {
            if (round($data['po_cost']) != 0) {
                $catalogProduct->setPoCost($data['po_cost']);
            }
        }

        // $attrValue = [];
        // if (array_key_exists('uom', $data)) {
        //     // foreach ($data['uom'] as $key => $uom) {
        //         $attrValue[] = $this->getAttributeOptionId(
        //             $catalogProduct,
        //             "uom",
        //             $data['uom']
        //         );
        //     // }

        //     if (!empty($attrValue)) {
        //         $catalogProduct->setData('uom', $attrValue);
        //     }
        // }
        if (array_key_exists('uom', $data)) {
           // $catalogProduct->setUom($data['uom']);
        }
        
        return $catalogProduct;
    }

    private function getStockDetails($data)
    {
        $stockData = [];
        if (array_key_exists('qty', $data)) {
            if ($data['qty'] > 0) {
                $stockStatus = 1;
            } else {
                $stockStatus = 0;
            }
            
            $stockData = [ 'qty' => $data['qty'], 'is_in_stock' => $stockStatus];
        }
        return $stockData;
    }

    public function getProductId($sku)
    {
        $catalogProduct = $this->catalogProductFactory->create();
        $productId = $catalogProduct->getIdBySku($sku);
        return $productId;
    }

    public function updateProductDetails($sku, $value, $price)
    {
        $catalogProduct = $this->catalogProductFactory->create();
        try {
            $productId = $catalogProduct->getIdBySku($sku);
            $catalogProduct->load($productId);
            $catalogProduct->setStoreId($value);
            $catalogProduct->setPrice($price);
            if ($catalogProduct->save()) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getProductCollection($filterBy, $filterValue, $selectedColumns)
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect($selectedColumns)
        ->addFieldToFilter($filterBy, ['like' => $filterValue])->load();
        return $collection;
    }

    public function updateProductData($productId, $data, $value, $stockData = [])
    {
     
        $catalogProduct = $this->catalogProductFactory->create();
        $catalogProduct->load($productId);
        $catalogProduct->setStoreId($value);
       
        if (!empty($data)) {
            if (array_key_exists('name', $data)) {
               // unset($data['name']);
            }
            if (array_key_exists('status', $data)) {
                $catalogProduct->setStatus($data['status']);
            }

            if (array_key_exists('width', $data)) {
                $catalogProduct->setTsDimensionsWidth($data['width']);
            }

            if (array_key_exists('weight', $data)) {
                $catalogProduct->setWeight($data['weight']);
            }

            if (array_key_exists('length', $data)) {
                $catalogProduct->setTsDimensionsLength($data['length']);
            }

            if (array_key_exists('height', $data)) {
                $catalogProduct->setTsDimensionsHeight($data['height']);
            }

            $price = $this->getPriceData($data);
            $catalogProduct->setPrice($price);

            
            //unset($data['extension_attributes']);
            //$catalogProduct->addData($data);
        }

        $stockData = $this->getStockData($data);
        if (!empty($stockData)) {
            $catalogProduct->setStockData($stockData);
        }
        //print_r($catalogProduct);exit;
    //unset($data['extension_attributes']);
        $catalogProduct = $this->productCustomAttributes($catalogProduct, $data);
       

        if ($this->productRepository->save($catalogProduct)) {
          
    //if(true){
            return true;
        } else {
            return false;
        }
    }

    public function addProductData($data, $catalogProduct, $storeIds, $filterBy)
    {
      
        list($catalogProduct) = $this->getProductsData($data, $catalogProduct, $storeIds, $filterBy);
        
        $catalogProduct = $this->productCustomAttributes($catalogProduct, $data);
      //  $catalogProduct->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);

        if ($catalogProduct->save()) {
            return true;
        } else {
            return false;
        }
    }


    public function getProductsData($data, $catalogProduct, $storeIds, $filterBy)
    {
               
        /*Mandatory Fields*/

        $catalogProduct->setWebsiteIds([1]); //Default websites
        //$catalogProduct->setStoreId(0);
        //$catalogProduct->setStoreIds($storeIds);

        $catalogProduct->setData('sku', $data['sku']);
        if ($filterBy != 'sku') {
            if (array_key_exists($filterBy, $data)) {
                $catalogProduct->setData($filterBy, $data[$filterBy]);
            }
        }

        if (array_key_exists('name', $data)) {
            $catalogProduct->setName($data['name']);
        } else {
            $catalogProduct->setName($data['sku']);
        }

        
        //echo $url;exit();
        $urlKey = preg_replace('#[^0-9a-z]+#i', '-', $data['name']);
        $urlKey = strtolower($urlKey);
        $urlKey = trim($urlKey, '-');
 
        $pathUrl = $urlKey.'.html';
        
        //$catalogProduct->setUrlKey($pathUrl);
        $catalogProduct->setAttributeSetId(4);
        if (array_key_exists('attribute_set_id', $data)) {
            $catalogProduct->setAttributeSetId($data['attribute_set_id']);
        }

        $catalogProduct->setTypeId('simple');
        if (array_key_exists('type_id', $data)) {
            $catalogProduct->setTypeId($data['type_id']);
        }
        
       
        $catalogProduct->setStatus(2);
        if (array_key_exists('status', $data)) {
            $catalogProduct->setStatus($data['status']);
        }

        if (array_key_exists('width', $data)) {
                $catalogProduct->setTsDimensionsWidth($data['width']);
            }

            if (array_key_exists('weight', $data)) {
                $catalogProduct->setWeight($data['weight']);
            }

            if (array_key_exists('length', $data)) {
                $catalogProduct->setTsDimensionsLength($data['length']);
            }

            if (array_key_exists('height', $data)) {
                $catalogProduct->setTsDimensionsHeight($data['height']);
            }

        $catalogProduct->setVisibility(4);
        
        $price = $this->getPriceData($data);
        $catalogProduct->setPrice($price);
        
        $stockData = $this->getStockData($data);

        $catalogProduct->setStockData(
            $stockData
        );

        if (array_key_exists('short_description', $data)) {
            $catalogProduct->setShortDescription($data['short_description']);
        }
        
        return [
            $catalogProduct
        ];
    }
    /**
     * @return \Cloras\Base\Api\ProductResultsInterface
     */
    public function updateProductPrice($productInfo)
    {
        
        $products = $this->productFactory->create();

        $response = [];
        try {
            $productInfo = $this->jsonHelper->unserialize($productInfo);

            if (array_key_exists('sku', $productInfo) || array_key_exists('partnumber', $productInfo)) {
                $selectedColumns = ['sku'];
                $filterColumn    = 'sku';
                $filterValue     = $productInfo['sku'];
                $sku             = $productInfo['sku'];

                if (!empty($productInfo['partnumber'])) {
                    $selectedColumns = [
                        'sku',
                        'partnumber',
                    ];
                    $filterColumn    = 'partnumber';
                    $filterValue     = $productInfo['partnumber'];
                    list($response) = $this->getResponseProducts(
                        $filterColumn,
                        $filterValue,
                        $productInfo,
                        $selectedColumns
                    );
                }
            } else {
                $response[] = [
                    'status'  => 'failure',
                    'message' => 'SKU Key is not present',
                ];
            }//end if
        } catch (\Exception $e) {
            $response[] = [
                'status'  => 'failure',
                'message' => $e->getMessage(),
            ];
        }//end try

        $products->setResponseMessage($response);

        return $products;
    }

    public function getResponseProducts($filterColumn, $filterValue, $productInfo, $selectedColumns)
    {

        $collection = $this->getProductCollection($filterColumn, $filterValue, $selectedColumns);

        $this->store->setCurrentStore('admin');

        $sku             = $productInfo['sku'];
        $collectionCount = count($collection);
        if ($collectionCount > 0) {
            $sku = $collection->getData()[0]['sku'];
            if ($sku) {
                $price = (
                    (round($productInfo['UnitPrice']) != 0) ? $productInfo['UnitPrice'] : $productInfo['BaseUnitPrice']
                );
                if (round($price) != 0) {
                    if ($this->updateProductDetails($sku, 1, $price)) {
                        $response[] = [
                        'status'  => 'success',
                        'message' => $sku . ' updated',
                        ];
                    } else {
                        $response[] = [
                        'status'  => 'failure',
                        'message' => $sku . ' is not available',
                        ];
                    }
                } else {
                    $response[] = [
                    'status'  => 'success',
                    'message' => $sku . ' Price is 0 ',
                    ];
                }
            }
        }

        return [
            $response
        ];
    }

    /**
     * @return \Cloras\Base\Api\Data\ResultsInterface
     */
    public function updateBulkInventory($data)
    {
      
        /* product Inventory Update*/
        $response = ['total_count' => 0, 'success_count' => 0, 'failure_count' => 0, 'failed_skus' => []];

        $productInfo = $this->jsonHelper->unserialize($data);
       
           
        $locationCode = '';
        $storeId = $this->storeManager->getStore()->getId();
        
     
        foreach ($productInfo as $sku => $info) {

            $proSku = $productInfo[$sku]['sku'];
            $proQty = $productInfo[$sku]['qty'];
         
            if ($proSku) {
              
                try {
                   
                  //  $this->saveStockData($sku, $info);
                    $this->saveStockData($proSku, $proQty, $storeId);
                    $response['success_count'] += 1;
                } catch (\Exception $e) {
                    $response['failed_skus'][$proSku] = $e->getMessage();
                    $response['failure_count'] += 1;
                }
                $response['total_count'] += 1;
            }
        }

        /**
         * @var \Cloras\Base\Api\Data\ResultsInterface
         */
        $results = $this->resultsFactory->create();

        $results->setResponse($response);

        return $results;
    }

    private function saveStockData($sku, $qty, $storeId,$price='')
    {
       
        $product = $this->productRepository->get($sku);
        $totalQty = $qty;
       
        // foreach ($info as $location => $qty) {
        //     $totalQty += (int) $qty;
        // }
        $product->setStoreId($storeId);
        $product->setStockData(['qty' => $totalQty, 'is_in_stock' => $totalQty > 0]);
        if($price !=''){
            $product->setPrice($price);
      }    
        $product->unsetData('media_gallery');
        $this->productRepository->save($product);
    }

    /**
     * @return \Cloras\Base\Api\Data\ProductResultsInterface
     */
    public function getNewProducts()
    {
        $products    = $this->productFactory->create();
        $productData = [];
        $total_count = 0;
        try {
            //$productFilters = $this->searchCriteriaBuilder->addFilter('status', '1', 'eq')->create();
            //$productItems   = $this->productRepository->getList($productFilters)->getItems();
            $productData    = [];
            $partnumber     = '';
            $uom            = '';
            //echo $total_count = count($productItems);exit();

            $productCollection = $this->productCollectionFactory->create();
        /** Apply filters here */
            $productCollection->addAttributeToSelect(['entity_id','sku','description','name','price','qty']);
            $start = date('Y-m-d' . ' 00:00:00', $this->dateTime->timestamp());
            $end = date('Y-m-d' . ' 23:59:59', $this->dateTime->timestamp());
            $productCollection->addAttributeToFilter('created_at', ['from' => $start, 'to' => $end]);
    
            if ($productCollection->getSize() != 0) {
                foreach ($productCollection as $product) {
                    $productData[] = [
                        'sku'        => $product->getSku(),
                    'description' => $product->getDescription(),
                    'name' => $product->getName(),
                    'price' => $product->getPrice(),
                    'qty' => $product->getQty()
                        
                    ];
                }
                $total_count++;
            }
        } catch (\Exception $e) {
            $productData[] = [
                'status'  => 'failure',
                'message' => $e->getMessage(),
            ];
        }//end try
        $products->addProduct($productData);
        $products->setTotalProducts($total_count);
        return $products;
    }

    /* Get Option id by Option Label */
    public function getAttributeOptionId($catalogProduct, $attributeName, $attributeValue)
    {
               
        $isAttributeExist = $catalogProduct->getResource()->getAttribute($attributeName);
        $optionId = "";
        if (!empty($attributeValue)) {
            if ($isAttributeExist && $isAttributeExist->usesSource()) {
                $optionValue = str_replace("'", "", $attributeValue);
       
                if ($isAttributeExist->getSource()->getOptionId(trim($optionValue))) {
                          $optionId = $isAttributeExist->getSource()->getOptionId(trim($optionValue));
                }
            }
        }
        
        return $optionId;
    }//end class
}//end class
