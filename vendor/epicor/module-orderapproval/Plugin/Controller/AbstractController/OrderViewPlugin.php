<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Plugin\Controller\AbstractController;

class OrderViewPlugin
{
    /**
     * @param \Magento\Sales\Controller\AbstractController\View $subject
     * @param $result
     * @return mixed
     */
    public function afterExecute(\Magento\Sales\Controller\AbstractController\View $subject, $result)
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        if (!$result instanceof \Magento\Framework\View\Result\Page) {
            return $result;
        }
        $resultPage = $result;
        if ($this->isViewingOrderApproval($subject)) {
            $this->switchHighlightedLink($resultPage);
        }

        return $result;
    }

    /**
     * @param $subject
     * @return bool
     */
    private function isViewingOrderApproval($subject)
    {
        return $subject->getRequest()->getParam('view_order_approval') === '1';
    }

    /**
     * @param $resultPage
     */
    private function switchHighlightedLink($resultPage)
    {
        /** @var \Magento\Framework\View\Element\Html\Links $navigationBlock */
        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
        $path = 'sales/order/history';
        $orderHistoryLink = '';
        if ($navigationBlock) {
            foreach ($navigationBlock->getLinks() as $link) {
                if ($link->getPath() == $path) {
                    $orderHistoryLink = $link;
                }
            }
            $orderHistoryLink->setIsHighlighted(false);
            $navigationBlock->setActive('epicor_orderapproval/manage/approvals');
        }
    }

}