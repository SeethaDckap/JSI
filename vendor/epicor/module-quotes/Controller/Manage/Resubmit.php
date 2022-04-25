<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Controller\Manage;

class Resubmit extends \Epicor\Quotes\Controller\Manage
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Quotes\Model\QuoteFactory
     */
    protected $quotesQuoteFactory;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory,
         \Magento\Checkout\Model\Session $checkoutSession,  
         \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Framework\Registry $registry)
    {
        $this->quotesQuoteFactory = $quotesQuoteFactory;
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->checkoutCart = $checkoutCart;
        $this->checkoutSession = $checkoutSession;
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
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Session\Generic $generic
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->cache = $cache;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->quotesQuoteFactory = $quotesQuoteFactory;
        $this->checkoutCart = $checkoutCart;
        $this->checkoutSession = $checkoutSession;
        $this->generic = $generic;
        parent::__construct(
            $context
        );
    }
*/

    public function execute()
    {
        $successMsg = __('Quote re-submitted for review');
        $errorMsg = __('Error has occurred while re-submitting this quote');

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

            $note = $this->getRequest()->get('note');

            if (!$note) {
                $errorMsg = __('Comment was empty. Please try again.');
                throw new \Exception('No Note found, Comment was empty. Please try again.');
            }

            $quote->addNote($note);
            $quote->setStatusId(\Epicor\Quotes\Model\Quote::STATUS_PENDING_RESPONSE);
            $quote->save();

            $cart = $this->checkoutCart;
            $cartQuote = $cart->getQuote();

            if ($cartQuote->getEccQuoteId() == $quote->getId()) {
                $cart->truncate()->save();
                $this->checkoutSession->setCartWasUpdated(true);
                $successMsg .= __(' The quote was also removed from your Cart');
            }

            $this->messageManager->addSuccess($successMsg);
            $error = false;
        } catch (\Exception $e) {
            $this->messageManager->addError($errorMsg . ':' . $e->getMessage());
        } catch (\Magento\Framework\Exception\InputException $e) {
            $this->messageManager->addError($errorMsg . ':' . $e->getMessage());
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererUrl();
        return $resultRedirect;
    }

}
