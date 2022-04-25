<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Crqs;

class Details extends \Epicor\SalesRep\Controller\Crqs
{
    /*
     * @var  \Magento\Framework\View\Result\PageFactory
     */
    protected   $resultPageFactory;
    
    public function __construct(
          \Epicor\SalesRep\Controller\Context $context
    ) {
        $this->resultPageFactory = $context->getResultPageFactory();
         parent::__construct(
            $context
        );
    }
    
    public function execute()
    { 
        if ($this->_loadRfq()) { 
             $resultPage = $this->resultPageFactory->create();
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
