<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Message\Request;


/**
 * Request CAPS - Customer Account AR Payments
 *
 *
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Caap extends \Epicor\Comm\Model\Message\Request
{

    const CAAP_STATUS_NOT_SENT = 0;
    const CAAP_STATUS_SENT = 1;
    const CAAP_STATUS_ERROR = 3;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Magento\Tax\Model\ClassModelFactory
     */
    protected $taxClassModelFactory;

    /**
     * @var \Epicor\Verifone\Model\TransactionFactory
     */
    //protected $verifoneTransactionFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $salesOrderFactory;

    /**
     * @var \Epicor\Comm\Model\Message\Request\AstFactory
     */
    protected $commMessageRequestAstFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $salesResourceModelOrderCollectionFactory;
    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\CollectionFactory
     */
    protected $erpaccountAddressCollectionFactory;
    /**
     * @var \Magento\Customer\Model\ResourceModel\Address\CollectionFactory
     */
    protected $addressCollectionFactory;

    /**
     * Construct object and set message type.
     */
    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Tax\Model\ClassModelFactory $taxClassModelFactory,
        //\Epicor\Verifone\Model\TransactionFactory $verifoneTransactionFactory,
        \Magento\Sales\Model\OrderFactory $salesOrderFactory,
        \Epicor\Comm\Model\Message\Request\AstFactory $commMessageRequestAstFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesResourceModelOrderCollectionFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\CollectionFactory $erpaccountAddressCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $addressCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->registry = $context->getRegistry();
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->taxClassModelFactory = $taxClassModelFactory;
        //$this->verifoneTransactionFactory = $verifoneTransactionFactory;
        $this->salesOrderFactory = $salesOrderFactory;
        $this->commMessageRequestAstFactory = $commMessageRequestAstFactory;
        $this->salesResourceModelOrderCollectionFactory = $salesResourceModelOrderCollectionFactory;
        $this->erpaccountAddressCollectionFactory       = $erpaccountAddressCollectionFactory;
        $this->addressCollectionFactory                 = $addressCollectionFactory;
        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->setMessageType('CAAP');
        $this->setLicenseType(array('Consumer', 'Customer'));
        $this->setMessageCategory(self::MESSAGE_CATEGORY_ORDER);
        $this->setConfigBase('customerconnect_enabled_messages/CAAP_request/');

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

        $reference = $this->_order->getIncrementId();

        $this->setMessageSecondarySubject('Payment Reference: ' . $reference
            . "\n" . 'ARPAYMENTS Quote ID: ' . $order->getQuoteId());

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

        return $this->getCustomerNote() ?: $carriageText;
    }

    /**
     * Get Order Note
     *
     * @return string
     */
    public function getOrderNote()
    {
        return $this->getCustomerNote();
    }

    /**
     * Limits the customer comment to the predefined value.
     */
    private function getCustomerNote()
    {
        $note = $this->_order->getCustomerNote();
        if ($this->registry->registry('customerComment'))
            $note = $this->registry->registry('customerComment');
//        else
//            $note = $this->_order->getStatusHistoryCollection()->getLastItem()->getComment();

        if ($this->scopeConfig->isSetFlag('checkout/options/limit_comment_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId())) {
            $value = $this->scopeConfig->getValue('checkout/options/max_comment_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());
            if (is_int($value)) {
                $note = substr($note, 0, $value);
            }
        }
        return $note;
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
        //$session->setTempGroupId($this->getCustomerGroupId());

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

            $defaultAddressCode = $this->scopeConfig->getValue('epicor_comm_enabled_messages/global_request/default_address_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());

            $billingAddress = ($this->_order->getBillingAddress()) ?: $this->_order->getShippingAddress();
            /* @var $billingAddress \Magento\Sales\Model\Order\Address */
            $customer = $this->customerCustomerFactory->create()->load($this->_order->getCustomerId());
            /* @var $customer \Magento\Customer\Model\Customer */

            $customer->getEccMobileNumber() ? null : $customer->setEccMobileNumber($billingAddress->getEccMobileNumber());  // if no customer mobile number, assign billing mobile number
            $addressCode = '';
            if (!empty($billingAddress->getCustomerAddressId())) {
                if ($customer->isSalesRep()) {
                    $addressCode = $this->getAddressCodeForSalesrep($billingAddress->getCustomerAddressId());
                }else{
                    $addressCode = $this->getAddressCodeBycustomerAddressId($billingAddress->getCustomerAddressId());
                }
            }
            //get payment details
            $payment = $helper->getPaymentMethodMap($this->_order->getPayment()->getMethod());
            $paymentMethod = $payment->getErpCode();
            $paymentCollected = $payment->getPaymentCollected();
            //get order details
            $reference = $this->scopeConfig->getValue('epicor_comm_enabled_messages/gor_request/gor_order_prefix', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());
            $reference .= $this->_order->getIncrementId();
            $customer_order_ref = $this->_order->getEccCustomerOrderRef() ?: $reference;
            //$orderTotal = (Mage::getStoreConfigFlag('epicor_comm_enabled_messages/gor_request/gor_total_inc_del_tax')) ? $this->_order->getGrandTotal() : $this->_order->getSubtotal();
            //set email for logging purposes
            $this->setEmail($customer->getEmail());
            // get order reprice flag
            $prevent_reprice = $helper->getOrderRepriceValue($this->_order, $paymentMethod, 'GOR');
            $quotePrefix = $this->_order->getEccQuoteId() ? $this->scopeConfig->getValue('epicor_quotes/general/prefix', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId()) : '';
            $data = $this->getMessageTemplate();
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
                'eccPaymentReference' => $this->_order->getIncrementId(),
                'paymentAmount' => $this->_order->getGrandTotal(),
                //'paymentOnAccount' => ($this->_order->getEccArpaymentsIspayment()) ? $this->_order->getEccArpaymentsAllocatedAmount() : '0.00',                
                'paymentOnAccount' => ($this->_order->getEccArpaymentsAmountleft())? $this->_order->getEccArpaymentsAmountleft() : '0.00',
                'paymentBy' => array(
                    'contactCode' => $customer->getContactCode(),
                    'contactName' => $this->_order->getBillingAddress()->getName(),
                    'addressCode'=> (!empty($addressCode))? $addressCode : $defaultAddressCode,
                    'address1' => $helper->stripNonPrintableChars($billingAddress->getStreetLine(1)),
                    'address2' => $helper->stripNonPrintableChars($billingAddress->getStreetLine(2)),
                    'address3' => $helper->stripNonPrintableChars($billingAddress->getStreetLine(3)),
                    'city'=>$this->_order->getBillingAddress()->getCity(),
                    'county'=>$helper->getRegionNameOrCode($this->_order->getBillingAddress()->getCountryId(), $this->_order->getBillingAddress()->getRegion()),
                    'country'=>$this->_order->getBillingAddress()->getCountryId(),
                    'function' => $customer->getFunction(),
                    'postcode'=>$this->_order->getBillingAddress()->getPostcode(),
                    'telephoneNumber' => $this->_order->getBillingAddress()->getTelephone(),
                    'mobileNumber' => $this->_order->getBillingAddress()->getMobileNumber(),
                    'faxNumber' => $this->_order->getBillingAddress()->getFax(),
                    'name'=>($customer->getName()) ? $customer->getName() : $this->_order->getBillingAddress()->getName(),
                    'emailAddress' => $customer->getEmail(),
                    'eccLoginId' => $customer->getId()
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
                        'isSavedToken' => $this->_order->getPayment()->getEccIsSaved() ? "Y" : "N", 
                        'erpProcessing' => $this->getErpProcessing()
                    )
                ),
                'invoices' => array(),
            ));

            //$data['messages']['request']['body']['delivery']['deliveryAddress']['carriageText'] = $this->getCustomerNote();

            $items = $this->_order->getItemsCollection();
            $count = 1;
            if(count($items)>0) {
                foreach ($items as $item) {
                    $info  = $this->Combinevalues($item->getAdditionalData());
                    $data['messages']['request']['body']['invoices']['invoice'][] = array(
                        'invoiceNumber' => $item->getSku(),
                        'paymentAmount' => $item->getRowTotal(),
                        'settlementDiscount' => "0.00",
                        'invoiceDisputed' => $info['dispute'],
                        'invoiceDisputedReason' => $info['disputeComment']
                    );
                    ++$count;                
                }
            }
            $this->setOutXml($data);
            return true;
        }
    }
    public function Combinevalues($postParams) {
        $insertedVals = json_decode($postParams, true);
        return $insertedVals;
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
                $erpProcessing = array(
                    'elements' => array(
                        'processorId' => $this->_order->getPayment()->getEccElementsProcessorId(),
                        'avsCode' => $this->_order->getPayment()->getCcAvsStatus(),
                        'cv2Code' => $this->_order->getPayment()->getEccCcCvvStatus(),
                        'paymentAccountId' => $this->_order->getPayment()->getEccElementsPaymentAccountId(),
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
            $this->_order->setEccCaapSent(self::CAAP_STATUS_ERROR);
            if($this->getStatusCode() == self::STATUS_CONNECTION_ERROR){
                $this->_order->setEccCaapSent(self::CAAP_STATUS_NOT_SENT);
            }
            $this->_order->setEccCaapMessage($this->getStatusCode() . " : " . $this->getStatusDescription());
            $this->_order->save();
            return false;
        } else {
            $caapMessage = '';
            if ($this->getStatusCode() == self::STATUS_DUPLICATE_ORDER) {
                $orderExists = $this->isOrderDuplicate($this->getResponse()->getOrderNumber());
                //$orderExists = $this->salesOrderFactory->create()->loadByAttribute('ecc_erp_order_number', $this->getResponse()->getOrderNumber());
                if (orderExists) {
                    $caapMessage = "Duplicate Payment." . "See AR Payment Reference No: " . $orderExists->getIncrementId() . " Erp Payment reference: " . $this->getResponse()->getOrderNumber();
                } else {                         // if order does exist, update statusdesc on new order with "duplicate"           
                    $this->_order->setErpArpaymentsOrderNumber($this->getResponse()->getErpPaymentReference());
                    $statusDesc = $this->getStatusDescription();
                    $caapMessage = "Payment Sent" . (empty($statusDesc) ? '' : " :  $statusDesc");
                }
            } else {
                $statusDesc = $this->getStatusDescription();
                $caapMessage = "Payment Sent" . (empty($statusDesc) ? '' : " :  $statusDesc");
                $this->_order->setErpArpaymentsOrderNumber($this->getResponse()->getErpPaymentReference());
            }
            $this->_order->setEccCaapSent(self::CAAP_STATUS_SENT);
            $this->_order->setEccCaapMessage($caapMessage);
            $this->_order->save();
            $customer = $this->customerCustomerFactory->create()->load($this->_order->getCustomerId());
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

    /**
     * Get Address code by customer address id for salesrep
     *
     * @param $customerAddressId
     *
     * @return mixed
     */
    public function getAddressCodeForSalesrep($customerAddressId)
    {
        $erpCode = '';
        $addressCollData = $this->erpaccountAddressCollectionFactory->create();
        /* @var $addressColl Epicor_Comm_Model_Resource_Customer_Erpaccount_Address_Collection */
        $addressCollData->addFieldToSelect('erp_code')->addFieldToFilter('entity_id', $customerAddressId);
        if (count($addressCollData->getData()) > 0) {
            $erpCodeData = $addressCollData->getData();
            $erpCode    = $erpCodeData[0]['erp_code'];
        }

        return $erpCode;
    }

    /**
     * @param $customerAddressId
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAddressCodeByCustomerAddressId($customerAddressId)
    {
        $erpCode = '';
        $addressCollData = $this->addressCollectionFactory->create();
        /* @var $addressColl Epicor_Comm_Model_Resource_Customer_Erpaccount_Address_Collection */
        $addressCollData->addAttributeToSelect('ecc_erp_address_code', 'inner')->addAttributeToFilter('entity_id', $customerAddressId);
        if (count($addressCollData->getData()) > 0) {
            $erpCodeData = $addressCollData->getData();
            $erpCode     = $erpCodeData[0]['ecc_erp_address_code'];
        }

        return $erpCode;
    }

}
