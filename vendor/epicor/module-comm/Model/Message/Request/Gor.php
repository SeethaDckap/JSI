<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message\Request;

use Epicor\Comm\Helper\BsvAndGor;
use Epicor\Comm\Model\Context;
use Epicor\Comm\Model\Message\Request;
use \Epicor\Comm\Model\RepriceFlag as RepriceFlag;
use Epicor\Common\Helper\Data;
use Epicor\Elements\Model\TransactionFactory;
use Epicor\Quotes\Model\ResourceModel\Quote as EccQuote;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Tax\Model\ClassModelFactory;

/**
 * Request GOR - Generate Order
 * Create an order on the ERP
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Gor extends Request
{

    const GOR_STATUS_NOT_SENT = 0;
    const GOR_STATUS_SENT = 1;
    const GOR_STATUS_ERROR = 3;
    const GOR_STATUS_ERROR_RETRY = 4;
    const GOR_FLOW_PROCESSING = 1;
    const GOR_FLOW_SUCCESS = 2;
    const GOR_FLOW_FAILED = 3;
    const GOR_STATUS_NEVER_SEND = 5;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var ClassModelFactory
     */
    protected $taxClassModelFactory;

    /**
     * @var \Epicor\Verifone\Model\TransactionFactory
     */
    //protected $verifoneTransactionFactory;

    /**
     * @var OrderFactory
     */
    protected $salesOrderFactory;

    /**
     * @var AstFactory
     */
    protected $commMessageRequestAstFactory;

    /**
     * @var CollectionFactory
     */
    protected $salesResourceModelOrderCollectionFactory;
    /**
     * @var TransactionFactory
     */
    private $elementsTransactionFactory;

    /** @var BsvAndGor  */
    private $bsvAndGor;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var Data
     */
    protected $commonHelper;

    /**
     * @var EccQuote|null
     */
    private $eccQuote;

    /**
     * Data object factory.
     *
     * @var DataObjectFactory
     */
    private $dataObjectFactory;


    /**
     * Construct object and set message type.
     * @param Context $context
     * @param CustomerFactory $customerCustomerFactory
     * @param ClassModelFactory $taxClassModelFactory
     * @param OrderFactory $salesOrderFactory
     * @param AstFactory $commMessageRequestAstFactory
     * @param CollectionFactory $salesResourceModelOrderCollectionFactory
     * @param TransactionFactory $elementsTransactionFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param ManagerInterface $messageManager
     * @param Data $commonHelper
     * @param BsvAndGor|null $bsvAndGor
     * @param EccQuote|null $eccQuote
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerFactory $customerCustomerFactory,
        ClassModelFactory $taxClassModelFactory,
        OrderFactory $salesOrderFactory,
        AstFactory $commMessageRequestAstFactory,
        CollectionFactory $salesResourceModelOrderCollectionFactory,
        TransactionFactory $elementsTransactionFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        ManagerInterface $messageManager,
        Data $commonHelper,
        BsvAndGor $bsvAndGor = null,
        EccQuote $eccQuote = null,
        array $data = []
    ){
        $this->registry = $context->getRegistry();
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->taxClassModelFactory = $taxClassModelFactory;
        $this->salesOrderFactory = $salesOrderFactory;
        $this->commMessageRequestAstFactory = $commMessageRequestAstFactory;
        $this->salesResourceModelOrderCollectionFactory = $salesResourceModelOrderCollectionFactory;
        $this->elementsTransactionFactory = $elementsTransactionFactory;
        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->setMessageType('GOR');
        $this->setLicenseType(array('Consumer', 'Customer'));
        $this->setMessageCategory(self::MESSAGE_CATEGORY_ORDER);
        $this->setConfigBase('epicor_comm_enabled_messages/gor_request/');
        $this->bsvAndGor = $bsvAndGor;
        $this->messageManager = $messageManager;
        $this->commonHelper = $commonHelper;
        $this->dataObjectFactory = $this->commonHelper->getDataObjectFactory();
        $this->eccQuote = $eccQuote ?: ObjectManager::getInstance()->get(EccQuote::class);
    }
    /**
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     *
     * @param \Epicor\Comm\Model\Order $order
     */
    public function setOrder($order)
    {
        $this->_order = $order;

        $this->setStoreId($order->getStoreId());

        $reference = $this->scopeConfig->getValue('epicor_comm_enabled_messages/gor_request/gor_order_prefix', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());
        $reference .= $this->_order->getIncrementId();

        $this->setMessageSecondarySubject('Order Reference: ' . $reference
            . "\n" . 'Basket Quote ID: ' . $order->getQuoteId()
            . "\n" . 'ERP Quote ID: ' . $order->getEccBasketErpQuoteNumber());

        return $this;
    }
    /**
     *
     * Get Delivery Notes
     *
     * @param \Magento\Sales\Model\Order\Address $address
     * @return string
     */
    public function getCarriageText($address = null)
    {
        $carriageText = $address ? $address->getEccInstructions() : '';
        $return = $this->getCustomerNote() ?: $carriageText;
        if(!$this->checkP21ErpSystem() && $this->getOrderCommentIncludedIn() =="E") {
            return $return;
        }
        if(!in_array($this->getOrderCommentIncludedIn(), array('C','B','E'))) {
            $return ='' ;
        }
        return $return;
    }
    /**
     * Get Order Note
     *
     * @return string
     */
    public function getOrderNote()
    {
        $return = $this->getCustomerNote();
        if(!$this->checkP21ErpSystem() && $this->getOrderCommentIncludedIn() =="E") {
            return $return;
        }
        if(!in_array($this->getOrderCommentIncludedIn(), array('O','B','E'))) {
            $return ='';
        }
        return $return;
    }

    public function getOrderCommentIncludedIn()
    {
        return $this->scopeConfig->getValue('checkout/options/order_comments_included_in', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public  function checkP21ErpSystem()
    {
        return $this->scopeConfig->getValue('Epicor_Comm/licensing/erp', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 'p21';
    }

    /**
     * Limits the customer comment to the predefined value.
     */
    private function getCustomerNote()
    {
        $note = $this->_order->getCustomerNote();
        if ($this->scopeConfig->isSetFlag('checkout/options/limit_comment_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId())) {
            $value = $this->scopeConfig->getValue('checkout/options/max_comment_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());
            if (is_int($value)) {
                $note = substr($note, 0, $value);
            }
        }
        return $note;
    }

    /**
     * @return \Magento\Customer\Model\Address
     */
    private function getCustomerDefaultShippingAddress()
    {
        return $this->bsvAndGor
            ->getCustomerDefaultShippingAddress($this->customerSession, $this->getOrderAddresses());
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderAddressInterface[]
     */
    private function getOrderAddresses()
    {
        if ($this->_order instanceof \Epicor\Comm\Model\Order) {
            return $this->_order->getAddresses();
        }
    }

    /**
     * @return \Magento\Sales\Model\Order\Address
     */
    private function getCustomerShippingAddress()
    {
        return $this->_order->getShippingAddress() ?: $this->getCustomerDefaultShippingAddress();
    }

    /**
     * Create a request
     *
     * @param array $data
     * @return
     */
    public function buildRequest()
    {
        $helper = $this->commMessagingHelper->create();
        /* @var $helper \Epicor\Comm\Helper\Messaging */
        $session = $this->genericFactory->create();

        if (!$this->_order) {
            return 'Missing Order';
        }

        $erpAccountId = $this->_order->getEccErpAccountId() ?: $this->getCustomerGroupId();
        $erpAccount = $helper->getErpAccountInfo($erpAccountId);
        /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount */
        $fullAccountNumber = $erpAccount->getErpCode();
        $accountNumber = $erpAccount->getAccountNumber();

        if (!$accountNumber) {
            return 'Missing account number';
        } else {

            if($this->_order->getArpaymentsQuote()) {
                return false;
            }

            $defaultAddressCode = $this->scopeConfig->getValue('epicor_comm_enabled_messages/global_request/default_address_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());

            $shippingAddress = $this->getCustomerShippingAddress();

            $billingAddress = ($this->_order->getBillingAddress()) ?: $this->_order->getShippingAddress();
            /* @var $billingAddress \Magento\Sales\Model\Order\Address */

            $customer = $this->customerCustomerFactory->create()->load($this->_order->getCustomerId());
            /* @var $customer \Magento\Customer\Model\Customer */

            if ($customer->isObjectNew()) {
                $customer->setPrefix($billingAddress->getPrefix())
                    ->setFirstname($billingAddress->getFirstname())
                    ->setMiddlename($billingAddress->getMiddlename())
                    ->setLastname($billingAddress->getLastname())
                    ->setSuffix($billingAddress->getSuffix())
                    ->setTelephone($billingAddress->getTelephone())
                    ->setEccMobileNumber($billingAddress->getEccMobileNumber())
                    ->setFax($billingAddress->getFax())
                    ->setEmail($billingAddress->getEmail());
            }

            $customer->getEccMobileNumber() ? null : $customer->setEccMobileNumber($billingAddress->getEccMobileNumber());  // if no customer mobile number, assign billing mobile number
            //get delivery details
            $delivery_method = $this->getHelper()->getShippingMethodMapping($this->_order->getShippingMethod());
            $default_shipping_days = $this->scopeConfig->getValue('epicor_comm_enabled_messages/gor_request/daystoship', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());
            $dateRequired = ($this->_order->getEccRequiredDate() != '1970-01-01' && $this->_order->getEccRequiredDate() != '0000-00-00') ? $helper->getLocalDate(strtotime($this->_order->getEccRequiredDate()), \IntlDateFormatter::LONG) : $helper->getLocalDate(strtotime(date('Y-m-d', strtotime(" +" . $default_shipping_days . " day"))), \IntlDateFormatter::LONG);
            //get payment details
            $payment = $helper->getPaymentMethodMap($this->_order->getPayment()->getMethod());
            $paymentMethod = $payment->getErpCode();
            $paymentCollected = $payment->getPaymentCollected();
            //get order details
            $reference = $this->scopeConfig->getValue('epicor_comm_enabled_messages/gor_request/gor_order_prefix', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());
            $reference .= $this->_order->getIncrementId();
            $customer_order_ref = $this->_order->getEccCustomerOrderRef() ?: $reference;
            //set email for logging purposes
            $this->setEmail($customer->getEmail());
            $taxExemptReference=$this->commHelper->isTaxExemptionAllowed($this->_order->getEccErpAccountId());

            // get order reprice flag
            $prevent_reprice = $helper->getOrderRepriceValue($this->_order, $paymentMethod, 'GOR');

            $data = $this->getMessageTemplate();

            if ($customer->isSalesRep() && $this->commHelper->isMasquerading() && $this->_order->getEccSalesrepChosenCustomerId()) {
                if ($this->_order->getCustomerId() != $this->_order->getEccSalesrepChosenCustomer()) {
                    $customerId = $this->_order->getEccSalesrepChosenCustomerId();

                    $salesRepCustomer = $this->customerCustomerFactory->create()->load($customerId);
                    $customer = $salesRepCustomer;
                }
            }
            $shipWholeOrder =  $this->scopeConfig->getValue('epicor_comm_enabled_messages/global_request/default_packing_basis', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());
            if ($this->_order->getPayment()->getLastTransId()) {
                $this->_order->getPayment()->setCcTransId($this->_order->getPayment()->getLastTransId()) ;
            }
            $pos = strpos($this->_order->getPayment()->getMethod(), 'braintree');
            if ($pos !== false) {
                $orderInfos = $this->_order->getPayment()->getAdditionalInformation();
                $authorizationCode = isset($orderInfos['processorAuthorizationCode']) ? $orderInfos['processorAuthorizationCode']: '';
                $this->_order->getPayment()->setEccCcAuthCode($authorizationCode) ;
            }
            $data['messages']['request']['body'] = array_merge($data['messages']['request']['body'], array(
                'accountNumber' => $accountNumber,
                'currencyCode' => $this->getHelper()->getCurrencyMapping($this->_order->getStoreCurrencyCode()),
                'languageCode' => $this->getHelper()->getLanguageMapping($this->scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId())),
                'orderBy' => array(
                    'contactCode' => $customer->getEccContactCode(),
                    'name' => $customer->getName(),
                    'function' => $customer->getEccFunction(),
                    'telephoneNumber' => $customer->getTelephone(),
                    'mobileNumber' => $customer->getEccMobileNumber(),
                    'faxNumber' => $customer->getFax(),
                    'emailAddress' => $customer->getEmail(),
                    'eccLoginId' => $customer->getId()
                ),
                'orderFor' => array(
                    'contactName' => $helper->stripNonPrintableChars($shippingAddress->getName()),
                    'addressCode' => $helper->getAddressCode($shippingAddress),
                    'name' => $helper->stripNonPrintableChars($shippingAddress->getCompany()),
                    'address1' => $helper->stripNonPrintableChars($shippingAddress->getStreetLine(1)),
                    'address2' => $helper->stripNonPrintableChars($shippingAddress->getStreetLine(2)),
                    'address3' => $helper->stripNonPrintableChars($shippingAddress->getStreetLine(3)),
                    'city' => $helper->stripNonPrintableChars($shippingAddress->getCity()),
                    'county' => $helper->stripNonPrintableChars($helper->getRegionNameOrCode($shippingAddress->getCountry_id(), ($shippingAddress->getRegionId() ? $shippingAddress->getRegionId() : $shippingAddress->getRegion()))),
                    'country' => $helper->getErpCountryCode($shippingAddress->getCountry_id()),
                    'postcode' => $helper->stripNonPrintableChars($shippingAddress->getPostcode()),
                    'telephoneNumber' => $helper->stripNonPrintableChars($shippingAddress->getTelephone()),
                    'mobileNumber' => $helper->stripNonPrintableChars($shippingAddress->getEccMobileNumber()),
                    'faxNumber' => $helper->stripNonPrintableChars($shippingAddress->getFax()),
                    'emailAddress' => $helper->stripNonPrintableChars(($shippingAddress->getEmail() ? $shippingAddress->getEmail() : ($customer->getEmail() ? $customer->getEmail() : $billingAddress->getEmail()))),
                ),
                'delivery' => array(
                    '_attributes' => array(
                        'shipWholeOrder' => $shipWholeOrder ? 'D' : 'N',
                    ),
                    'deliveryAddress' => array(
                        'addressCode' => $helper->getAddressCode($shippingAddress),
                        'contactName' => $helper->stripNonPrintableChars($shippingAddress->getName()),
                        'name' => $helper->stripNonPrintableChars($shippingAddress->getCompany()),
                        'address1' => $helper->stripNonPrintableChars($shippingAddress->getStreetLine(1)),
                        'address2' => $helper->stripNonPrintableChars($shippingAddress->getStreetLine(2)),
                        'address3' => $helper->stripNonPrintableChars($shippingAddress->getStreetLine(3)),
                        'city' => $helper->stripNonPrintableChars($shippingAddress->getCity()),
                        'county' => $helper->stripNonPrintableChars($helper->getRegionNameOrCode($shippingAddress->getCountry_id(), ($shippingAddress->getRegionId() ? $shippingAddress->getRegionId() : $shippingAddress->getRegion()))),
                        'country' => $helper->getErpCountryCode($shippingAddress->getCountry_id()),
                        'postcode' => $helper->stripNonPrintableChars($shippingAddress->getPostcode()),
                        'emailAddress' => $helper->stripNonPrintableChars(($shippingAddress->getEmail() ? $shippingAddress->getEmail() : ($customer->getEmail() ? $customer->getEmail() : $billingAddress->getEmail()))),
                        'telephoneNumber' => $helper->stripNonPrintableChars($shippingAddress->getTelephone()),
                        'mobileNumber' => $helper->stripNonPrintableChars($shippingAddress->getEccMobileNumber()),
                        'faxNumber' => $helper->stripNonPrintableChars($shippingAddress->getFax()),
                        'carriageText' => $this->getCarriageText(),
                    ),
                    'charge' => $this->_order->getBaseShippingAmount(),
                    'methodCode' => $delivery_method
                ),
                'payment' => array(
                    'paymentMethod' => $paymentMethod,
                    'creditCard' => array(
                        '_attributes' => array(
                            'collected' => $paymentCollected,
                        ),
                        'type' => $helper->getCardTypeMapping($this->_order->getPayment()->getMethod(), $this->_order->getPayment()->getCcType()),
                        'last4' => '**** **** **** ' . $this->_order->getPayment()->getCcLast4(),
                        'issueNumber' => $this->_order->getPayment()->getCcSsIssue(),
                        'startDate' => $this->_formatCcDates($this->_order->getPayment()->getCcSsStartMonth(), $this->_order->getPayment()->getCcSsStartYear()),
                        'expiryDate' => $this->_formatCcDates($this->_order->getPayment()->getCcExpMonth(), $this->_order->getPayment()->getCcExpYear()),
                        'cardToken' => $this->_order->getPayment()->getEccCcvToken(),
                        'cv2Token' => $this->_order->getPayment()->getEccCvvToken(),
                        'authorizationCode' => $this->_order->getPayment()->getEccCcAuthCode(),
                        'transactionId' => $this->_order->getPayment()->getCcTransId(),
                        'siteUrl' => $this->_order->getPayment()->getEccSiteUrl(),
                        'isSavedToken' => ($this->_order->getPayment()->getEccIsSaved() == 1) ? "Y" : "N",
                        'erpProcessing' => $this->getErpProcessing()
                    )
                ),
                'invoiceAddress' => array(
                    'addressCode' => $helper->getAddressCode($billingAddress),
                    'contactName' => $helper->stripNonPrintableChars($billingAddress->getName()),
                    'name' => $helper->stripNonPrintableChars($billingAddress->getCompany()),
                    'address1' => $helper->stripNonPrintableChars($billingAddress->getStreetLine(1)),
                    'address2' => $helper->stripNonPrintableChars($billingAddress->getStreetLine(2)),
                    'address3' => $helper->stripNonPrintableChars($billingAddress->getStreetLine(3)),
                    'city' => $helper->stripNonPrintableChars($billingAddress->getCity()),
                    'county' => $helper->stripNonPrintableChars($helper->getRegionNameOrCode($billingAddress->getCountry_id(), ($billingAddress->getRegionId() ? $billingAddress->getRegionId() : $billingAddress->getRegion()))),
                    'country' => $helper->getErpCountryCode($billingAddress->getCountry_id()),
                    'postcode' => $helper->stripNonPrintableChars($billingAddress->getPostcode()),
                    'emailAddress' => $helper->stripNonPrintableChars($billingAddress->getEmail() ? $billingAddress->getEmail() : $customer->getEmail()),
                    'telephoneNumber' => $helper->stripNonPrintableChars($billingAddress->getTelephone()),
                    'mobileNumber' => $helper->stripNonPrintableChars($billingAddress->getEccMobileNumber()),
                    'faxNumber' => $helper->stripNonPrintableChars($billingAddress->getFax()),
                ),
                'order' => array(
                    '_attributes' => array(
                        'preventRepricing' => $prevent_reprice ? 'Y' : 'N'
                    ),
                    'orderReference' => $reference,
                    'customerReference' => $customer_order_ref,
                    'taxExemptReference'=>($taxExemptReference)? $this->_order->getEccTaxExemptReference() :null,
                    'additionalReference' => ($this->_order->getEccAdditionalReference()) ?: null,
                    'shipStatus' => ($this->_order->getEccShipStatusErpcode()) ?: null,
                    'dateOrdered' => $helper->getFormattedDateTime($this->_order->getCreatedAt()),
                    'dateRequired' => $dateRequired,
                    'quoteNumber' => $this->_order->getEccBasketErpQuoteNumber(),
                    'eccGqrQuoteNumber' => $this->eccQuote->getWebReferenceId($this->_order->getEccQuoteId()),
                    'erpGqrQuoteNumber' => $this->_order->getEccErpQuoteId(),
                    'salesRep' => $erpAccount->getSalesRep(),
                    'goodsTotal' => $this->checkValueDataType($this->_order->getBaseSubtotal()),
                    'goodsTotalInc' => $this->checkValueDataType($this->_order->getBaseSubtotalInclTax()),
                    'discountAmount' => $this->checkValueDataType(abs($this->_order->getBaseDiscountAmount())),
                    'carriageAmount' => $this->checkValueDataType($this->_order->getBaseShippingAmount()),
                    'carriageAmountInc' => $this->checkValueDataType($this->_order->getBaseShippingInclTax()),
                    'grandTotal' => $this->checkValueDataType($this->_order->getBaseGrandTotal() - $this->_order->getBaseTaxAmount()),
                    'grandTotalInc' => $this->checkValueDataType($this->_order->getBaseGrandTotal()),
                    'visitorEmail' => ($customer->getEmail() ? $customer->getEmail() : $billingAddress->getEmail()),
                    'orderText' => $this->getOrderNote(),
                    'contractCode' => $this->_order->getEccContractCode(),
                ),
                'contact' => array(
                    'contactCode' => $customer->getEccContactCode(),
                    'name' => $customer->getName(),
                    'function' => $customer->getEccFunction(),
                    'telephoneNumber' => $customer->getTelephone(),
                    'faxNumber' => $customer->getFax(),
                    'emailAddress' => $customer->getEmail(),
                    'eccLoginId' => $customer->getId()
                ),
                'lines' => array(),
            ));

            $orderCommentConfig = (!$this->checkP21ErpSystem() && $this->getOrderCommentIncludedIn() =="E") ? "B" : $this->getOrderCommentIncludedIn();
            $data['messages']['request']['body']['order']['orderCommentConfig'] = $orderCommentConfig ;

            $items = $this->_order->getItemsCollection();
            $count = 1;
            foreach ($items as $item) {
                if ($item->getParentItemId() != null) {
                    continue;
                }
                /* @var $item \Magento\Sales\Model\Order\Item */

                $tax_class = $this->taxClassModelFactory->create()->load($item->getProduct()->getTaxClassId());
                $uomArr = $helper->splitProductCode($item->getSku());
                $productSku = $uomArr[0];
                $uomCode = $this->commonHelper->getProductUom($uomArr, $item);
                $customerSku = $item->getProduct()->getCustomerSku($erpAccount->getId(), true, true);

                $attributes = array();
                $productOptions = $helper->getItemProductOptions($item);

                if (!empty($productOptions) && !empty($productOptions['options'])) {
                    if (is_array($productOptions['options'])) {
                        $attributes['attribute'] = array();
                        foreach ($productOptions['options'] as $option) {
                            if (!in_array($option['option_type'], array(
                                'ewa_description',
                                'ewa_title',
                                'ewa_short_description',
                                'ewa_sku'
                            ))
                            ) {
                                $attributes['attribute'][] = array(
                                    'description' => $option['option_type'] == 'ewa_code' ? 'Ewa Code' : $option['label'],
                                    'value' => $option['value']
                                );
                            }
                        }
                    }
                } else if (isset($productOptions['info_buyRequest']['options']['ewa_code'])) {
                    $attributes['attribute'][] = array(
                        'description' => 'Ewa Code',
                        'value' => $productOptions['info_buyRequest']['options']['ewa_code']
                    );
                }

                $_itemProduct = $item->getProduct();
                $decimalPlaces = $helper->getDecimalPlaces($_itemProduct);
                $discount = $item->getEccOriginalPrice() - $item->getBasePrice();
                $data['messages']['request']['body']['lines']['line'][] = array(
                    '_attributes' => array(
                        'number' => $item->getId(),
                        'preventRepricing' =>
                            RepriceFlag::getItemRepricingFlag($discount, $item->getQtyOrdered(), $prevent_reprice)
                    ),
                    'productCode' => $productSku,
                    'unitOfMeasureCode' => $uomCode,
                    'contractCode' => $item->getEccContractCode(),
                    'locationCode' => $item->getEccLocationCode(),
                    'decimalPlaces' => $decimalPlaces,
                    'quantity' => $helper->qtyRounding($item->getQtyOrdered(), $decimalPlaces),
                    'price' => $this->checkValueDataType($item->getBasePrice()),
                    'priceInc' => $this->checkValueDataType($item->getBasePriceInclTax()),
                    'lineValue' => $this->checkValueDataType($item->getBaseRowTotal()),
                    'lineValueInc' => $this->checkValueDataType($item->getBaseRowTotalInclTax()),
                    'lineDiscount' => $this->checkValueDataType($discount * $item->getQtyOrdered()),
                    'taxCode' => $tax_class->getClassName(),
                    'dateRequired' => $item->getEccRequiredDate() ? $helper->getLocalDate(strtotime($item->getEccRequiredDate()), \IntlDateFormatter::LONG) : $dateRequired,
                    'customer' => array(
                        'productCode' => $customerSku,
                        'lineText' => $item->getEccLineComment(),
                    ),
                    'eccGqrLineNumber' => $item->getEccGqrLineNumber(),
                    'attributes' => $attributes
                );

                ++$count;
            }

            $data = $this->dataObjectFactory->create(['data' => $data]);

            $this->eventManager->dispatch(
                'epicor_message_gor_alter',
                [
                    'request_data' => $data,
                    'order'        => $this->_order,
                ]
            );

            $this->setOutXml($data->getData());
            return true;
        }
    }

    /**
     * Get ERP Processing Data
     *
     * @return array
     */
    protected function getErpProcessing()
    {
        $erpProcessing = array();

        switch ($this->_order->getPayment()->getMethod()) {
            case 'elements':
                $eccElementsTable = $this->elementsTransactionFactory->create();
                $collection = $eccElementsTable->getCollection()->addFieldToFilter('quote_id', $this->_order->getQuoteId())
                    ->addFieldToFilter('hosted_express_response_message', 'PaymentAccount created')
                    ->addFieldToFilter('hosted_payment_status', 'Complete')
                    ->getLastItem();
                $paymentAccountId = $this->_order->getPayment()->getEccElementsPaymentAccountId() ?
                                    $this->_order->getPayment()->getEccElementsPaymentAccountId() :
                                    $collection->getPaymentAccountId();
                $processorId = $this->_order->getPayment()->getEccElementsProcessorId() ?
                      $this->_order->getPayment()->getEccElementsProcessorId() :
                      $this->scopeConfig->getValue('payment/elements/AccountID',
                          \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());


                $erpProcessing = array(
                    'elements' => array(
                        'processorId' => $processorId ,
                        'avsCode' => $this->_order->getPayment()->getCcAvsStatus(),
                        'cv2Code' => $this->_order->getPayment()->getEccCcCvvStatus(),
                        'paymentAccountId' => $paymentAccountId,
                    )
                );
                break;

            case 'verifone':
                $verifone = $this->verifoneTransactionFactory->create()->load($this->_order->getPayment()->getVerifoneTransactionId());
                /* @var $verifone \Epicor\Verifone\Model\Transaction */

                $erpProcessing = array(
                    'verifone' => array(
                        'tokenId' => $verifone->getToken()->getToken(),
                        'processingDb' => $verifone->getProcessingdb(),
                        'pcAvsResult' => $verifone->getPcavsresult(),
                        'add1AvsResult' => $verifone->getAd1avsresult(),
                        'atsData' => $verifone->getAtsdata(),
                    )
                );
                break;

            case 'esdm':
                $erpProcessing = array(
                    'esdm' => array(
                        'cardToken' => $this->_order->getPayment()->getEccCcvToken(),
                        'cv2Token' => $this->_order->getPayment()->getEccCvvToken(),
                    )
                );
                break;

            case 'cre':
                $erpProcessing = array(
                    'cre' => array(
                        'cardToken' => $this->_order->getPayment()->getEccCcvToken(),
                        'cv2Token' => $this->_order->getPayment()->getEccCvvToken(),
                    ),
                    'esdm' => array(
                        'cardToken' => $this->_order->getPayment()->getEccCcvToken(),
                        'cv2Token' => $this->_order->getPayment()->getEccCvvToken(),
                    )
                );
                break;

        }

        return $erpProcessing;
    }

    /**
     * Process message response
     * @return boolean
     */
    public function processResponse()
    {
        $helper = $this->commMessagingHelper->create();
        if (!$this->getIsSuccessful() || $this->isErrorStatusCode()) {
            if ($this->getStatusCode() != self::STATUS_SERVICE_OFFLINE) {
                $this->_order->setEccGorSent(self::GOR_STATUS_ERROR);
                $this->_order->setEccGorFlow(self::GOR_FLOW_FAILED);
            }

            $this->_order->setEccGorMessage($this->getStatusCode() . " : " . $this->getStatusDescription());
            $this->_order->save();

            return false;
        } else {
            $gorMessage = '';
            if ($this->getStatusCode() == self::STATUS_DUPLICATE_ORDER) {
                // $this->getResponse()->setOrderNumber('5480');
                // if gor has been sent twice the response will be 'duplicate' - a 903 error
                // check to see if there is a valid order matching the orderNumber field
                // if there is, mark the new order as duplicate. If not set status to "success" and erp order number to number returned
                $orderExists = $this->isOrderDuplicate($this->getResponse()->getOrderNumber());
                //$orderExists = $this->salesOrderFactory->create()->loadByAttribute('ecc_erp_order_number', $this->getResponse()->getOrderNumber());
                if ($orderExists) {                        // if order doesn't exist, update erpOrderNumber on order
                    // with that returned from gor and set statusdesc to "success"
                    $gorMessage = "Duplicate Order." . "See order number: " . $orderExists->getIncrementId() . " Erp order number: " . $this->getResponse()->getOrderNumber();
                } else {                         // if order does exist, update statusdesc on new order with "duplicate"
                    $this->_order->setEccErpOrderNumber($this->getResponse()->getOrderNumber());
                    $statusDesc = $this->getStatusDescription();
                    $gorMessage = "Order Sent" . (empty($statusDesc) ? '' : " :  $statusDesc");
                }
            } else {
                $statusDesc = $this->getStatusDescription();
                $gorMessage = "Order Sent" . (empty($statusDesc) ? '' : " :  $statusDesc");
                $this->_order->setEccErpOrderNumber($this->getResponse()->getOrderNumber());
            }
            $this->_order->setEccGorFlow(self::GOR_FLOW_SUCCESS);
            $this->_order->setEccGorSent(self::GOR_STATUS_SENT);
            $this->_order->setEccGorMessage($gorMessage);
            $this->_order->save();
            $customer = $this->customerCustomerFactory->create()->load($this->_order->getCustomerId());
            #Mage::getSingleton('core/session')->unsErpQuoteNumber();
            #$this->setMessageSecondarySubject($this->getMessageSecondarySubject(). "\n" . 'ERP Order Number: ' . $this->_order->getEccErpOrderNumber());
            //Mage::log($this->getMessageSecondarySubject(), null, 'gor.log');
            if ($this->scopeConfig->getValue('epicor_comm_enabled_messages/ast_request/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId())) {
                $ast = $this->commMessageRequestAstFactory->create();
                $ast->setCustomer($customer);

                //change these values as when sent by the cron, the store is not correct. The account number should be the account related to the order
                $ast->setStore($this->storeManager->getStore($this->_order->getStoreId())->getId());
                $ast->setAccountNumber($this->commHelper->getErpAccountNumber($this->_order->getEccErpAccountId(), $ast->getStore()));

                $ast->sendMessage();
            }
            if ($this->scopeConfig->isSetFlag('epicor_comm_enabled_messages/gor_request/gor_auto_invoice', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId()) === true) {
                $helper->invoiceOrder($this->_order);
            }
            if ($this->scopeConfig->isSetFlag('epicor_comm_enabled_messages/gor_request/gor_auto_ship', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId()) === true) {
                $helper->shipOrder($this->_order);
            }

            $this->genericFactory->create()->unsTempGroupId();
            return true;
        }
    }

    /**
     *
     * @param type $month
     * @param type $year
     * @return type
     */
    private function _formatCcDates($month, $year)
    {
        return date("m/y", strtotime("$year-$month-1"));
    }

    /**
     * Returns whether error cound shall be done for the message
     *
     * @param bool $error
     * @return bool
     */
    public function doErrorCount($error)
    {
        if ($error == false) {
            return false;
        }

        if ($this->scopeConfig->getValue('Epicor_Comm/licensing/erp', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 'p21') {
            if ($this->getStatusCode() == self::STATUS_SERVICE_OFFLINE || ($this->getStatusCode() == self::STATUS_UNKNOWN && stripos($this->getStatusDescription(), 'SISM') !== false)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Skip send error email
     * For selected error code
     *
     * @param type $error
     * @return boolean
     */
    public function isEmailNotificationRequired($error = true)
    {
        $isNotify = parent::isEmailNotificationRequired($error);
        if ($isNotify) {
            $skipEmailCode = explode(',', $this->scopeConfig->getValue('epicor_comm_enabled_messages/gor_request/skip_email_error_status_codes', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $statusCode = $this->getStatusCode();
            if (in_array($statusCode, $skipEmailCode)) {
                return false;
            }
            return $isNotify;
        }
        return $isNotify;
    }

    /**
     * Get duplicated order object
     *
     * @param type $eccErpOrderNumber
     * @return Mage_Sales_Model_Order
     */
    public function isOrderDuplicate($eccErpOrderNumber)
    {
        $collection = $this->salesResourceModelOrderCollectionFactory->create();
        /* @var $collection  Mage_Sales_Model_Resource_Order_Collection */
        $collection->addFieldToFilter('ecc_erp_order_number', $eccErpOrderNumber);
        return $collection->getFirstItem()->getId() ? $collection->getFirstItem() : false;
    }
}
