<?php
namespace Epicor\Customerconnect\Plugin\Arpayments;

class ArPaymentInformationManagementPlugin
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
    
    
    
    public function __construct(\Epicor\Customerconnect\Helper\Arpayments $arpaymentsHelper, 
                                \Magento\Quote\Model\QuoteRepository $quoteRepository,
                                \Magento\Customer\Model\CustomerFactory $customerCustomerFactory, 
                                \Magento\Customer\Model\Session $customerSession, 
                                \Magento\Framework\Session\SessionManagerInterface $sessionManager, 
                                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory, 
                                \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager)
    {
        $this->arpaymentsHelper        = $arpaymentsHelper;
        $this->_cookieManager          = $cookieManager;
        $this->quoteRepository         = $quoteRepository;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->customerSession         = $customerSession;
        $this->_cookieMetadataFactory  = $cookieMetadataFactory;
        $this->sessionManager          = $sessionManager;
    }
    
    public function beforeSavePaymentInformation(\Magento\Checkout\Model\PaymentInformationManagement $subject,
                                                  $cartId, 
                                                  \Magento\Quote\Api\Data\PaymentInterface $paymentMethod, 
                                                  \Magento\Quote\Api\Data\AddressInterface $billingAddress = null)
    {
        $arPaymentsPage = $this->arpaymentsHelper->checkArpaymentsPage();
        if ($arPaymentsPage) {
            $sessionQuote = $this->arpaymentsHelper->getArpaymentsSessionQuoteId();
            return array(
                $sessionQuote,
                $paymentMethod,
                $billingAddress
            );
        } else {
            return array(
                $cartId,
                $paymentMethod,
                $billingAddress
            );
        }
    }
    
    public function beforeSavePaymentInformationAndPlaceOrder(\Magento\Checkout\Model\PaymentInformationManagement $subject, $cartId, 
                                                               \Magento\Quote\Api\Data\PaymentInterface $paymentMethod, 
                                                               \Magento\Quote\Api\Data\AddressInterface $billingAddress = null)
    {
        $arPaymentsPage = $this->arpaymentsHelper->checkArpaymentsPage();
        if ($arPaymentsPage) {
            $sessionQuote = $this->arpaymentsHelper->getArpaymentsSessionQuoteId();
            return array(
                $sessionQuote,
                $paymentMethod,
                $billingAddress
            );
        } else {
            return array(
                $cartId,
                $paymentMethod,
                $billingAddress
            );
        }
    }
}