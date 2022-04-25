<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Controller\Manage;

class View extends \Epicor\Quotes\Controller\Manage
{


    const FRONTEND_RESOURCE = 'Epicor_Customer::my_account_quotes_details';
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Quotes\Model\QuoteFactory
     */
    protected $quotesQuoteFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

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
        $error = true; 
        $errorMsg = __('Error trying to retrieve Quote');
        $notLoggedIn = false;
         
        /*
        if (!$this->customerSession->authenticate($this)) {
            return;
        } */

        try {  
            $quote = $this->quotesQuoteFactory->create()->load($this->getRequest()->get('id'));
            /* @var $quote Epicor_Quotes_Model_Quote */
            $customer = $this->customerSession->getCustomer();
            /* @var $customer Epicor_Comm_Model_Customer */
            if ($quote->canBeAccessedByCustomer($customer)) {

                $this->registry->register('quote', $quote);
                $error = false;
                $resultPage = $this->resultPageFactory->create();
                return $resultPage;
            } else {
                $errorMsg .= __(': You do not have permission to access this quote');
                if(!$this->customerSession->isLoggedIn()){                    
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath('b2b/portal/login');
                    return $resultRedirect; 
                }
                throw new \Exception('Invalid customer');
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($errorMsg);
        } catch (Mage_Exception $ee) {
            $this->messageManager->addError($errorMsg);
        }
        if ($error) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setRefererUrl();
            return $resultRedirect;
        }
    }
}
