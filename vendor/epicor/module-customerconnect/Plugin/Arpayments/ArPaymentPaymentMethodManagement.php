<?php
namespace Epicor\Customerconnect\Plugin\Arpayments;

class ArPaymentPaymentMethodManagement
{
    
    /*
     * @var \Epicor\SalesRep\Helper\Checkout
     */
    protected $arpaymentsHelper;
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManager;
    
    protected $quoteRepository;
    
    protected $customerCustomerFactory;
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    
    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $_cookieMetadataFactory;
    
    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;
    
    
    
    public function __construct(
        \Epicor\Customerconnect\Helper\Arpayments $arpaymentsHelper,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager    
    ) {
        $this->arpaymentsHelper = $arpaymentsHelper;
        $this->_cookieManager = $cookieManager;
        $this->quoteRepository = $quoteRepository;
        $this->customerCustomerFactory = $customerCustomerFactory; 
        $this->customerSession = $customerSession; 
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionManager = $sessionManager;
    }
    
    public function beforeSet(\Magento\Quote\Model\PaymentMethodManagement $subject, $cartId, 
                              \Magento\Quote\Api\Data\PaymentInterface $method)
    {
        $arPaymentsPage = $this->arpaymentsHelper->checkArpaymentsPage();
        if ($arPaymentsPage) {
            $sessionQuote = $this->arpaymentsHelper->getArpaymentsSessionQuoteId();
            return array(
                $sessionQuote,
                $method
            );
        } else {
            return array(
                $cartId,
                $method
            );
        }
    }
    
    public function beforeGet(\Magento\Quote\Model\PaymentMethodManagement $subject, $cartId)
    {
        $arPaymentsPage = $this->arpaymentsHelper->checkArpaymentsPage();
        if ($arPaymentsPage) {
            $sessionQuote = $this->arpaymentsHelper->getArpaymentsSessionQuoteId();
            return array(
                $sessionQuote
            );
        } else {
            return array(
                $cartId
            );
        }
    }
    
}