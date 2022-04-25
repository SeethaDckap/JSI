<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Cre\Model;



class Token extends \Magento\Framework\Model\AbstractModel 
{

    protected $customerSession;

    protected $_isOffline = false;

    /**
     * Availability options
     */
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = false;
    protected $_canCapturePartial = false;
    protected $_canRefund = false;
    protected $_canRefundInvoicePartial = false;
    protected $_canVoid = false;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;
    protected $_canFetchTransactionInfo = false;
    protected $_canReviewPayment = false;       


    protected $tokenCollectionFactory;    

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $salesOrderFactory;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteQuoteFactory;    

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;    

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $checkoutSession;       

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\OrderFactory $salesOrderFactory,
        \Magento\Quote\Model\QuoteFactory $quoteQuoteFactory,
        \Epicor\Cre\Model\ResourceModel\Token\CollectionFactory $tokenCollectionFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->salesOrderFactory = $salesOrderFactory;
        $this->quoteQuoteFactory = $quoteQuoteFactory;
        $this->urlBuilder = $urlBuilder;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->tokenCollectionFactory = $tokenCollectionFactory;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }


    public function _construct()
    {

        $this->_init('Epicor\Cre\Model\ResourceModel\Token');
    }    


    public function requestToken($paymentDetails)
    {
        $customer_id='';
        if ($this->customerSession->isLoggedIn()) {
            $customer_id = $this->customerSession->getCustomer()->getId();
        }        
        $this->getEntityId(null);
        $this->setCcvToken($paymentDetails->getToken());
        $this->setCvvToken($paymentDetails->getCvv());
        $this->setLastFour($paymentDetails->getLastfour());
        $this->setCardType($paymentDetails->getCardtype());
        $expdate = $paymentDetails->getExpdate();
        $pos = 2;
        $Month       = substr($expdate, 0, $pos);
        $Year        = substr($expdate, -2);        
        $this->setExpiryDate(strtotime($Year . '-' . $Month . '-01'));
        $this->setCustomerId($customer_id);
        $this->setCreTransactionId($paymentDetails->getTransid());
        $this->save();      
        $paymentDetails->setCcExpMonth($Month);
        $paymentDetails->setCcExpYear($Year);
        $this->saveInQuote($paymentDetails);  
        return true;
    }


    public function saveInQuote($token)
    {
        $quote = $this->checkoutSession->getQuote();
        $quote->getPayment()->setEccCcvToken($token->getToken());
        $quote->getPayment()->setEccCvvToken($token->getCvv());
        $quote->getPayment()->setCcExpMonth($token->getCcExpMonth());
        $quote->getPayment()->setCcExpYear($token->getCcExpYear());
        $quote->getPayment()->setEccCvvToken($token->getCvv());
        $quote->getPayment()->setCreTransactionId($token->getTransid());
        $quote->getPayment()->setCcType($token->getCardtype());
        $quote->getPayment()->setCcLast4($token->getLastfour());
        $quote->getPayment()->save();         
    }

}