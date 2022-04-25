<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Cre\Model\Payment;

use Epicor\Cre\Helper\CreData;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObjectFactory;
use \Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger;

class Cre extends AbstractMethod
{
    const METHOD_CODE = 'cre';
    protected $_code = self::METHOD_CODE;
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

    /**
     * @var \Epicor\Elements\Model\TransactionFactory
     */
    protected $elementsTransactionFactory;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /*
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * Cre constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Data $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param DataObjectFactory $dataObjectFactory
     * @param Logger $logger
     * @param Http $request
     * @param CheckoutSession $_checkoutSession
     * @param CustomerSession $customerSession
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        DataObjectFactory $dataObjectFactory,
        Logger $logger,
        Http $request,
        CheckoutSession $_checkoutSession,
        CustomerSession $customerSession,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->request = $request;
        $this->customerSession = $customerSession;
        $this->_checkoutSession = $_checkoutSession;
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

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return parent::isAvailable($quote);
    }

    /**
     * this method is called if we are just authorising
     * a transaction
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $this->addTransaction($payment);
        return $this;
    }

    /**
     * Add a transaction to an order
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @param \Epicor\Elements\Model\Transaction $elements
     */
    public function addTransaction($payment)
    {
        $creTransactionId = $this->_checkoutSession->getQuote()->getPayment();
        $payment->setTransactionAdditionalInfo(\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS, array(
            'test' => 'test 1234',
            'test 2' => 'Jimbo'
        ));
        $payment->setIsTransactionClosed(false);
        $payment->setShouldCloseParentTransaction(false);
        $payment->setParentTransactionId(null);
        $payment->setTransactionId($creTransactionId->getCreTransactionId());
    }

    /**
     * Validate payment method information object
     *
     * @return $this
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validate()
    {
        $info = $this->getInfoInstance();
        $availableTypes = explode(',', $this->getConfigData('cctypes'));
        $ccNumber = $info->getCcNumber();
        // remove credit card number delimiters such as "-" and space
        $ccNumber = preg_replace('/[\-\s]+/', '', $ccNumber);
        $info->setCcNumber($ccNumber);
        $ccType = $info->getCcType();
        $eccCardMapWithCre = CreData::ECC_CRE_CARD_TYPE_MAP;
        if (isset($eccCardMapWithCre[$ccType])) {
            $info->setCcType($eccCardMapWithCre[$ccType]);
            $ccType = $info->getCcType();
        }

        if ((is_null($ccType)) || (in_array($ccType, $availableTypes))) {
            $errorMsg = null;
        } else {
            $errorMsg = __('This credit card type is not allowed for this payment method or invalid mapping.');
        }

        //validate credit card verification number
        if ($errorMsg === false && $this->hasVerification()) {
            $verifcationRegEx = $this->getVerificationRegEx();
            $regExp = isset($verifcationRegEx[$ccType]) ? $verifcationRegEx[$ccType] : '';
            if (!$info->getCcCid() || !$regExp || !preg_match($regExp, $info->getCcCid())) {
                $errorMsg = __('Please enter a valid credit card verification number.');
            }
        }

        if ($errorMsg) {
            $info->setEccIsSaved(0)->save();
            throw new LocalizedException($errorMsg);
        }
        return $this;
    }
}
