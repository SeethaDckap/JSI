<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class SaveOrderComment extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->registry->registry('customerCommentAdded')) {
            $this->registry->register('customerCommentAdded', true);
            $orderComment = $this->request->getPost('ordercomment');
            if (is_array($orderComment) && isset($orderComment['comment']))
                $comment = trim($orderComment['comment']);
            else
                $comment = '';

            $order = $observer->getEvent()->getOrder();
            /* @var $order Mage_Sales_Model_Order */
            $order_comment = $order->addStatusHistoryComment($comment);
            $order_comment->save();

//                $order->setCustomerComment($comment);
//                $order->setCustomerNoteNotify(true);
            $order->setCustomerNote($comment);
        }
    }

}