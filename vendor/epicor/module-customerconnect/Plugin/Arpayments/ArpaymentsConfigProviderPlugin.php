<?php
namespace Epicor\Customerconnect\Plugin\Arpayments;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Customer\Model\Session as CustomerSession;

class ArpaymentsConfigProviderPlugin
{
    /**
     * @var PersistentSession
     */
    private $persistentSession;
    
    /**
     * @var PersistentHelper
     */
    private $persistentHelper;
    
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;
    
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;
    
    /**
     * @var CustomerSession
     */
    private $customerSession;
    
    protected $_request;
    
    /**
     * @var \Epicor\Lists\Helper\Session
     */
    protected $listsSessionHelper;
    
    protected $quoteRepository;
    
    /*
     * @var \Epicor\SalesRep\Helper\Checkout
     */
    protected $arpaymentsHelper;
    
    /**
     * @param PersistentHelper $persistentHelper
     * @param PersistentSession $persistentSession
     * @param CheckoutSession $checkoutSession
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param CustomerSession $customerSession
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        \Epicor\Customerconnect\Helper\Arpayments $arpaymentsHelper,      
        CustomerSession $customerSession,
        \Epicor\Lists\Helper\Session $listsSessionHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->customerSession = $customerSession;
        $this->_request = $request;
        $this->listsSessionHelper = $listsSessionHelper;
        $this->arpaymentsHelper = $arpaymentsHelper;
    }
    
    /**
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetConfig(\Magento\Checkout\Model\DefaultConfigProvider $subject, array $result)
    {
        $handle = $this->arpaymentsHelper->checkArpaymentsPage();
        if ($handle) {
            $sessionHelper                   = $this->listsSessionHelper;
            /* @var $sessionHelper Epicor_Lists_Helper_Session */
            $quoteId                         = $sessionHelper->getValue('ecc_arpayments_quote');
            /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
            $quote                           = $this->quoteRepository->get($quoteId);
            $quoteData                       = $quote->toArray();
            $result['defaultSuccessPageUrl'] = 'customerconnect/arpayments/Redirectpage';
            $result['checkoutUrl']           = 'customerconnect/arpayments/';
            $result['quoteData']             = $quoteData;
            $result['arPaymentQuote']        = $this->arpaymentsHelper->getArpaymentsQuote()->getBillingAddress()->getData();
            $result['arPaymentCheckout']     = TRUE;
        }
        return $result;
    }
}