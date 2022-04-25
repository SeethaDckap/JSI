<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Controller\Manage;

class Accept extends \Epicor\Quotes\Controller\Manage
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager; 

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Quotes\Model\QuoteFactory
     */
    protected $quotesQuoteFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory,
        \Magento\Framework\Registry $registry)
    {
        $this->quotesQuoteFactory = $quotesQuoteFactory;
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }
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
    }
    */

   public function execute()
    {
        $successMsg = __('Quote has been accepted');
        $errorMsg = __('Error has occurred while accepting the quote');
        $error = true;
        $notLoggedIn = false;
        try {
            $quote = $this->quotesQuoteFactory->create()->load($this->getRequest()->get('id'));
            /* @var $quote Epicor_Quotes_Model_Quote */
            $customer = $this->customerSession->getCustomer();
            /* @var $customer Epicor_Comm_Model_Customer */

            if (!$quote->canBeAccessedByCustomer($customer)) {
                $errorMsg .= __(': You do not have permission to access this quote');
                if(!$this->customerSession->isLoggedIn()){                    
                    $notLoggedIn = true;
                    $error = true;
                }
                throw new \Exception('Invalid customer');
            }
            
            if (!$this->customerSession->authenticate($this)) {
                return;
            }

            if (!$quote->isAcceptable()) {
                $errorMsg .= __(': Quote cannot be Accepted');
                throw new \Exception('Quote cannot be Accepted');
            }

            if ($quote->productsSaleable()) {
                $quote->setStatusId(\Epicor\Quotes\Model\Quote::STATUS_QUOTE_ACCEPTED);
                $quote->save();

                $this->messageManager->addSuccess($successMsg);
                $error = false;
            } else {
                $errorMsg .= __('Could not accept quote, one or more products are no longer available');
                throw new \Exception('Could not accept quote, one or more products are no longer available');
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($errorMsg);
        } catch (Mage_Exception $e) {
            $this->messageManager->addError($errorMsg);
        }

        if ($error) {
            //$this->_redirectReferer();
            if($notLoggedIn){
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('b2b/portal/login');
                return $resultRedirect; 
            }
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setRefererUrl();
            return $resultRedirect;
        } else {
            if ($quote->setQuoteAsCart()) {
                $this->_redirect('checkout/cart');
            } else {
                $this->_redirect('quotes/manage/accept', array('id' => $quote->getId()));
            }
        }
    }

}
