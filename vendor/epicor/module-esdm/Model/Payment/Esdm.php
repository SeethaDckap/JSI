<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Esdm\Model\Payment;

use \Magento\Framework\DataObject;
use \Magento\Framework\Exception\LocalizedException;

class Esdm extends \Magento\Payment\Model\Method\Cc
{

    const METHOD_CODE = 'esdm';

    protected $_code = self::METHOD_CODE;
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
    private $esdm;


    private $_encryptor;


    protected $tokenapi ;

    private $token;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    protected $tokenFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Epicor\Esdm\Model\TokenApiFactory $tokenapi,
        \Epicor\Esdm\Model\TokenFactory $tokenFactory,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
        $this->tokenapi = $tokenapi;
        $this->tokenFactory = $tokenFactory;
        $this->_encryptor = $encryptor;
    }

    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        return parent::isAvailable($quote);
    }

    /**
     * Sets up the token
     *
     * @param \Magento\Framework\DataObject $payment
     * @param integer $tokenId
     *
     * @return Epicor_Esdm_Model_Token
     */
    public function requestToken($payment, $tokenId = null)
    {
        if (!$this->token) {
            $api = $this->tokenapi->create();
            /* @var $api Epicor_Esdm_Model_TokenApi */

            $token = $this->tokenFactory->create();
            /* @var $token Epicor_Esdm_Model_Token */
            if ($tokenId) {
                $this->token = $token->load($tokenId);
                $cvvToken = $api->cvvTokenRequest($payment->getCcCid());
                if (!empty($cvvToken) && strlen($cvvToken) == 54) {
                    $this->token->setCvvToken($cvvToken);
                    $this->token->setSuccess(true);
                } else {
                    $this->token->setSuccess(false);
                }
            } else {
                $ccvToken = $api->ccvTokenRequest($payment->getCcNumber());
                $cvvToken = $api->cvvTokenRequest($payment->getCcCid());
                $this->token = $token->createToken($ccvToken, $cvvToken, $payment);
            }
        }

        return $this->token;
    }

    /**
     * Validate payment method information object
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validate()
    {
        $info = $this->getInfoInstance();
        $errorMsg = false;
        $availableTypes = explode(',', $this->getConfigData('cctypes'));
        $ccNumber = $info->getCcNumber();
        // remove credit card number delimiters such as "-" and space
        $ccNumber = preg_replace('/[\-\s]+/', '', $ccNumber);
        $info->setCcNumber($ccNumber);
        $ccType = '';
        if (in_array($info->getCcType(), $availableTypes)) {
            $errorMsg = '';
        } else {
            $errorMsg = __('This credit card type is not allowed for this payment method.');
        }

        //validate credit card verification number
        if ($errorMsg === false && $this->hasVerification()) {
            $verifcationRegEx = $this->getVerificationRegEx();
            $regExp = isset($verifcationRegEx[$info->getCcType()]) ? $verifcationRegEx[$info->getCcType()] : '';
            if (!$info->getCcCid() || !$regExp || !preg_match($regExp, $info->getCcCid())) {
                $errorMsg = __('Please enter a valid credit card verification number.');
            }
        }

        if ($ccType != 'SS' && !$this->_validateExpDate($info->getCcExpYear(), $info->getCcExpMonth())) {
            $errorMsg = __('Please enter a valid credit card expiration date.');
        }

        if ($errorMsg) {
            $info->setEccIsSaved(0)->save();
            throw new LocalizedException($errorMsg);
        }

        return $this;
    }

    public function otherCcType($type)
    {
        return true;
    }



    /**
     * Authorize payment
     *
     * @param InfoInterface|Payment|Object $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\State\InvalidTransitionException
     */
    public function authorize(\Magento\Payment\Model\InfoInterface  $payment, $amount)
    {
        $this->addTransaction($payment);

        return $this;
    }


    /**
     * Add a transaction to an order
     *
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @param \Magento\Framework\DataObject $response
     * @param string $type
     * @param bool $close
     * @param string $parent_txn_id
     * @param bool $close_parent
     */
    public function addTransaction($payment)
    {
        $payment->setTransactionAdditionalInfo(
            \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS, array(
            'CCV Code' => $payment->getCcvCode(),
            'CVV Code' => $payment->getCvvCode(),
        ));
        $payment->setIsTransactionClosed(false);
        $payment->setShouldCloseParentTransaction(false);
        $payment->setParentTransactionId(null);
    }

}