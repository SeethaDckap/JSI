<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Elements\Model\Payment;
/**
 * Elements 
 * 
 * @category    Epicor
 * @package     Epicor_Elements
 * @author      Epicor Web Sales Team
 */



class Elements extends \Magento\Payment\Model\Method\AbstractMethod
{

    protected $_code = "elements";
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
    private $elements;    
    private $arelements;



    /**
     * @var \Epicor\Elements\Model\TransactionFactory
     */
    protected $elementsTransactionFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Epicor\Elements\Model\TokenFactory
     */
    protected $elementsTokenFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /*
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    protected $_checkoutSession;
    
    protected $elementsArTransactionFactory;
    
    protected $checkoutArSession;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Payment\Model\Method\Logger $logger,
        \Epicor\Elements\Model\TransactionFactory $elementsTransactionFactory,
        \Epicor\Elements\Model\ArTransactionFactory $elementsArTransactionFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Epicor\Customerconnect\Model\ArPayment\Session $checkoutArSession,
        //\Epicor\Elements\Model\TokenFactory $elementsTokenFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->elementsTransactionFactory = $elementsTransactionFactory;
        $this->elementsArTransactionFactory = $elementsArTransactionFactory;
        $this->request = $request;
       // $this->elementsTokenFactory = $elementsTokenFactory;
        $this->customerSession = $customerSession;
        $this->_checkoutSession = $_checkoutSession;
        $this->checkoutArSession = $checkoutArSession;
        $this->scopeConfig = $scopeConfig;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->getElements();
    }
    

    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        return parent::isAvailable($quote);
    }

    /**
     * 
     * @return \Epicor\Elements\Model\Transaction
     */
    public function getElementsTransaction($transactionId = null)
    {
        if (!$this->elements || $transactionId != null) {
            $this->elements = $this->elementsTransactionFactory->create()->load($transactionId);
        }
        return $this->elements;
    }
    
    
    /**
     * 
     * @return \Epicor\Elements\Model\Transaction
     */
    public function getArElementsTransaction($transactionId = null)
    {
        if (!$this->arelements || $transactionId != null) {
            $this->arelements = $this->elementsArTransactionFactory->create()->load($transactionId);
        }
        return $this->arelements;
    }    

    /**
     * 
     * @param array $paymentDetails
     * @param string $ref
     * @return \Epicor\Elements\Model\Token
     */
    public function getToken($paymentDetails, $tokenId)
    {
        $token = $this->getElements()->requestToken($paymentDetails, $tokenId);
        return $token;
    }


    /**
     * this method is called if we are just authorising
     * a transaction
     */
    public function authorize(\Magento\Payment\Model\InfoInterface  $payment, $amount)
    {
        $order = $payment->getOrder();
        $elementsTransactionId = $this->_checkoutSession->getQuote()->getPayment();
        $elements = $this->getElementsTransaction($elementsTransactionId->getEccElementsTransactionId());
        $cvvEnabled = $this->scopeConfig->getValue('payment/elements/CVVEnabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        //If cvv was enabled in ECC then there is no need to do card authorization again
        if ($cvvEnabled) {
            $elements->creditCardCvvNoAuth($order, $amount);
        } else {
            //check if authorisation call is to be made in ECC
            $authorizeInEcc = $this->scopeConfig->getValue('payment/elements/authorizeInEcc', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if($authorizeInEcc){
                $elements->creditCardAuth($order, $amount);
            }else{
                //this needs to be set or following code will fail
                $elements->setCreditCardAuthExpressResponseCode('0');
            }
        }

        if ($elements->successfulCreditCardAuth()) {
            $this->addTransaction($payment, $elements);
        } else {
            $elements->paymentAccountDelete();
            throw new \Magento\Framework\Exception\LocalizedException(__("Payment Failed \nPlease Try again or use another payment method"));
        }

        return $this;
    }


    /**
     * Add a transaction to an order
     * 
     * 
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @param \Epicor\Elements\Model\Transaction $elements
     * 
     */
    public function addTransaction($payment, $elements)
    {

        $payment->setTransactionAdditionalInfo(
            \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS, array(
            'test' => 'test 1234',
            'test 2' => 'Jimbo',
        ));

        $payment->setIsTransactionClosed(false);
        $payment->setShouldCloseParentTransaction(false);
        $payment->setParentTransactionId(null);
        $payment->setTransactionId($elements->getTransactionId());
    }
    
    public function updateTransaction($order)
    {
        
        $id = $order->getPayment()->getEccElementsTransactionId();
        if($id){
            $amount = $order->getGrandTotal();
            $elements = $this->getArElementsTransaction($id);
            $cvvEnabled = $this->scopeConfig->getValue('payment/elements/CVVEnabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            //If cvv was enabled in ECC then there is no need to do card authorization again            
            if ($cvvEnabled) {
                $elements->creditCardCvvNoAuth($order, $amount);
            } else {
                //check if authorisation call is to be made in ECC
                $authorizeInEcc = $this->scopeConfig->getValue('payment/elements/authorizeInEcc', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if($authorizeInEcc){
                    $elements->creditCardAuth($order, $amount);
                }else{
                    //this needs to be set or following code will fail
                    $elements->setCreditCardAuthExpressResponseCode('0');
                }
            }
            if ($elements->successfulCreditCardAuth()) {
                $transaction =  $this->elementsTransactionFactory->create()->load($id);
                $transaction->setOrder($order);
                $transaction->save();
                return $transaction;
            } else {
                $elements->paymentAccountDelete();
                throw new \Magento\Framework\Exception\LocalizedException(__("Payment Failed \nPlease Try again or use another payment method"));
            }
        }
        return false;
    }        


}