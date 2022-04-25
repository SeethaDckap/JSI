<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Controller\Adminhtml\Quotes\Quotes;

class Reactivate extends \Epicor\Quotes\Controller\Adminhtml\Quotes\Quotes
{

    /**
     * @var \Epicor\Quotes\Model\QuoteFactory
     */
    protected $quotesQuoteFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

     public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
         \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
         \Psr\Log\LoggerInterface $logger
        )
    {
        $this->quotesQuoteFactory = $quotesQuoteFactory;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        
        parent::__construct($context, $backendAuthSession, $quotesQuoteFactory);
    }
  
    public function execute()
    {
        $successMsg = __('Quote has been reactivated');
        $errorMsg = __('Error occurred while trying to reactivate the quote');

        try {
            $quote = $this->quotesQuoteFactory->create()->load($this->getRequest()->get('id'));
            /* @var $quote Epicor_Quotes_Model_Quote */

            $daysTillExpired = $this->scopeConfig->getValue('epicor_quotes/general/days_till_expired', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?: 5;
            $quote->setExpires(strtotime('+' . $daysTillExpired . ' days'));
            $quote->setStatusId(\Epicor\Quotes\Model\Quote::STATUS_PENDING_RESPONSE);
            $quote->save();

            $this->messageManager->addSuccess($successMsg);
        } catch (\Exception $e) {
            $this->logger->log(null, var_export($e,true));
            $this->messageManager->addError($errorMsg);
        } catch (Mage_Exception $e) {
            $this->logger->log(null, var_export($e,true));
            $this->messageManager->addError($errorMsg);
        }
           $resultRedirect = $this->resultRedirectFactory->create();
           $resultRedirect->setRefererUrl();
           return $resultRedirect;
    }

}
