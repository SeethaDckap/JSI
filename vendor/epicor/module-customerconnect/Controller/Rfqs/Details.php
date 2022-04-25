<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Rfqs;

class Details extends \Epicor\Customerconnect\Controller\Rfqs
{

    const FRONTEND_RESOURCE = 'Epicor_Customerconnect::customerconnect_account_rfqs_details';

    public function execute()
    {
        if ($this->_loadRfq()) {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('Customer Connect RFQ Details'));
            $pageMainTitle = $resultPage->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle && $pageMainTitle instanceof \Magento\Theme\Block\Html\Title) {
                $rfq = $this->registry->registry('customer_connect_rfq_details');
                $pageMainTitle->setPageTitle(__('Quote Number : %1', $rfq->getQuoteNumber()));
            }
            return $resultPage;
        }

        if ($this->messageManager->getMessages()->getItems()) {
            session_write_close();
            $this->_redirect('*/*/index');
        }
    }

}
