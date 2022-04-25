<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Observer\Lists;

class AfterSendMsqNoDefaultUomProducts extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Process products after sending MSQ
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor_Lists_Model_Observer_Frontend_Pricelist
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $message = $observer->getEvent()->getMessage();
        /* @var $message Epicor_Comm_Model_Message_Request_Msq */

        if (!$message->getHasListGroupedProducts()) {
            return $this;
        }

        $childProducts = (array) $message->getListGroupedChildProducts();
        $parentProducts = $message->getListGroupedParentProducts();
        
        foreach ($parentProducts as $parentProduct) {
            $childrenIds = $parentProduct->getListsChildrenIds();
            if (!is_array($childrenIds)) {
                continue;
            }

            $productData = false;
            foreach ($childrenIds as $childId) {
                if (!isset($childProducts[$childId])) {
                    continue;
                }
                $childProduct = $childProducts[$childId];

                if (!$productData || $childProduct->getPrice() < $productData->getPrice()) {
                    $productData = $childProduct;
                }
            }

            if ($productData && !$parentProduct->getEccPricingSku()) {
                $parentProduct->setPrice($productData->getPrice());
                $parentProduct->setSpecialPrice($productData->getSpecialPrice());
                $parentProduct->setEccMsqBasePrice($productData->getEccMsqBasePrice());
                $parentProduct->setFinalPrice($productData->getFinalPrice());
                $parentProduct->setMinimalPrice($productData->getMinimalPrice());
                $parentProduct->setMinPrice($productData->getMinPrice());
                $parentProduct->setCustomerPrice($productData->getCustomerPrice());
                $parentProduct->setTierPrice($productData->getTierPrice());
                
                if ($this->scopeConfig->isSetFlag('epicor_comm_locations/global/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                    foreach($productData->getLocations() as $loc){
                        $parentProduct->setLocation($loc['location_code'], $loc);
                    } 
                }
                $parentProduct->cleanCache();
            }
        }
    }

}