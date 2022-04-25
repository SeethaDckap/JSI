<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Elements\Model;

use Epicor\Elements\Service\ElementsConfiguration;

/**
 * Elements model
 * 
 * @category    Epicor
 * @package     Epicor_Elements
 * @author      Epicor Web Sales Team
 * 
 *  
 * @method string getError()
 * @method setError(string $errorMsg)
 * 
 * @method int getQuoteId()
 * @method setQuoteId(int $quoteId)
 * 
 * @method int getOrderId()
 * @method setOrderId(int $orderId)
 * 
 * @method string getRef()
 * @method setRef(string $ref)
 * 
 * * * * * * * * * * * * * * * * * *
 * Transaction Setup Value Methods *
 * * * * * * * * * * * * * * * * * *
 * @method int getTransactionSetupExpressResponseCode()
 * @method setTransactionSetupExpressResponseCode(int $code)
 * 
 * @method string getTransactionSetupExpressResponseMessage()
 * @method setTransactionSetupExpressResponseMessage(string $message)
 * 
 * @method string getTransactionSetupId()
 * @method setTransactionSetupId(string $setupId)
 * 
 * @method string getTransactionValidationCode()
 * @method setTransactionValidationCode(string $validationCode)
 * 
 * 
 * * * * * * * * * * * * * * * * *
 * Hosted Payment Value Methods  *
 * * * * * * * * * * * * * * * * *
 * @method string getHostedExpressResponseCode()
 * @method setHostedExpressResponseCode(string $code)
 * 
 * @method string getHostedExpressResponseMessage()
 * @method setHostedExpressResponseMessage(string $message)
 * 
 * @method string getHostedPaymentStatus()
 * @method setHostedPaymentStatus(string $status)
 * 
 * @method string getHostedServicesId()
 * @method setHostedServicesId(string $servicesId)
 * 
 * @method string getPaymentAccountId()
 * @method setPaymentAccountId(string $paymentAccountId)
 * 
 * @method string getLastFour()
 * @method setLastFour(string $lastFour)
 * 
 * @method string getHostedValidationCode()
 * @method setHostedValidationCode(string $validationCode)
 * 
 * @method string getCvvResponseCode()
 * @method setCvvResponseCode(string $lastFour)
 * 
 * 
 * * * * * * * * * * * * * * * * * * * *
 * Payment Account Query Value Methods *
 * * * * * * * * * * * * * * * * * * * *
 * @method string getPaymentAccountQueryExpressResponseCode()
 * @method setPaymentAccountQueryExpressResponseCode(string $code)
 * 
 * @method string getPaymentAccountQueryExpressResponseMessage()
 * @method setPaymentAccountQueryExpressResponseMessage(string $message)
 * 
 * @method string getPaymentAccountQueryServicesId()
 * @method setPaymentAccountQueryServicesId(string $servicesId)
 * 
 * @method string getPaymentAccountType()
 * @method setPaymentAccountType(string $type)
 * 
 * @method string getTruncatedCardNumber()
 * @method setTruncatedCardNumber(string $cardNumber)
 * 
 * @method string getExpirationMonth()
 * @method setExpirationMonth(string $month)
 * 
 * @method string getExpirationYear()
 * @method setExpirationYear(string $year)
 * 
 * @method string getPaymentAccountReferenceNumber()
 * @method setPaymentAccountReferenceNumber(string $paymentAccountRef)
 * 
 * @method string getPaymentBrand()
 * @method setPaymentBrand(string $cardType)
 * 
 * @method string getPassUpdaterBatchStatus()
 * @method setPassUpdaterBatchStatus(string $status)
 * 
 * @method string getPassUpdaterStatus()
 * @method setPassUpdaterStatus(string $status)
 * 
 * 
 * * * * * * * * * * * * * * * * * *
 * Credit Card Auth Value Methods  *
 * * * * * * * * * * * * * * * * * *
 * @method string getCreditCardAuthExpressResponseCode()
 * @method setCreditCardAuthExpressResponseCode(string $code)
 * 
 * @method string getCreditCardAuthExpressResponseMessage()
 * @method setCreditCardAuthExpressResponseMessage(string $message)
 * 
 * @method string getCreditCardAuthHostResponseCode()
 * @method setCreditCardAuthHostResponseCode(string $code)
 * 
 * @method string getCreditCardAuthHostResponseMessage()
 * @method setCreditCardAuthHostResponseMessage(string $message)
 * 
 * @method string getAvsResponseCode()
 * @method setAvsResponseCode(string $code)
 * 
 * @method string getCardLogo()
 * @method setCardLogo(string $logo)
 * 
 * @method string getTransactionId()
 * @method setTransactionId(string $transactionId)
 * 
 * @method string getApprovalNumber()
 * @method setApprovalNumber(string $approvalNumber)
 * 
 * @method string getReferenceNumber()
 * @method setReferenceNumber(string $ref)
 * 
 * @method string getAcquirerData()
 * @method setAcquirerData(string $data)
 * 
 * @method string getProcessorName()
 * @method setProcessorName(string $processorName)
 * 
 * @method string getTransactionStatus()
 * @method setTransactionStatus(string $status)
 * 
 * @method string getTransactionStatusCode()
 * @method setTransactionStatusCode(string $code)
 * 
 * @method string getApprovedAmount()
 * @method setApprovedAmount(string $ampunt)
 * 
 * @method string getBillingAddress1()
 * @method setBillingAddress1(string $addressLine1)
 * 
 * @method string getBillingZipcode()
 * @method setBillingZipcode(string $zipcode)
 * 
 * 
 * * * * * * * * * * * * * * * * * * * * *
 * Payment Account Delete Value Methods  *
 * * * * * * * * * * * * * * * * * * * * *
 * @method string getPaymentAccountDeleteExpressResponseCode()
 * @method setPaymentAccountDeleteExpressResponseCode(string $code)
 * 
 * @method string getPaymentAccountDeleteExpressResponseMessage()
 * @method setPaymentAccountDeleteExpressResponseMessage(string $message)
 * 
 * @method string getPaymentAccountDeleteServicesId()
 * @method setPaymentAccountDeleteServicesId(string $servicesId)
 *
 */
