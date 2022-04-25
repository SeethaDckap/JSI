<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Controller\Manage;

class Update extends \Epicor\Quotes\Controller\Manage
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    //protected $customerSession;

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
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
       )
    {
        $this->quotesQuoteFactory = $quotesQuoteFactory;
        $this->scopeConfig = $scopeConfig;
        
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

        $successMsg = __('Options updated successfully');
        $errorMsg = __('An error has occurred while updating quote options');

        if (!$this->customerSession->authenticate($this)) {
            return;
        }

        if ($data = $this->getRequest()->getPost()) {
            try {

                $quote = $this->quotesQuoteFactory->create()->load($this->getRequest()->get('id'));
                /* @var $quote Epicor_Quotes_Model_Quote */
                $customer = $this->customerSession->getCustomer();
                /* @var $customer Epicor_Comm_Model_Customer */

                if (!$quote->canBeAccessedByCustomer($customer)) {
                    $errorMsg .= __(': You do not have permission to access this quote');
                    throw new \Exception('Invalid customer');
                }

                $customerGlobal = $this->scopeConfig->isSetFlag('epicor_quotes/general/allow_customer_global', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if ($customer->isCustomer() && $customerGlobal) {
                    $quote->setIsGlobal(isset($data['is_global']) ? true : false);
                }

                $quote->setSendCustomerComments(isset($data['send_comments']) ? true : false);
                $quote->setSendCustomerReminders(isset($data['send_reminders']) ? true : false);
                $quote->setSendCustomerUpdates(isset($data['send_updates']) ? true : false);
                $quote->save();

                $this->messageManager->addSuccess($successMsg);
             } catch (\Exception $e) {
                $this->messageManager->addError($errorMsg);
            } catch (Mage_Exception $ee) {
                $this->messageManager->addError($errorMsg);
            }
        }
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setRefererUrl();
            return $resultRedirect;
    }

}
