<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class PurgeAlternative extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $items = $observer->getEvent()->getItems();

        foreach ($items as $item) {
            $product = $this->catalogProductFactory->create()->load($item->getEntityId());
            /* @var $product Epicor_Comm_Model_Product */

            $child = $this->catalogProductFactory->create()->load($item->getChildId());
            /* @var $child Epicor_Comm_Model_Product */

            if (!$product->isObjectNew()) {

                $params = array(
                    'entity' => $product,
                    'child' => $child,
                    'register' => $item
                );

                $this->eventManager->dispatch('epicor_comm_entity_purge_' . strtolower($type) . '_before', $params);

                $getMethod = 'get' . $type . 'LinkCollection';
                $setMethod = 'set' . $type . 'LinkData';

                $linkData = array();

                foreach ($product->$getMethod() as $link) {
                    if ($link->getLinkedProductId() != $item->getChildId()) {
                        $linkData[$link->getLinkedProductId()]['position'] = $link->getPosition();
                    }
                }

                $product->$setMethod($linkData);
                $product->setStoreId(0)->save();

                $this->eventManager->dispatch('epicor_comm_entity_purge_' . strtolower($type) . '_before', $params);
            }
        }
    }

}