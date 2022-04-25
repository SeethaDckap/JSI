<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Helper\Pricing\Rule;


use Epicor\Common\Helper\Context;

class Product extends \Epicor\Common\Helper\Data
{

    private $_products;

    /**
     * @var \Epicor\SalesRep\Model\ResourceModel\Pricing\Rule\CollectionFactory
     */
    protected $salesRepResourcePricingRuleCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Epicor\Customerconnect\Helper\Rfq
     */
    protected $customerconnectRfqHelper;

    public function __construct(
        \Epicor\Common\Helper\Context $context,
        \Epicor\SalesRep\Model\ResourceModel\Pricing\Rule\CollectionFactory $salesRepResourcePricingRuleCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Epicor\Customerconnect\Helper\Rfq $customerconnectRfqHelper
    )
    {
        $this->salesRepResourcePricingRuleCollectionFactory = $salesRepResourcePricingRuleCollectionFactory;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->customerconnectRfqHelper = $customerconnectRfqHelper;
        parent::__construct($context);
    }
    
    public function _construct()
    {
        parent::_construct();
    }

    public function hasActiveRules()
    {
        // get salesrep account rules
        $customerSession = $this->customerSessionFactory->create();
        /* @var $customerSession Mage_Customer_Model_Session */

        $customer = $customerSession->getCustomer();
        /* @var $customer Epicor_Common_Model_Customer */

        $rules = $this->salesRepResourcePricingRuleCollectionFactory->create();
        /* @var $rules Epicor_SalesRep_Model_Resource_Pricing_Rule_Collection */
        $rules->addFieldToFilter('sales_rep_account_id', $customer->getEccSalesRepAccountId());
        $rules->filterCurrentlyActiveOnly();

        return $rules->count() > 0 ? true : false;
    }

    /**
     *
     * @param \Epicor\Comm\Model\Product $product
     */
    public function getMaxDiscount($product, $price = null, $qty = 1)
    {
        $discount = $this->_calculateProductMaxDiscount($product, $price, $qty);

        return $discount['max_discount'];
    }

    /**
     *
     * @param \Epicor\Comm\Model\Product $product
     */
    public function getMinPrice($product, $price = null, $qty = 1)
    {
        $discount = $this->_calculateProductMaxDiscount($product, $price, $qty);

        return $discount['min_price'];
    }

    /**
     *
     * @param \Epicor\Comm\Model\Product $product
     */
    public function getRuleBasePrice($product, $price = null, $qty = 1)
    {
        $discount = $this->_calculateProductMaxDiscount($product, $price, $qty);

        return $discount['rule_price'];
    }

    /**
     *
     * @param \Epicor\Comm\Model\Product $product
     */
    private function _calculateProductMaxDiscount($product, $price = null, $qty = 1)
    {
        $data = false;
        if ($product) {

            $pKey = $product->getId();

            if ($product->getMsqAttributes()) {
                $attributeString = '';
                foreach ($product->getMsqAttributes() as $key => $value) {
                    $attributeString .= $key . $value;
                }
                $pKey .= md5($attributeString);
            }

            if ($ewaSku = $product->getEwaSku()) {
                $pKey .= md5('ewa_sku' . $ewaSku);
            }

            $cacheData = $this->_getCachedProductData($pKey);

            if (!empty($cacheData)) {
                return $cacheData;
            }

            // get rules that apply to this product
            $rules = $this->_getRulesForProduct($product);
            // loop through rules and work out minimum max discount and maximum min price
            $costPrice = $product->getCost();
            $listPrice = !is_null($price) ? $price : $this->getBasePrice($product, $qty);
            $basePrice = !is_null($product->getEccMsqBasePrice()) ? $product->getEccMsqBasePrice() : $listPrice;
            $minPrice = $listPrice;
            $maxDiscount = 0;
            $rulePrice = 0;
            $precision = $this->customerconnectRfqHelper->getProductPricePrecision();

            if (!empty($rules)) {
                foreach ($rules as $rule) {
                    /* @var $rule Epicor_SalesRep_Model_Pricing_Rule */
                    if ($rule->getIsValid()) {
// list price based amount, means that the max discount is as specified
                        $maxDiscount = $rule->getActionAmount();
                        if ($rule->getActionOperator() == 'cost') {

                            // if no cost price move to next rule
                            if (!$costPrice || $costPrice == 0) {
                                continue;
                            }
// cost price based amount, means that the max discount & min price is calculated :
// minPrice = cost price * (1 + (markup / 100))
// maxDiscount = (1 - (min price / list price
                            $minPrice = $costPrice * (1 + ($maxDiscount / 100));
                            $maxDiscount = round((1 - ($minPrice / $listPrice)) * 100, $precision);
                            $rulePrice = $listPrice;
                        } elseif ($rule->getActionOperator() == 'list') {
                            // check if we have a max discount, if so work out the minimum price for the product
                            //if no list price move to next rule
                            if (!$listPrice || $listPrice <= 0) {
                                continue;
                            }

                            $minPrice = round($listPrice * (1 - ($maxDiscount / 100)), $precision);
                            $rulePrice = $listPrice;
                        } elseif ($rule->getActionOperator() == 'base') {
                            // check if we have a max discount, if so work out the minimum price for the product
                            //if no list price move to next rule
                            if (!$basePrice || $basePrice <= 0) {
                                continue;
                            }

                            $minPrice = round($basePrice * (1 - ($maxDiscount / 100)), $precision);
                            // check the list price to make sure that if set to max discount then it doesnt go below min price
                            $rulePrice = $basePrice;
                        }
                        break;
                    }
                }

                // check if we have a max discount, if so work out the minimum price for the product
            }

            $data = array(
                'max_discount' => $maxDiscount,
                'min_price' => $minPrice,
                'rule_price' => $rulePrice
            );

            $this->_cacheProductData($pKey, $data);
        }
        return $data;
    }

