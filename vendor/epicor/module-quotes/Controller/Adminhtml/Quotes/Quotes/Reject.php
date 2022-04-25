<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Controller\Adminhtml\Quotes\Quotes;

class Reject extends \Epicor\Quotes\Controller\Adminhtml\Quotes\Quotes
{

     /**
      * @var \Epicor\Quotes\Model\QuoteFactory
     */
      protected $quotesQuoteFactory;
    
      public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
         \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory
        )
    {
        $this->quotesQuoteFactory = $quotesQuoteFactory;
        parent::__construct($context, $backendAuthSession, $quotesQuoteFactory);
    }
    
    public function execute()
    {
        $successMsg = __('Quote has been rejected');
        $errorMsg = __('Error occurred while trying to reject the quote');
        $error = true;
        try {
            $quote = $this->quotesQuoteFactory->create()->load($this->getRequest()->get('id'));
            /* @var $quote Epicor_Quotes_Model_Quote */
            $quote->setStatusId(\Epicor\Quotes\Model\Quote::STATUS_QUOTE_REJECTED_ADMIN);
            $quote->save();

            $this->messageManager->addSuccess($successMsg);
            $error = false;
            } catch (\Exception $e) {
                $this->messageManager->addError($errorMsg);
            } catch (Mage_Exception $e) {
                $this->messageManager->addError($errorMsg);
            }

           $resultRedirect = $this->resultRedirectFactory->create();
           $resultRedirect->setRefererUrl();
           return $resultRedirect;
    }
}
