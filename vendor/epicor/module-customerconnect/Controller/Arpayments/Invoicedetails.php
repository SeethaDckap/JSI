<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Arpayments;

class Invoicedetails extends \Epicor\Customerconnect\Controller\Invoices
{
    const FRONTEND_RESOURCE = 'Epicor_Customerconnect::customerconnect_account_ar_payment_payment_details';

    public function execute()
    {
        if($this->_loadInvoice()) {
            $resultPage = $this->resultPageFactory->create();
            $pageMainTitle = $resultPage->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle && $pageMainTitle instanceof \Magento\Theme\Block\Html\Title) {
                $order = $this->registry->registry('customer_connect_invoices_details');
                $pageMainTitle->setPageTitle(__('Invoice Number : %1', $order->getOrderNumber()));
            }
            return $resultPage;
        }
        if ($this->messageManager->getMessages()->getItems()) {
            /** @var \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory * */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setUrl($this->_url->getUrl('*/*/index'));
        }
    }

}
