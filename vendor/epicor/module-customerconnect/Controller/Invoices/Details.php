<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Invoices;

class Details extends \Epicor\Customerconnect\Controller\Invoices
{
    const FRONTEND_RESOURCE = 'Epicor_Customerconnect::customerconnect_account_invoices_details';

    public function execute()
    {
        if ($this->_loadInvoice()) {
            $resultPage = $this->resultPageFactory->create();
            $pageMainTitle = $resultPage->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle && $pageMainTitle instanceof \Magento\Theme\Block\Html\Title) {
                $invoice = $this->registry->registry('customer_connect_invoices_details');
                $pageMainTitle->setPageTitle(__('Invoice Number : %1', $invoice->getInvoiceNumber()));
            }
            return $resultPage;
        }

        if ($this->messageManager->getMessages()->getItems()) {
            session_write_close();
            $this->_redirect('*/*/index');
        }
    }

}
