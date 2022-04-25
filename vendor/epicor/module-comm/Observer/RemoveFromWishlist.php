<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class RemoveFromWishlist extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // this will only be executed if a product is added to the cart from the wishlist and locations is on or product is group/configurator
        // the details page will be displayed and the wishlist id will be passed.  
        $wishlistId = $observer->getEvent()->getRequest()->getParam('item');
        if ($wishlistId) {
            $wishlistObj = $this->wishlistItemFactory->create()->load($wishlistId);
            if ($wishlistObj->getId()) {
                $wishlistObj->delete();
            }
        }
    }

}