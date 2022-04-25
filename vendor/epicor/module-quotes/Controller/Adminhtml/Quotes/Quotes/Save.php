<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Controller\Adminhtml\Quotes\Quotes;

class Save extends \Epicor\Quotes\Controller\Adminhtml\Quotes\Quotes
{

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;
    /*
    public function __construct(
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Backend\Helper\Data $backendHelper
    ) {
        $this->backendSession = $backendSession;
        $this->backendHelper = $backendHelper;
    }
     */
        public function __construct(
           \Epicor\Comm\Controller\Adminhtml\Context $context,
           \Magento\Backend\Model\Auth\Session $backendAuthSession,
            \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory
           )
       {
           $this->quotesQuoteFactory = $quotesQuoteFactory;
           $this->backendHelper = $context->getHelper();
           $this->registry = $context->getRegistry();

        
           parent::__construct($context, $backendAuthSession, $quotesQuoteFactory);
       }
    
        public function execute()
        {

            $successMsg = __('Quote has been saved');
            $errorMsg = __('Error occurred while trying to save the quote');
            $error = true;

            if ($this->savePost()) {

                $this->messageManager->addSuccess($successMsg);
                $errorMsg = '';
                $error = false;
            }

            $this->getResponse()->setBody(
                json_encode(
                    array(
                        'redirectUrl' => $this->backendHelper->getUrl('*/*/'),
                        'error' => $error,
                        'errorMsg' => $errorMsg
                    )
                )
            );
        }
}
