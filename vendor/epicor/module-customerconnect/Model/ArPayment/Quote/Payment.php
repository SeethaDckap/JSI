<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\ArPayment\Quote;

use Epicor\Customerconnect\Api\Data\PaymentInterface;

/**
 * Quote payment information
 *
 * @api
 * @method int getQuoteId()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Payment setQuoteId(int $value)
 * @method string getCreatedAt()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Payment setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Payment setUpdatedAt(string $value)
 * @method string getCcNumberEnc()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Payment setCcNumberEnc(string $value)
 * @method string getCcLast4()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Payment setCcLast4(string $value)
 * @method string getCcCidEnc()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Payment setCcCidEnc(string $value)
 * @method string getCcSsOwner()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Payment setCcSsOwner(string $value)
 * @method int getCcSsStartMonth()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Payment setCcSsStartMonth(int $value)
 * @method int getCcSsStartYear()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Payment setCcSsStartYear(int $value)
 * @method string getCcSsIssue()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Payment setCcSsIssue(string $value)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Payment extends \Magento\Payment\Model\Info implements PaymentInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'ar_sales_quote_payment';

    /**
     * @var string
     */
    protected $_eventObject = 'payment';

    /**
     * Quote model object
     *
     * @var \Epicor\Customerconnect\Model\ArPayment\Quote
     */
    protected $_quote;

    /**
     * @var \Magento\Payment\Model\Checks\SpecificationFactory
     */
    protected $methodSpecificationFactory;

    /**
     * @var array
     */
    private $additionalChecks;

    /**
     * @var \Epicor\Comm\Model\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @var \Epicor\Comm\Model\Serialize\Serializer\JsonValidator
     */
    private $jsonValidator;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Payment\Model\Checks\SpecificationFactory $methodSpecificationFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param array $additionalChecks
     * @param \Epicor\Comm\Model\Serialize\Serializer\Json|null $serializer
     * @param \Epicor\Comm\Model\Serialize\Serializer\JsonValidator|null $jsonValidator
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Payment\Model\Checks\SpecificationFactory $methodSpecificationFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        array $additionalChecks = [],
        \Epicor\Comm\Model\Serialize\Serializer\Json $serializer = null,
        \Epicor\Comm\Model\Serialize\Serializer\JsonValidator $jsonValidator = null
    ) {
        $this->methodSpecificationFactory = $methodSpecificationFactory;
        $this->additionalChecks = $additionalChecks;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Epicor\Comm\Model\Serialize\Serializer\Json::class);
        $this->jsonValidator = $jsonValidator ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Epicor\Comm\Model\Serialize\Serializer\JsonValidator::class);
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $encryptor,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Epicor\Customerconnect\Model\ArPayment\ResourceModel\Quote\Payment::class);
    }

    /**
     * Declare quote model instance
     *
     * @param \Epicor\Customerconnect\Model\ArPayment\Quote $quote
     * @return $this
     */
    public function setQuote(\Epicor\Customerconnect\Model\ArPayment\Quote $quote)
    {
        $this->_quote = $quote;
        $this->setQuoteId($quote->getId());
        return $this;
    }

    /**
     * Retrieve quote model instance
     *
     * @codeCoverageIgnore
     *
     * @return \Epicor\Customerconnect\Model\ArPayment\Quote
     */
    public function getQuote()
    {
        return $this->_quote;
    }

    /**
     * Import data array to payment method object,
     * Method calls quote totals collect because payment method availability
     * can be related to quote totals
     *
     * @param array $data
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function importData(array $data)
    {
        $data = $this->convertPaymentData($data);
        $data = new \Magento\Framework\DataObject($data);
        $this->setMethod($data->getMethod());
        $method = $this->getMethodInstance();
        $quote = $this->getQuote();

        /**
         * Payment availability related with quote totals.
         * We have to recollect quote totals before checking
         */
        $quote->collectTotals();


        //$method->assignData($data);

        /*
         * validating the payment data
         */
        //$method->validate();
        return $this;
    }

    /**
     * Converts request to payment data
     *
     * @param array $rawData
     * @return array
     */
    private function convertPaymentData(array $rawData)
    {
        $paymentData = [
            PaymentInterface::KEY_METHOD => null,
            PaymentInterface::KEY_PO_NUMBER => null,
            PaymentInterface::KEY_ADDITIONAL_DATA => [],
            'checks' => []
        ];

        foreach (array_keys($rawData) as $requestKey) {
            if (!array_key_exists($requestKey, $paymentData)) {
                $paymentData[PaymentInterface::KEY_ADDITIONAL_DATA][$requestKey] = $rawData[$requestKey];
            } elseif ($requestKey === PaymentInterface::KEY_ADDITIONAL_DATA) {
                $paymentData[PaymentInterface::KEY_ADDITIONAL_DATA] = array_merge(
                    $paymentData[PaymentInterface::KEY_ADDITIONAL_DATA],
                    (array) $rawData[$requestKey]
                );
            } else {
                $paymentData[$requestKey] = $rawData[$requestKey];
            }
        }

        return $paymentData;
    }

    /**
     * Prepare object for save
     *
     * @return $this
     */
    public function beforeSave()
    {
        if ($this->getQuote()) {
            $this->setQuoteId($this->getQuote()->getId());
        }
        return parent::beforeSave();
    }

    /**
     * Checkout redirect URL getter
     *
     * @return string
     */
    public function getCheckoutRedirectUrl()
    {
        $method = $this->getMethodInstance();
        if ($method) {
            return $method->getCheckoutRedirectUrl();
        }
        return '';
    }

    /**
     * Checkout order place redirect URL getter
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        $method = $this->getMethodInstance();
        if ($method) {
            return $method->getConfigData('order_place_redirect_url');
        }
        return '';
    }

    /**
     * Retrieve payment method model object
     *
     * @return \Magento\Payment\Model\MethodInterface
     */
    public function getMethodInstance()
    {
        $method = parent::getMethodInstance();
        $method->setStore($this->getQuote()->getStoreId());
        return $method;
    }

    /**
     * @codeCoverageIgnoreStart
     */

    /**
     * Get purchase order number
     *
     * @return string|null
     */
    public function getPoNumber()
    {
        return $this->getData(self::KEY_PO_NUMBER);
    }

    /**
     * Set purchase order number
     *
     * @param string $poNumber
     * @return $this
     */
    public function setPoNumber($poNumber)
    {
        return $this->setData(self::KEY_PO_NUMBER, $poNumber);
    }

    /**
     * Get payment method code
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->getData(self::KEY_METHOD);
    }

    /**
     * Set payment method code
     *
     * @param string $method
     * @return $this
     */
    public function setMethod($method)
    {
        return $this->setData(self::KEY_METHOD, $method);
    }

    /**
     * Get payment additional details
     *
     * @return string[]|null
     */
    public function getAdditionalData()
    {
        $additionalDataValue = $this->getData(self::KEY_ADDITIONAL_DATA);
        if (is_array($additionalDataValue)) {
            return $additionalDataValue;
        }
        if (is_string($additionalDataValue) && $this->jsonValidator->isValid($additionalDataValue)) {
            $additionalData = $this->serializer->unserialize($additionalDataValue);
            if (is_array($additionalData)) {
                return $additionalData;
            }
        }
        return null;
    }

    /**
     * Set payment additional details
     *
     * @param string $additionalData
     * @return $this
     */
    public function setAdditionalData($additionalData)
    {
        return $this->setData(self::KEY_ADDITIONAL_DATA, $additionalData);
    }

   
}
