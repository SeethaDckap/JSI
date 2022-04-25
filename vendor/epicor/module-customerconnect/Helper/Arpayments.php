<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Helper;

/**
 * Dealers Helper
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */

use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\OrderFactory;


class Arpayments extends \Magento\Framework\App\Helper\AbstractHelper
{


    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /*
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customerCustomer;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    protected $currencyCode;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $pricing;

    /**
     * @var \Epicor\Customerconnect\Model\ArPayment\Quote
     */
    protected $arSession;

    protected $listsSessionHelper;

    /**
     * @var \Epicor\Customerconnect\Model\ArPayment\OrderFactory
     */
    protected $arpaymentOrder;

    /**
     * @var PhpCookieManager
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var OrderFactory
     */
    private $orderResourceFactory;

    /**
     * @var \Epicor\AccessRight\Helper\Data
     */
    protected $_accessHelper;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Helper\Data $commHelper,
        PhpCookieManager $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        \Epicor\Customerconnect\Model\ArPayment\Session\Proxy $arSession,
        \Epicor\Customerconnect\Model\ArPayment\OrderFactory $arpaymentOrder,
        \Epicor\Lists\Helper\Session $listsSessionHelper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        OrderRepository $orderRepository,
        OrderFactory $orderResourceFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Customer $customerCustomer,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Eav\Model\Config $eavConfig,
        \Epicor\AccessRight\Helper\Data $accessHelper
    ) {
        $this->customerSession = $customerSession;
        $this->commHelper = $commHelper;
        $this->storeManager = $storeManager;
        $this->currencyCode = $currencyFactory->create();
        $this->customerCustomer = $customerCustomer;
        $this->request = $request;
        $this->eavConfig = $eavConfig;
        $this->scopeConfig = $context->getScopeConfig();
        $this->pricing = $pricingHelper;
        $this->arSession = $arSession;
        $this->listsSessionHelper = $listsSessionHelper;
        $this->arpaymentOrder = $arpaymentOrder;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->orderRepository = $orderRepository;
        $this->orderResourceFactory = $orderResourceFactory;
        $this->_accessHelper = $accessHelper;
        parent::__construct(
            $context
        );
    }


    public function getCurrencySymbol()
    {
        $currentCurrency = $this->storeManager->getStore()->getCurrentCurrencyCode();
        $currency = $this->currencyCode->load($currentCurrency);
        $currencySymbol = $currency->getCurrencySymbol();
        return $currencySymbol;
    }

    public function varienToArray($var_obj)
    {
        $array = array();

        $data = ($var_obj instanceof \Magento\Framework\DataObject) ? $var_obj->getData() : $var_obj;

        if (!is_array($data)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            $array[$key] = $this->varienToArray($value);
        }

        return $array;
    }

    /*
     * Check is InvoiceField edit supported or not
     */

    public function getIsInVoiceEditSupported() {
        if (!$this->_accessHelper->isAllowed("Epicor_Customerconnect::customerconnect_account_ar_payment_payment_payment")) {
            return false;
        }
        $commHelper = $this->commHelper;
        $erpAccount = $commHelper->getErpAccountInfo();
        $customerErpAllow = $erpAccount->getIsInvoiceEdit();
        if ($customerErpAllow == 2 || $customerErpAllow == NULL) {
            $allow = $this->scopeConfig->getValue('customerconnect_enabled_messages/CAAP_request/is_invoice_edit', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        } elseif ($customerErpAllow == 0 ) {
            $allow = false;
        } elseif ($customerErpAllow == 1) {
            $allow = true;
        }

        $allowCaap = $this->scopeConfig->getValue('customerconnect_enabled_messages/CAAP_request/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if(!$allowCaap) {
            $allow=false;
        }

        return $allow;
    }


    /**
     * Check AR Payments Disputes is Active Or Not
     */
    public function checkDisputeAllowedOrNot()
    {
        $commHelper = $this->commHelper;
        $erpAccount = $commHelper->getErpAccountInfo();
        $checkErp   = $erpAccount->getIsArpaymentsAllowed();
        if ($checkErp !=3) {
            $allow = $this->scopeConfig->getValue('customerconnect_enabled_messages/CAAP_request/caap_disputeactive', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        if ($checkErp == 3) {
            $allow = false;
        }
        return $allow;
    }


    public function formatPrices($price) {
        return $this->pricing->currency($price,true,true);
    }

    public function formatPriceWithoutCode($price) {
        return $this->currencyCode->format($price, ['display'=>\Zend_Currency::NO_SYMBOL], false);
    }


    /**
     * Get Arpayments quote
     */
    public function getArpaymentsQuote() {
        $arQuote = $this->arSession->getQuote();
        //set Payment Method
        return $arQuote;
    }

    /**
     * Get Resource Connection
     */

    public function getResourceConnection() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        return $resource;
    }

    /**
     * get the Arpayments handle name
     */

    public function getArpaymentsHandle() {
        $handle = $this->request->getFullActionName();
        return $handle;
    }


    /**
     * Check the current page is Arpayments Page or other page
     */

    public function checkArpaymentsPage() {
        $handle = $this->request->getFullActionName();
        $referer = '';
        if(isset($_SERVER['HTTP_REFERER'])) {
            $referer = $_SERVER['HTTP_REFERER'];
        }
        $valid = false;
        if (strpos($referer, "archeckout")!==false){
            $valid = true;
        }
        if($handle =="customerconnect_arpayments_archeckout" || $valid) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get Arpayments Quote Id from customer session
     */
    public function getArpaymentsSessionQuoteId() {
        $sessionHelper = $this->listsSessionHelper;
        $arPaymentsSession = $sessionHelper->getValue('ecc_arpayments_quote');
        return $arPaymentsSession;
    }

    /**
     * Copying the values from Magento 2 default quote to arpayments Quote
     */
    public function setPaymentMethod($mOrder, $paymentmethod, $eccElementsTransId = null) {
        $quote = $this->getArpaymentsQuote();
        $quote->setPaymentMethod($paymentmethod); //payment method
        //$quote->setInventoryProcessed(false); //not effetc inventory
        $quote->getPayment()->setIsTransactionClosed(0);
        $quote->getPayment()->setAdditionalInformation($mOrder->getPayment()->getAdditionalInformation());
        $quote->getPayment()->setCcLast4($mOrder->getPayment()->getCcLast4());
        $quote->getPayment()->setCcExpMonth($mOrder->getPayment()->getCcExpMonth());
        $quote->getPayment()->setCcExpYear($mOrder->getPayment()->getCcExpYear());
        $quote->getPayment()->setCcType($mOrder->getPayment()->getCcType());
        $quote->getPayment()->setLastTransId($mOrder->getPayment()->getLastTransId());
        $quote->getPayment()->setCcTransId($mOrder->getPayment()->getCcTransId());
        $quote->getPayment()->setCcNumber($mOrder->getPayment()->getCcNumber());
        $quote->getPayment()->setCcCid($mOrder->getPayment()->getCcCid());
        $quote->getPayment()->setEccElementsProcessorId($mOrder->getPayment()->getEccElementsProcessorId());
        if (is_null($eccElementsTransId) || empty($eccElementsTransId)) {
            $eccElementsTransId = $mOrder->getPayment()->getEccElementsTransactionId();
        }
        $quote->getPayment()->setEccElementsTransactionId($eccElementsTransId);
        $quote->getPayment()->setEccElementsPaymentAccountId($mOrder->getPayment()->getEccElementsPaymentAccountId());
        $quote->getPayment()->setEccCcCvvStatus($mOrder->getPayment()->getEccCcCvvStatus());
        $quote->getPayment()->setEccCcAuthCode($mOrder->getPayment()->getEccCcAuthCode());
        $quote->getPayment()->setEccCcvToken($mOrder->getPayment()->getEccCcvToken());
        $quote->getPayment()->setEccCvvToken($mOrder->getPayment()->getEccCvvToken());
        if($mOrder->getPayment()->getLastTransId()) {
            $quote->getPayment()->setCcTransId($mOrder->getPayment()->getLastTransId());
        }
        $quote->getPayment()->setEccIsSaved($mOrder->getPayment()->getEccIsSaved());
        $quote->getPayment()->importData($mOrder->getPayment()->toArray());
        $quote->getPayment()->save();
        $this->clearArpaymentStorage();
        $order = $quote->submitQuote($quote);
        $this->arSession->clearHelperData();
        $this->arSession
            ->setLastOrderId($order->getId())
            ->setRealArOrderId($mOrder->getId())
            ->setRedirectUrl()
            ->setLastRealOrderId($order->getIncrementId())
            ->setLastOrderStatus($order->getStatus());
        $orderInsert = $this->arpaymentOrder->create()->load($order->getId());
        $orderInsert->getPayment()->save();
        if($order->getPayment()->getCcTransId() || $order->getPayment()->getLastTransId()) {
            $txnId = ($order->getPayment()->getLastTransId()) ? $order->getPayment()->getLastTransId() : $order->getPayment()->getCcTransId();
            $message = ' ' . __('Transaction ID: "%1"', $txnId);
            $orderInsert->addStatusHistoryComment($message)->save();
        }
    }

    /**
     * Clearing the Arpayments Storage
     */
    public  function clearArpaymentStorage() {
        if ($this->cookieManager->getCookie('mage-cache-sessid')) {
            $metadata = $this->cookieMetadataFactory->createCookieMetadata();
            $metadata->setPath('/');
            $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
        }
    }

    /**
     * Delete the particular order in Magento 2 Order Table
     */
    public function deleteOrder($order) {
        $this->orderRepository->delete($order);
    }

    /**
     * Delete all the information in the Order Grid Table
     * @param $orderId
     */
    public function deleteRecord($orderId)
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order $resource */
        $resource = $this->orderResourceFactory->create();
        $connection = $resource->getConnection();
        /** delete invoice grid record via resource model*/
        $connection->delete(
            $resource->getTable('sales_invoice_grid'),
            $connection->quoteInto('order_id = ?', $orderId)
        );
        /** delete shipment grid record via resource model*/
        $connection->delete(
            $resource->getTable('sales_shipment_grid'),
            $connection->quoteInto('order_id = ?', $orderId)
        );
        /** delete creditmemo grid record via resource model*/
        $connection->delete(
            $resource->getTable('sales_creditmemo_grid'),
            $connection->quoteInto('order_id = ?', $orderId)
        );
        return;
    }
}
