<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class SaveOrderLineComment extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        if (!$this->registry->registry('lineCommentAdded')) {
            $this->registry->register('lineCommentAdded', true);
            $order = $observer->getEvent()->getOrder();
            /* @var $order Mage_Sales_Model_Order */
            $comments = $this->request->getPost('cart');
            if (!empty($comments)) {
                foreach ($comments as $itemId => $itemInfo) {
                    $item = $order->getItemByQuoteItemId($itemId);
                    if (isset($itemInfo['ecc_line_comment'])) {
                        $item->setEccLineComment($itemInfo['ecc_line_comment']);
                    }
                    if ($item->getId()) {
                        $item->save();
                    }
                }
            }
        }
    }

}