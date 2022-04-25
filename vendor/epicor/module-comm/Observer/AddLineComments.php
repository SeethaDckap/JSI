<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class AddLineComments extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Add Line comments to items on cart update
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $cart = $observer->getEvent()->getCart();
        $info = $observer->getEvent()->getInfo()->getData();

        foreach ($info as $itemId => $itemInfo) {
            $item = $cart->getQuote()->getItemById($itemId);
            if (isset($itemInfo['ecc_line_comment'])) {
                $item->setEccLineComment($itemInfo['ecc_line_comment']);
            }
        }
    }

}