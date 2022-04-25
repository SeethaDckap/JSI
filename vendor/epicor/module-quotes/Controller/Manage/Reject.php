<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Controller\Manage;

class Reject extends \Epicor\Quotes\Controller\Manage
{

      /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Quotes\Model\QuoteFactory
     */
    protected $quotesQuoteFactory;

    
    /*
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory,
        \Magento\Framework\Session\Generic $generic
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->cache = $cache;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->quotesQuoteFactory = $quotesQuoteFactory;
        $this->generic = $generic;
        parent::__construct(
            $context
        );
    } */
     public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
       \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory
    ) {
        $this->customerSession = $customerSession;
        $this->quotesQuoteFactory = $quotesQuoteFactory;
         parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }


    public function execute()
      {
        $successMsg = __('Quote has been rejected');
        $errorMsg = __('Error has occurred while rejecting the quote');

        if (!$this->customerSession->authenticate($this)) {
            return;
        }

        try {

            $quote = $this->quotesQuoteFactory->create()->load($this->getRequest()->get('id'));
            /* @var $quote Epicor_Quotes_Model_Quote */
            $customer = $this->customerSession->getCustomer();
            /* @var $customer Epicor_Comm_Model_Customer */

            if (!$quote->canBeAccessedByCustomer($customer)) {
                $errorMsg .= __(': You do not have permission to access this quote');
                throw new \Exception('Invalid customer');
            }

            $quote->setStatusId(\Epicor\Quotes\Model\Quote::STATUS_QUOTE_REJECTED_CUSTOMER);
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
