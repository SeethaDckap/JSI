<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Observer\Lists;

class PrepareProductsForMsq extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Filters products by lists
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor_Comm_Model_Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {   
        $helper = $this->listsFrontendProductHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Product */
        $contractHelper = $this->listsFrontendContractHelper;
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */

        if ($helper->listsDisabled() || !($helper->hasFilterableLists() || $contractHelper->mustFilterByContract())) {
            return $this;
        }

        $message = $observer->getEvent()->getMessage();
        /* @var $message Epicor_Comm_Model_Message_Request_Msq */
        $dataObject = $observer->getEvent()->getDataObject();
        $activeProducts = $helper->getActiveListsProductIds(true);
        $productIds = array();
        $parentProducts = array();
        foreach ($dataObject->getProducts() as $product) {
            if ($product->getTypeId() != 'grouped') {
                continue;
            }
            
            $childrenIds = $product->getTypeInstance(true)->getChildrenIds($product->getId());
            $childrenIds = $childrenIds[\Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED];
            foreach ($childrenIds as $key => $childId) {
                if (in_array($childId, $activeProducts)) {
                    $productIds[] = $childId;
                } else {
                    unset($childrenIds[$key]);
                }
            }
            $product->setListsChildrenIds($childrenIds);
            $parentProducts[$product->getId()] = $product;
        }
        if (!empty($productIds)) {
            $message->setHasListGroupedProducts(true);
            $collection = $this->catalogResourceModelProductCollectionFactory->create();
            $collection->getSelect()->where(
                '(e.entity_id IN(' . join(',', $productIds) . '))'
            );
            $items = $collection->getItems();
            $message->setListGroupedParentProducts($parentProducts);
        }
    }

}