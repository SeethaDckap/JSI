<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Observer\Pricelist;

class PrepareDataBeforeSendMsq extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Prepares products before sending MSQ
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor_Lists_Model_Observer_Frontend_Pricelist
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->listsFrontendPricelistHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Pricelist */

        $event = $observer->getEvent();
        /* @var $event Varien_Event */
        $dataObject = $event->getDataObject();
        /* @var $dataObject Varien_Object */

        $message = $event->getMessage();
        /* @var $message Epicor_Comm_Model_Message_Request_Msq */

        if (!$helper->usePriceLists() || $message->getSaveProductDetails()) {
            return $this;
        }

        $useCustomerPrice = $message->getConfig('cusomterpriceused');

        $productsObj = $dataObject->getProducts();
        $products = $productsObj;

        if ($products instanceof \Magento\Catalog\Model\ResourceModel\Product\Collection) {
            $products = $products->getItems();
        } elseif ($products instanceof \Magento\Catalog\Model\Product) {
            $products = array($products->getId() => $products);
        }

        $skus = array();
        foreach ($products as $key => $product) {
            if (($product instanceof \Magento\Catalog\Model\Product) == false) {
                continue; 
            }
            //don't try to add to skus if product doesn't exist
            /* @var $product \Epicor\Comm\Model\Product */
            $productSku = $this->getProductSku($product);
            if ($productSku) {
                $skus[$key] = $productSku;
            }
        }

        if (empty($skus)) {
           return $this; 
        }
        
        $prices = $helper->getProductsPrice($skus);
        $repriceableProducts = $this->getRepriceableProducts($products, $prices);

        $updatePricesStock = $dataObject->getMessage()->getConfig('update_prices_stock');
        if ($updatePricesStock == 'priceonly' && $useCustomerPrice && !$message->getForceMsqPrices()) {
            $products = array_intersect_key($products, $repriceableProducts);
            if (count($products) > 0) {
                array_map(
                    function ($key) use ($productsObj) {
                        return $productsObj->removeItemByKey($key);
                    },
                    array_keys($products)
                );
            }
        }
        $dataObject->setProducts($productsObj);
        $dataObject->setRepriceableProducts($repriceableProducts);
        $dataObject->setPrices($prices);

        return $this;
    }

}