    private function _getCachedProductData($pKey)
    {
        if (isset($this->_products[$pKey])) {
            return $this->_products[$pKey];
        }
    }

    private function _cacheProductData($pKey, $data)
    {
        $this->_products[$pKey] = $data;
    }

    /**
     * get all active and in date rules for this sales rep (that apply to this product)
     * get all rule ids for this product from the index.
     * @param \Epicor\Comm\Model\Product $product
     */
    private function _getRulesForProduct($product)
    {
        // get salesrep account rules
        $customerSession = $this->customerSessionFactory->create();
        /* @var $customerSession Mage_Customer_Model_Session */

        $customer = $customerSession->getCustomer();
        /* @var $customer Epicor_Common_Model_Customer */

        $productId = $product->getId();
        $ewaSku = $product->getEwaSku();
        if (!$ewaSku && $product->getMsqAttributes()) {
            foreach ($product->getMsqAttributes() as $key => $value) {
                if (strtolower($key) == 'ewa sku') {
                    $ewaSku = $value;
                }
            }
        }

        if ($ewaSku && $ewaSku != $product->getSku()) {
            $ewaProductId = $this->catalogProductFactory->create()->getIdBySku($ewaSku);
            if ($ewaProductId) {
                $productId = $ewaProductId;
            }
        }

        $rules = $this->salesRepResourcePricingRuleCollectionFactory->create();
        /* @var $rules Epicor_SalesRep_Model_Resource_Pricing_Rule_Collection */
        $rules->addFieldToFilter('sales_rep_account_id', $customer->getEccSalesRepAccountId());
        $rules->filterCurrentlyActiveOnly();
        $rules->filterByApplicableProduct($productId);
        $rules->getSelect()->order('priority DESC');

        return $rules->getItems();
    }

    /**
     *
     * @param \Epicor\Comm\Model\Product $product
     */
    public function getDiscountAmount($price, $basePrice)
    {
        if($basePrice ==0) {
                return @round((1) * 100, 2);
        }else{
                return @round((1 - ($price / $basePrice)) * 100, 2);
        }
        
    }

    /**
     *
     * @param \Epicor\Comm\Model\Product $product
     */
    public function getBasePrice($product, $qty)
    {
        $basePrice = $product->getPrice();
        $tierPrice = $product->getTierPrice($qty);

        if (is_array($tierPrice)) {
            $tierPrice = isset($tierPrice[0]['website_price']) ? $tierPrice[0]['website_price'] : null;
        }

        if (!is_null($tierPrice)) {
            $basePrice = min($basePrice, $tierPrice);
        }

        $special = $product->getSpecialPrice();
        if (!is_null($special)) {
            $basePrice = min($basePrice, $special);
        }

        return $basePrice;
    }

    /**
     * Validates Discounted Prices
     *
     * @param array $lines
     * @return array $failedProducts
     */
    public function validateLinesForDiscountedPrices($lines)
    {
        $helper = $this->commMessagingHelper;
        /* @var $helper Epicor_Comm_Helper_Messaging */
        $rfqHelper = $this->customerconnectRfqHelper;
        /* @var $rfqHelper Epicor_Customerconnect_Helper_Rfq */

        $sections = array('existing', 'new');

        $products = array();
        $failedProducts = array();
        foreach ($sections as $section) {
            if (isset($lines[$section]) && is_array($lines[$section])) {
                foreach ($lines[$section] as $key => $newData) {
                    $oldData = isset($newData['old_data']) ? unserialize(base64_decode($newData['old_data'])) : array();
                    $newPrice = $newData['price'];
                    $oldPrice = isset($oldData['price']) ? $oldData['price'] : 0;

                    if ($newPrice != $oldPrice && (!isset($newData['delete']) || !in_array($newData['delete'], array(1, 'on')))) {
                        $product = $helper->findProductBySku($newData['product_code'], $newData['unit_of_measure_code'], false);
                        if ($product instanceof \Epicor\Comm\Model\Product) {
                            $product->setLineNewPrice($newPrice);
                            if(isset($newData['discount'])){
                                $product->setLineDiscount($newData['discount']);
                            }
                            $product->setLineQty($newData['quantity']);
                            $product->setQty($newData['quantity']);
                            $msqAtts = array();

                            if (isset($newData['attributes']) && !empty($newData['attributes'])) {
                                $attributes = unserialize(base64_decode($newData['attributes']));

                                foreach ($attributes as $att) {
                                    if (in_array($att['description'], array('Ewa Code', 'Ewa SKU'))) {
                                        $msqAtts[$att['description']] = $att['value'];
                                    }
                                }
                            }

                            if (!empty($newData['group_sequence'])) {
                                $msqAtts['groupSequence'] = $newData['group_sequence'];
                            }

                            if (!empty($msqAtts)) {
                                $product->setMsqAttributes($msqAtts);
                            }
                            $products[] = $product;
                        } else if ($oldPrice > 0) {
                            $failedProducts[] = $newData['product_code'];
                        }
                    }
                }
            }
        }

        if (count($products) > 0) {
            $rfqHelper->sendMsqForRfqProducts($products);

            foreach ($products as $product) {
                if (
                    $product->getLineNewPrice() != $this->getBasePrice($product, $product->getLineQty()) &&
                    ($product->getLineNewPrice() < $this->getMinPrice($product, null, $product->getLineQty()) ||
                        $product->getLineDiscount() > $this->getMaxDiscount($product, null, $product->getLineQty()))
                ) {
                    $failedProducts[] = $product->getSku();
                }
            }
        }

        return $failedProducts;
    }

}
