<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Helper\Frontend;


/**
 * Helper for List Contracts on the frontend
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Pricelist extends \Epicor\Lists\Helper\Frontend
{

    protected $priceLists;
    protected $productsPrice = array();

    /**
     * @var \Epicor\Comm\Model\Location\Product\CurrencyFactory
     */
    protected $commLocationProductCurrencyFactory;

    /**
     * @var \Epicor\Common\Model\XmlvarienFactory
     */
    protected $commonXmlvarienFactory;

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\Product\CollectionFactory
     */
    protected $listsResourceListModelProductCollectionFactory;

    public function __construct(
        // FOR PARENT
        \Epicor\Lists\Helper\Context $context,
        \Epicor\Lists\Model\Contract\AddressFactory $listsContractAddressFactory,
        \Epicor\Lists\Model\ListFilterReader $filterReader,
        // FOR THIS CLASS
        \Epicor\Comm\Model\Location\Product\CurrencyFactory $commLocationProductCurrencyFactory,
        \Epicor\Lists\Model\ResourceModel\ListModel\Product\CollectionFactory $listsResourceListModelProductCollectionFactory,
        \Epicor\Common\Model\XmlvarienFactory $commonXmlvarienFactory
    ) {
        $this->commonXmlvarienFactory = $commonXmlvarienFactory;
        $this->commLocationProductCurrencyFactory = $commLocationProductCurrencyFactory;
        $this->listsResourceListModelProductCollectionFactory = $listsResourceListModelProductCollectionFactory;
        
        $listsSessionHelper = $context->getListsSessionHelper();        
        $customerAddressFactory = $context->getCustomerAddressFactory(); 
        parent::__construct(
            $context, 
            $listsContractAddressFactory,
            $filterReader
        );
    }
    /**
     * Returns Mandatory Price Lists
     * 
     * @return array
     */
    public function getPriceLists()
    {
        if (is_null($this->priceLists)) {
            $this->getActiveLists();
            $listIds = $this->getTypeIds('Pr');
            $this->priceLists = array_intersect_key($this->lists, $listIds);
        }

        $priceLists = array_intersect_key($this->lists, $this->priceLists);

        return $priceLists;
    }

    /**
     * Returns whether price lists will be used or not
     * 
     * @return bool
     */
    public function usePriceLists()
    {
        if ($this->listsDisabled()) {
            return false;
        }

        $lists = $this->getPriceLists();

        return count($lists) > 0;
    }

    /**
     * Returns product prices for skus
     * 
     * @param  array $skus
     * @return array $prices
     */
    public function getProductsPrice($skus)
    {
        if (!is_array($skus)) {
            $skus = array($skus);
        }

        $newSkus = array_diff($skus, array_keys($this->productsPrice));

        if (is_array($newSkus) && count($newSkus) > 0) {
            $newPrices = $this->_getPriceBySkus($newSkus);
            $this->productsPrice = $this->productsPrice + $newPrices;
        }
        return array_intersect_key($this->productsPrice, array_flip($skus));
    }

    /**
     * Returns product prices for skus - no cache
     * 
     * @param  array $skus
     * @return array $prices
     */
    protected function _getPriceBySkus($skus)
    {
        if (!is_array($skus)) {
            $skus = array($skus);
        }

        $products = array();
        if ($this->usePriceLists()) {
            $collection = $this->getProductsPriceCollection($skus);
            /* @var $collection Epicor_Lists_Model_Resource_List_Product_Collection */
            foreach ($collection->getItems() as $item) {
                $list = $this->getValidListById($item->getListId());
                if (!$list instanceof \Epicor\Lists\Model\ListModel) {
                    continue;
                }

                $sku = $item->getSku();
                if (!isset($products[$sku]) || $this->comparePriceProductAgainstList($products[$sku], $list)) {
                    $product = array(
                        'price' => $item->getPrice(),
                        'price_breaks' => unserialize($item->getPriceBreaks()),
                        'value_breaks' => unserialize($item->getValueBreaks()),
                        'list_id' => $list->getId(),
                        'priority' => $list->getPriority(),
                        'start_date' => $list->getStartDate(),
                        'created_date' => $list->getCreatedDate(),
                    );
                    $products[$sku] = $product;
                }
            }
        }

        return $products;
    }

    /**
     * Returns the products price collection for said skus
     * 
     * @param  array $skus
     * @return \Epicor\Lists\Model\ResourceModel\List\Product\Collection $collection
     */
    protected function getProductsPriceCollection($skus)
    {
        $lists = $this->getPriceLists();
        $listIds = array_keys($lists);
        $currencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();

        $collection = $this->listsResourceListModelProductCollectionFactory->create();
        /* @var $collection Epicor_Lists_Model_Resource_List_Product_Collection */
        $collection->getSelect()->join(
            array('product_price' => $collection->getTable('ecc_list_product_price')), 'main_table.id = product_price.list_product_id');
        $collection->addFieldToFilter('main_table.list_id', array('in' => $listIds));
        $collection->addFieldToFilter('main_table.sku', array('in' => $skus));
        $collection->addFieldToFilter('product_price.currency', $currencyCode);

        return $collection;
    }

    /**
     * Compares products price from current list with past assigned price list
     * 
     * @param  array $product
     * @param  \Epicor\Lists\Model\ListModel $list
     * @return bool
     */
    protected function comparePriceProductAgainstList($product, \Epicor\Lists\Model\ListModel $list)
    {
        if ($product['priority'] < $list->getPriority()) {
            return true;
        } elseif ($product['priority'] > $list->getPriority()) {
            return false;
        }

        if ($product['start_date'] > $list->getStartDate()) {
            return true;
        } elseif ($product['start_date'] < $list->getStartDate()) {
            return false;
        }

        if ($product['created_date'] > $list->getCreatedDate()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Reprices products with priceInfo
     * 
     * @param  \Epicor\Comm\Model\Product $product
     * @param  array $priceInfo
     * @param  bool  $allowPricingRules
     * @param  int   $roundingDecimals
     * @return /Epicor_Lists_Helper_Frontend_Pricelist
     */
    public function repriceProduct($product, $priceInfo, $allowPricingRules, $roundingDecimals)
    {
        $productHelper = $this->commProductHelper;
        /* @var $productHelper Epicor_Comm_Helper_Product */

        $customerPrice = $priceInfo['price'];
        $basePrice = $product->getPrice();

        $breaks = array();
        if(is_array($priceInfo['price_breaks'])){
        foreach ($priceInfo['price_breaks'] as $priceBreak) {
            $breaks[] = $this->dataObjectFactory->create([ 
                'data' => [
                    'price' => $priceBreak['price'],
                    'quantity' => $priceBreak['qty']
                ]
            ]);
        }
        }
        
        $breaksObj = $this->commonXmlvarienFactory->create();
        $breaksObj->setBreak($breaks);
        $product->setBreaks($breaksObj);
        $product->setPriceListApplied(true);
        $productHelper->setProductPrices($product, $basePrice, $customerPrice, $breaksObj, $roundingDecimals, $allowPricingRules);


        $this->repriceLocations($product);

        return $this;
    }

    /**
     * Reprices product location with product current price
     * 
     * @param  \Epicor\Comm\Model\Product $product
     * @return /Epicor_Lists_Helper_Frontend_Pricelist
     */
    public function repriceLocations($product)
    {
        if (!$this->scopeConfig->isSetFlag('epicor_comm_locations/global/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return $this;
        }

        $locations = $product->getLocations();
        $currencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();

        foreach ($locations as $location) {
            /* @var $location Epicor_Comm_Model_Location_Product */
            if (!($currency = $location->getCurrency($currencyCode))) {
                $currency = $this->commLocationProductCurrencyFactory->create();
            }
            /* @var $currency Epicor_Comm_Model_Location_Product_Currency */
            $currency->setPrice($product->getPrice());
            $currency->setBasePrice($product->getBasePrice());
            $currency->setCustomerPrice($product->getCustomerPrice());
            $currency->setDiscount($product->getDiscount());
            $currency->setTierPrice($product->getTierPrice());
            $currency->setMinPrice($product->getMinPrice());
            $currency->setMininmalPrice($product->getMinimalPrice());
            $currency->setBreaks($product->getBreaks());
            $location->setCurrencyObject($currencyCode, $currency);
        }

        return $this;
    }

}
