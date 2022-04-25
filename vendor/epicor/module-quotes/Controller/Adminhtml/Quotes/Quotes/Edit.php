<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Controller\Adminhtml\Quotes\Quotes;

class Edit extends \Epicor\Quotes\Controller\Adminhtml\Quotes\Quotes
{

    /**
     * @var \Epicor\Quotes\Model\QuoteFactory
     */
    protected $quotesQuoteFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
     /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    //protected $backendSession;
    
    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
          \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory
        )
    {
        $this->quotesQuoteFactory = $quotesQuoteFactory;
        $this->registry = $context->getRegistry();
        parent::__construct($context, $backendAuthSession,$quotesQuoteFactory);
    }
    
    public function execute()
    {

        $quote = $this->quotesQuoteFactory->create()->load($this->getRequest()->get('id'));
        /* @var $quote Epicor_Quotes_Model_Quote */
       
        $quote->getCustomer(true);
        $this->registry->register('quote', $quote);
      
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Epicor_Quotes::sales_quotes');
        
        return $resultPage;
    }
}
