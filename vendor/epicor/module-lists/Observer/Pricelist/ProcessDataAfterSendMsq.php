<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Observer\Pricelist;

class ProcessDataAfterSendMsq extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Process products after sending MSQ
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor_Lists_Model_Observer_Frontend_Pricelist
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->listsFrontendPricelistHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Pricelist */

        if (!$helper->usePriceLists()) {
            return $this;
        }

        $event = $observer->getEvent();
        /* @var $event Varien_Event */
        $dataObject = $event->getDataObject();
        /* @var $dataObject Varien_Object */

        $message = $event->getMessage();
        /* @var $message Epicor_Comm_Model_Message_Request_Msq */

        $products = $dataObject->getRepriceableProducts();
        
        if (empty($products)) {
            return $this; 
        }
        
        $prices = $dataObject->getPrices();
        $precision = $helper->getProductPricePrecision();
        $roundingDecimals = $message->getPreventRounding() ? '4' : $precision;

        foreach ($products as $product) {
            $sku = $this->getProductSku($product);
            if (isset($prices[$sku]) && !$this->shouldUseContractPrice($product)) {
                $helper->repriceProduct($product, $prices[$sku], $message->getAllowPriceRules(), $roundingDecimals);
            }
        }

        return $this;
    }

}