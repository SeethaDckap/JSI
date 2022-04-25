<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Orders;

class Details extends \Epicor\Customerconnect\Controller\Orders
{


    const FRONTEND_RESOURCE = 'Epicor_Customerconnect::customerconnect_account_orders_details';

    public function execute()
    {
        if ($this->_getOrderDetails()) {
            $resultPage = $this->resultPageFactory->create();
            $pageMainTitle = $resultPage->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle && $pageMainTitle instanceof \Magento\Theme\Block\Html\Title) {
                $order = $this->registry->registry('customer_connect_order_details');
                $pageMainTitle->setPageTitle(__('Order Number : %1', $order->getOrderNumber()));
            }
            return $resultPage;
        } else {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getLayout()->unsetElement('content');
            $resultPage->getLayout()->getBlock('page.main.title')->setTemplate('Epicor_AccessRight::access_denied.phtml');
            return $resultPage;
        }

        if ($this->messageManager->getMessages()->getItems()) {
            /** @var \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory * */

            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setUrl($this->_url->getUrl('*/*/index'));
        }
    }

}