class ArTransaction extends \Epicor\Database\Model\Elements\Transaction
{

    /** @var \Epicor\Elements\Model\Api */
    protected $_api;

    /** @var  \Magento\Sales\Model\Order */
    protected $_order;

    /** @var \Magento\Quote\Model\Quote */
    protected $_quote;

    /**
     * @var \Epicor\Elements\Model\Api
     */
    protected $elementsApi;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $salesOrderFactory;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Epicor\Elements\Model\Config\Source\Cvvresults
     */
    protected $cvvResultsArray;

    /**
     * @var \Epicor\Elements\Model\Validation\Cvv
     */
    private $cvvValidation;

    /**
     * @var \Epicor\Elements\Model\Validation\Avs
     */
    private $avsValidation;

    /**
     * @var ElementsConfiguration
     */
    private $elementsConfiguration;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Elements\Model\Api $elementsApi,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\OrderFactory $salesOrderFactory,
        \Epicor\Customerconnect\Model\ArPayment\Session $checkoutSession,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Epicor\Elements\Model\Validation\Cvv $cvvValidation,
        \Epicor\Elements\Model\Validation\Avs $avsValidation,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        ElementsConfiguration $elementsConfiguration,
        array $data = []
    ) {
        $this->elementsApi = $elementsApi;
        $this->scopeConfig = $scopeConfig;
        $this->salesOrderFactory = $salesOrderFactory;
        $this->checkoutSession = $checkoutSession;
        $this->urlBuilder = $urlBuilder;
        $this->cvvValidation = $cvvValidation;
        $this->avsValidation = $avsValidation;
        $this->elementsConfiguration = $elementsConfiguration;
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
        $this->_init('Epicor\Elements\Model\ResourceModel\Transaction');
    }

    /**
     * Get the Elements API class
     * 
     * @return \Epicor\Elements\Model\Api
     */
    protected function getApi()
    {

        if (!$this->_api) {
            $this->_api = $this->elementsApi;
        }

        return $this->_api;
    }

    /**
     * Format price for Elements
     * @param type $price
     */
    protected function formatPrice($price)
    {
        return number_format($price, '2', '.', '');
    }

    /**
     * Return the saved TransactionSetupId or generate one
     * 
     * @param \Magento\Framework\DataObject $paymentDetails
     * @param \Magento\Quote\Model\Quote $quote
     * @param string $ref
     * @return \Epicor\Elements\Model\Elements
     */
    public function transactionSetup($paymentDetails = null, $quote = null, $ref = null)
    {
        if ($ref != null) {
            $this->setRef($ref);
        }
        
        $getStreet = $quote->getBillingAddress()->getStreet();
        $street =  implode($getStreet, ', ');
        
        $getStreet1 = $quote->getShippingAddress()->getStreet();
        $street1 =  implode($getStreet1, ', ');
        
        $response = $this->getApi()
        ->addElement('Transaction', array(
            'ReferenceNumber' => $this->getRef(),
            'TicketNumber' => $this->getRef(),
            'TransactionAmount' => $this->formatPrice($quote->getGrandTotal()),
            'MarketCode' => $this->scopeConfig->getValue('payment/elements/MarketCode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
        ))
        ->addElement('Terminal', array(
            'TerminalID' => $this->scopeConfig->getValue('payment/elements/TerminalID', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'TerminalType' => $this->scopeConfig->getValue('payment/elements/TerminalType', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'CardPresentCode' => $this->scopeConfig->getValue('payment/elements/CardPresentCode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'CardholderPresentCode' => $this->scopeConfig->getValue('payment/elements/CardholderPresentCode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'CardInputCode' => $this->scopeConfig->getValue('payment/elements/CardInputCode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'CVVPresenceCode' => $this->scopeConfig->getValue('payment/elements/CVVEnabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'TerminalCapabilityCode' => $this->scopeConfig->getValue('payment/elements/TerminalCapabilityCode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'TerminalEnvironmentCode' => $this->scopeConfig->getValue('payment/elements/TerminalEnvironmentCode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'MotoECICode' => $this->scopeConfig->getValue('payment/elements/MotoECICode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
        ))
        ->addElement('PaymentAccount', array(
            'PaymentAccountType' => '0',
            'PaymentAccountReferenceNumber' => uniqid('', true),
        ))
        ->addElement('Address', array(
            'BillingName' => $quote->getBillingAddress()->getName(),
            'BillingAddress1' => $street,
            'BillingCity' => $quote->getBillingAddress()->getCity(),
            'BillingState' => $quote->getBillingAddress()->getRegionCode(),
            'BillingZipcode' => $quote->getBillingAddress()->getPostcode(),
            'BillingEmail' => $quote->getBillingAddress()->getEmail(),
            'BillingPhone' => $quote->getBillingAddress()->getTelephone(),
            'ShippingName' => $quote->getShippingAddress()->getName(),
            'ShippingAddress1' => $street1,
            'ShippingCity' => $quote->getShippingAddress()->getCity(),
            'ShippingState' => $quote->getShippingAddress()->getRegionCode(),
            'ShippingZipcode' => $quote->getShippingAddress()->getPostcode(),
            'ShippingEmail' => $quote->getShippingAddress()->getEmail(),
            'ShippingPhone' => $quote->getShippingAddress()->getTelephone(),
        ))
        ->addElement('TransactionSetup', array(
                //M1 > M2 Translation Begin (Rule p2-4)
                //'ReturnURL' => Mage::getUrl('elements/payment/setupreturn', array('_secure' => true)),
            'ReturnURL' => $this->urlBuilder->getUrl('elements/payment/arsetupreturn', array('_secure' => true)),
                //M1 > M2 Translation End
            'CVVRequired' => $this->scopeConfig->getValue('payment/elements/CVVEnabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'CompanyName' => $this->scopeConfig->getValue('payment/elements/CompanyName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'LogoURL' => $this->scopeConfig->getValue('payment/elements/LogoUrl', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'Tagline' => $this->scopeConfig->getValue('payment/elements/TagLine', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'WelcomeMessage' => $this->scopeConfig->getValue('payment/elements/WelcomeMessage', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'ReturnURLTitle' => '',
            'OrderDetails' => '',
            'ProcessTransactionTitle' => $this->scopeConfig->getValue('payment/elements/ButtonLabel', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'TransactionSetupMethod' => $this->scopeConfig->isSetFlag('payment/elements/CVVEnabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ? 2 : 7,
            'Embedded' => '0',
            'AutoReturn' => '1',
            'Device' => '0',
        ))
        ->transactionSetup();


        if (is_array($response)) {
            $this->setTransactionSetupExpressResponseCode(@$response['ExpressResponseCode']);
            $this->setTransactionSetupExpressResponseMessage(@$response['ExpressResponseMessage']);
            $this->setTransactionSetupId(@$response['TransactionSetup']['TransactionSetupID']);
            $this->setTransactionValidationCode(@$response['TransactionSetup']['ValidationCode']);
            $this->setQuote($quote);
        } else {
            $this->setError($response);
        }

        $this->save();
        $quote->getPayment()->setEccElementsTransactionId($this->getEntityId())->save();

        return $this;
    }

    /**
     * Get the transaction setup URL.
     *
     * @param integer $isMobile Require url for mobile or desktop.
     *
     * @return string
     */
    public function getTransactionSetupUrl(int $isMobile = 0)
    {
        $variables = [
            '{{prefix}}'             => ($this->getApi()->isTestMode() === true) ? 'cert' : '',
            '{{transactionSetupId}}' => $this->getTransactionSetupId()
        ];

        return $this->elementsConfiguration->getTransactionUrl($variables, $isMobile);

    }//end getTransactionSetupUrl()

    public function paymentAccountCreateFromTransactionId()
    {
        $quote = $this->getQuote();
        
        $getStreet = $quote->getBillingAddress()->getStreet();
        $street =  implode($getStreet, ', ');     
        
        $getStreet1 = $quote->getShippingAddress()->getStreet();
        $street1 =  implode($getStreet1, ', ');        

        $response = $this->getApi()
        ->addElement('Transaction', array(
            'TransactionID' => $this->getTransactionId(),
        ))
        ->addElement('PaymentAccount', array(
            'PaymentAccountType' => '0',
            'PaymentAccountReferenceNumber' => uniqid('', true),
        ))
        ->addElement('Address', array(
            'BillingName' => $quote->getBillingAddress()->getName(),
            'BillingAddress1' => $street,
            'BillingCity' => $quote->getBillingAddress()->getCity(),
            'BillingState' => $quote->getBillingAddress()->getRegionCode(),
            'BillingZipcode' => $quote->getBillingAddress()->getPostcode(),
            'BillingEmail' => $quote->getBillingAddress()->getEmail(),
            'BillingPhone' => $quote->getBillingAddress()->getTelephone(),
            'ShippingName' => $quote->getShippingAddress()->getName(),
            'ShippingAddress1' => $street1,
            'ShippingCity' => $quote->getShippingAddress()->getCity(),
            'ShippingState' => $quote->getShippingAddress()->getRegionCode(),
            'ShippingZipcode' => $quote->getShippingAddress()->getPostcode(),
            'ShippingEmail' => $quote->getShippingAddress()->getEmail(),
            'ShippingPhone' => $quote->getShippingAddress()->getTelephone(),
        ))
        ->paymentAccountCreateFromTransactionId();


        if (is_array($response)) {
            $this->setPaymentAccountId(@$response['PaymentAccountID']);
            $this->setPaymentAccountReferenceNumber(@$response['PaymentAccountReferenceNumber']);
        } else {
            $this->setError($response);
        }

        $this->save();

        return $this;
    }

    /**
     * Send Payment Account Query
     * 
     * @return \Epicor\Elements\Model\Elements
     */
    public function paymentAccountQuery()
    {
        if ($this->getPaymentAccountId()) {
            $response = $this->getApi()->addElement('PaymentAccountParameters', array('PaymentAccountID' => $this->getPaymentAccountId()))->paymentAccountQuery();
        } else {
            if($this->hasError()) {
                $response = $this->getError();
            } else {
                $response = 'No Payment Account Id found';
            }
        }


        if (is_array($response)) {
            $this->setPaymentAccountQueryExpressResponseCode(@$response['ExpressResponseCode']);
            $this->setPaymentAccountQueryExpressResponseMessage(@$response['ExpressResponseMessage']);
            $this->setPaymentAccountQueryServicesId(@$response['ServicesID']);
            $this->setPaymentAccountType(@$response['PaymentAccountType']);
            $this->setTruncatedCardNumber(@$response['TruncatedCardNumber']);
            $this->setExpirationMonth(@$response['ExpirationMonth']);
            $this->setExpirationYear(@$response['ExpirationYear']);
            $this->setPaymentAccountReferenceNumber(@$response['PaymentAccountReferenceNumber']);
            $this->setPaymentBrand(@$response['PaymentBrand']);
            $this->setPassUpdaterBatchStatus(@$response['PASSUpdaterBatchStatus']);
            $this->setPassUpdaterStatus(@$response['PASSUpdaterStatus']);
        } else {
            $this->setError($response);
        }

        $this->save();

        $this->getQuote()->getPayment()
        ->setEccElementsPaymentAccountId($this->getPaymentAccountId())
        ->setCcExpMonth($this->getExpirationMonth())
        ->setCcExpYear($this->getExpirationYear())
        ->setCcLast4($this->getTruncatedCardNumber())
        ->setCcType($this->getPaymentBrand())
        ->setEccElementsTransactionId($this->getEntityId())
        ->save();

        return $this;
    }

    /**
     * Send Payment Account Delete Request
     * 
     * @return \Epicor\Elements\Model\Elements
     */
    public function paymentAccountDelete()
    {   
        if ($this->getPaymentAccountId()) {
            $response = $this->getApi()->addElement('PaymentAccountParameters', array('PaymentAccountID' => $this->getPaymentAccountId()))->paymentAccountDelete();
        } else {
            if($this->hasError()) {
                $response = $this->getError();
            } else {
                $response = 'No Payment Account Id found';
            }
        }

        if (is_array($response)) {
            $this->setPaymentAccountDeleteExpressResponseCode(@$response['ExpressResponseCode']);
            $this->setPaymentAccountDeleteExpressResponseMessage(@$response['ExpressResponseMessage']);
            $this->setPaymentAccountDeleteServicesID(@$response['ServicesID']);
        } else {
            $this->setError($response);
        }

        $this->save();
        return $this;
    }

    /**
     * Get AVS Results for quote billing address
     * @return \Epicor\Elements\Model\Elements
     */
    public function creditCardAvsOnly()
    {
        $quote = $this->getQuote();
        
        $getStreet = $quote->getBillingAddress()->getStreet();
        $street =  implode($getStreet, ', ');        
        
        $getStreet1 = $quote->getShippingAddress()->getStreet();
        $street1 =  implode($getStreet1, ', ');          

        $response = $this->getApi()
        ->addElement('Transaction', array(
            'ReferenceNumber' => $this->getRef(),
            'TicketNumber' => $this->getRef(),
            'TransactionAmount' => $this->formatPrice($quote->getGrandTotal()),
            'MarketCode' => $this->scopeConfig->getValue('payment/elements/MarketCode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
        ))->addElement('Terminal', array(
            'TerminalID' => $this->scopeConfig->getValue('payment/elements/TerminalID', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'TerminalType' => $this->scopeConfig->getValue('payment/elements/TerminalType', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'CardPresentCode' => $this->scopeConfig->getValue('payment/elements/CardPresentCode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'CardholderPresentCode' => $this->scopeConfig->getValue('payment/elements/CardholderPresentCode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'CardInputCode' => $this->scopeConfig->getValue('payment/elements/CardInputCode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'CVVPresenceCode' => $this->scopeConfig->getValue('payment/elements/CVVEnabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'TerminalCapabilityCode' => $this->scopeConfig->getValue('payment/elements/TerminalCapabilityCode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'TerminalEnvironmentCode' => $this->scopeConfig->getValue('payment/elements/TerminalEnvironmentCode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'MotoECICode' => $this->scopeConfig->getValue('payment/elements/MotoECICode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
        ))->addElement('ExtendedParameters', array(
            'PaymentAccount' => array(
                'PaymentAccountID' => $this->getPaymentAccountId()
            ))
    )
        ->addElement('Address', array(
            'BillingName' => $quote->getBillingAddress()->getName(),
            'BillingAddress1' => $street,
            'BillingCity' => $quote->getBillingAddress()->getCity(),
            'BillingState' => $quote->getBillingAddress()->getRegionCode(),
            'BillingZipcode' => $quote->getBillingAddress()->getPostcode(),
            'BillingEmail' => $quote->getBillingAddress()->getEmail(),
            'BillingPhone' => $quote->getBillingAddress()->getTelephone(),
            'ShippingName' => $quote->getShippingAddress()->getName(),
            'ShippingAddress1' => $street1,
            'ShippingCity' => $quote->getShippingAddress()->getCity(),
            'ShippingState' => $quote->getShippingAddress()->getRegionCode(),
            'ShippingZipcode' => $quote->getShippingAddress()->getPostcode(),
            'ShippingEmail' => $quote->getShippingAddress()->getEmail(),
            'ShippingPhone' => $quote->getShippingAddress()->getTelephone(),
        ))
        ->creditCardAvsOnly();

        if (is_array($response)) {
            $this->setAvsResponseCode(@$response['AVSResponseCode']);
            $this->setCardLogo(@$response['CardLogo']);
            $this->setTransactionId(@$response['TransactionID']);
            $this->setApprovalNumber(@$response['ApprovalNumber']);
            $this->setReferenceNumber(@$response['ReferenceNumber']);
            $this->setAcquirerData(@$response['AcquirerData']);
            $this->setProcessorName(@$response['ProcessorName']);
            $this->setTransactionStatus(@$response['TransactionStatus']);
            $this->setTransactionStatusCode(@$response['TransactionStatusCode']);
            $this->setBillingAddress(@$response['BillingAddress1']);
            $this->setBillingZipcode(@$response['BillingZipcode']);
        } else {
            $this->setError($response);
        }

        $quote->getPayment()
        ->setEccElementsPaymentAccountId($this->getPaymentAccountId())
        ->setEccElementsTransactionId($this->getEntityId())
        ->setCcAvsStatus($this->getAvsResponseCode())
        ->save();

        $this->save();

        return $this;
    }

    /**
     * 
     * @param \Magento\Framework\DataObject $paymentDetails
     * @param \Magento\Sales\Model\Order $order
     * @param string $ref
     * @return \Epicor\Elements\Model\Elements
     */
    public function creditCardAuth($order, $amount)
    {
        if ($order) {
            
            $getStreet = $order->getBillingAddress()->getStreet();
            $OrderBillingStreet =  implode($getStreet, ', '); 
            
            $getStreet1 = $order->getShippingAddress()->getStreet();
            $OrderShippingStreet =  implode($getStreet1, ', ');             
            
            $response = $this->getApi()
            ->addElement('Transaction', array(
                'ReferenceNumber' => $this->getRef(),
                'TicketNumber' => $this->getRef(),
                'TransactionAmount' => $this->formatPrice($amount),
                'MarketCode' => $this->scopeConfig->getValue('payment/elements/MarketCode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ))->addElement('Terminal', array(
                'TerminalID' => $this->scopeConfig->getValue('payment/elements/TerminalID', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                'TerminalType' => $this->scopeConfig->getValue('payment/elements/TerminalType', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                'CardPresentCode' => $this->scopeConfig->getValue('payment/elements/CardPresentCode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                'CardholderPresentCode' => $this->scopeConfig->getValue('payment/elements/CardholderPresentCode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                'CardInputCode' => $this->scopeConfig->getValue('payment/elements/CardInputCode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                'CVVPresenceCode' => $this->scopeConfig->getValue('payment/elements/CVVEnabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                'TerminalCapabilityCode' => $this->scopeConfig->getValue('payment/elements/TerminalCapabilityCode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                'TerminalEnvironmentCode' => $this->scopeConfig->getValue('payment/elements/TerminalEnvironmentCode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                'MotoECICode' => $this->scopeConfig->getValue('payment/elements/MotoECICode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            ))->addElement('ExtendedParameters', array(
                'PaymentAccount' => array(
                    'PaymentAccountID' => $this->getPaymentAccountId()
                ))
        )
            ->addElement('Address', array(
                'BillingName' => $order->getBillingAddress()->getName(),
                'BillingAddress1' => $OrderBillingStreet,
                'BillingCity' => $order->getBillingAddress()->getCity(),
                'BillingState' => $order->getBillingAddress()->getRegionCode(),
                'BillingZipcode' => $order->getBillingAddress()->getPostcode(),
                'BillingEmail' => $order->getBillingAddress()->getEmail(),
                'BillingPhone' => $order->getBillingAddress()->getTelephone(),
                'ShippingName' => $order->getShippingAddress()->getName(),
                'ShippingAddress1' => $OrderShippingStreet,
                'ShippingCity' => $order->getShippingAddress()->getCity(),
                'ShippingState' => $order->getShippingAddress()->getRegionCode(),
                'ShippingZipcode' => $order->getShippingAddress()->getPostcode(),
                'ShippingEmail' => $order->getShippingAddress()->getEmail(),
                'ShippingPhone' => $order->getShippingAddress()->getTelephone(),
            ))
            ->creditCardAuthorization();
        } else {
            $response = 'No Order found';
        }

        if (is_array($response)) {
            $this->setCreditCardAuthExpressResponseCode(@$response['ExpressResponseCode']);
            $this->setCreditCardAuthExpressResponseMessage(@$response['ExpressResponseMessage']);
            $this->setCreditCardAuthHostResponseCode(@$response['HostResponseCode']);
            $this->setCreditCardAuthHostResponseMessage(@$response['HostResponseMessage']);
            $this->setAvsResponseCode(@$response['AVSResponseCode']);
            $this->setCardLogo(@$response['CardLogo']);
            $this->setTransactionId(@$response['TransactionID']);
            $this->setApprovalNumber(@$response['ApprovalNumber']);
            $this->setReferenceNumber(@$response['ReferenceNumber']);
            $this->setAcquirerData(@$response['AcquirerData']);
            $this->setProcessorName(@$response['ProcessorName']);
            $this->setTransactionStatus(@$response['TransactionStatus']);
            $this->setTransactionStatusCode(@$response['TransactionStatusCode']);
            $this->setApprovedAmount(@$response['ApprovedAmount']);
            $this->setBillingAddress(@$response['BillingAddress1']);
            $this->setBillingZipcode(@$response['BillingZipcode']);
        } else {
            $this->setError($response);
        }

        $order->getPayment()
        ->setEccElementsPaymentAccountId($this->getPaymentAccountId())
        ->setEccElementsProcessorId($this->scopeConfig->getValue('payment/elements/AccountID', \Magento\Store\Model\ScopeInterface::SCOPE_STORE))
        ->setCcExpMonth($this->getExpirationMonth())
        ->setCcExpYear($this->getExpirationYear())
        ->setCcLast4($this->getTruncatedCardNumber())
        ->setCcType($this->getPaymentBrand())
        ->setCcTransId($this->getTransactionId())
        ->setEccCcAuthCode($this->getApprovalNumber())
        ->setEccElementsTransactionId($this->getEntityId())
        ->setCcAvsStatus($this->getAvsResponseCode())
        ->setEccCcCvvStatus($this->getCvvResponseCode())->save();


        $this->setOrder($order)->save();
        return $this;
    }

    /** There is no need for any authorization  if cvv was enabled
     * 
     * @param \Magento\Framework\DataObject $paymentDetails
     * @param \Magento\Sales\Model\Order $order
     * @param string $ref
     * @return \Epicor\Elements\Model\Elements
     */
    public function creditCardCvvNoAuth($order, $amount)
    {
        if ($order) {
            $response = array("cvvenabled" => true);
        } else {
            $response = 'No Order found';
        }
        $elementsCode = $this->getHostedExpressResponseCode();
        $this->setCreditCardAuthExpressResponseCode($elementsCode);
        if (is_array($response)) {
            
            $order->getPayment()
                    ->setEccElementsPaymentAccountId($this->getPaymentAccountId())
                    ->setEccElementsProcessorId($this->scopeConfig->getValue('payment/elements/AccountID', \Magento\Store\Model\ScopeInterface::SCOPE_STORE))
                    ->setCcExpMonth($this->getExpirationMonth())
                    ->setCcExpYear($this->getExpirationYear())
                    ->setCcLast4($this->getTruncatedCardNumber())
                    ->setCcType($this->getPaymentBrand())
                    ->setCcTransId($this->getTransactionId())
                    ->setEccCcAuthCode($this->getApprovalNumber())
                    ->setEccElementsTransactionId($this->getId())
                    ->setCcAvsStatus($this->getAvsResponseCode())
                    ->setEccCcCvvStatus($this->getCvvResponseCode())->save();

            $this->setOrder($order)->save();
        } else {
            $this->setError($response);
        }
        return $this;
    }

    /**
     * Has the Elements Credit Card Auth Response returned a success result
     * 
     * @return bool
     */
    public function successfulCreditCardAuth()
    {
        $error = $this->getError();
        return $this->getCreditCardAuthExpressResponseCode() === '0' &&
        empty($error);
    }

    /**
     * Has the Elements Hosted Card Capture Response returned a success result
     * 
     * @return bool
     */
    public function successfulHostedResponse()
    {
        $error = $this->getError();
        $success = $this->getHostedPaymentStatus() == 'Complete' &&
        $this->getHostedExpressResponseCode() === '0' &&
        $this->getHostedValidationCode() === $this->getTransactionValidationCode() &&
        empty($error);

        return $success;
    }

    public function validateCvvResponse()
    {
        if (!$this->cvvValidation->validate($this->getCvvResponseCode())) {
            $this->setError($this->cvvValidation->getError());
            return false;
        }
        return true;
    }

    public function validateAvsResponse()
    {
        if (!$this->avsValidation->validate($this->getAvsResponseCode())) {
            $this->setError($this->avsValidation->getError());
            return false;
        }
        return true;
    }

    public function isCardExpiryValid()
    {
        return (time() < $this->getExpiryDate());
    }

    public function hasCardExpired()
    {
        return (time() >= $this->getExpiryDate());
    }

    public function getExpiryDate()
    {
        $expires_str = $this->getExpirationYear() . '-' . $this->getExpirationMonth() . '-01';
        return strtotime($expires_str);
    }

    /**
     * Load the related Order
     * 
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if (!$this->_order) {
            $this->_order = $this->salesOrderFactory->create()->load($this->getOrderId());
        }

        return $this->_order;
    }

    /**
     * Load the related Quote
     * 
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        if (!$this->_quote) {
            $this->_quote = $this->checkoutSession->getQuote();
        }

        return $this->_quote;
    }

    /**
     * Set Quote
     * 
     * @param \Magento\Quote\Model\Quote $quote
     * @return \Epicor\Elements\Model\Elements
     */
    public function setQuote($quote)
    {
        if (!$quote->isObjectNew()) {
            $this->_quote = $quote;
            $this->setQuoteId($quote->getId());
        }
        return $this;
    }

    /**
     * Set Quote
     * 
     * @param \Magento\Sales\Model\Order $order
     * @return \Epicor\Elements\Model\Elements
     */
    public function setOrder($order)
    {
        if ($order) {
            $this->_order = $order;
            $orderId = $order->getIncrementId();
            $this->setOrderId($orderId);
        }
        return $this;
    }

}
