<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

// @codingStandardsIgnoreFile

namespace Epicor\Customerconnect\Model\ArPayment\Order;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Epicor\Customerconnect\Api\Data\OrderPaymentInterface;
use Epicor\Customerconnect\Model\ArPayment\Order;
use Epicor\Customerconnect\Model\ArPayment\Order\Payment\Info;

/**
 * Order payment information
 *
 * @api
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Payment extends Info implements OrderPaymentInterface
{
    /**
     * Actions for payment when it triggered review state
     *
     * @var string
     */
    const REVIEW_ACTION_ACCEPT = 'accept';

    const REVIEW_ACTION_DENY = 'deny';

    const REVIEW_ACTION_UPDATE = 'update';

    const PARENT_TXN_ID = 'parent_transaction_id';

    /**
     * Order model object
     *
     * @var Order
     */
    protected $_order;

    /**
     * Whether can void
     * @var string
     */
    protected $_canVoidLookup = null;

    /**
     * @var string
     */
    protected $_eventPrefix = 'ecc_ar_sales_order_payment';

    /**
     * @var string
     */
    protected $_eventObject = 'payment';

    /**
     * Transaction additional information container
     *
     * @var array
     */
    protected $transactionAdditionalInfo = [];


    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;


    /**
     * @var Payment\Processor
     */
    protected $orderPaymentProcessor;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var CreditmemoManager
     */
    private $creditmemoManager = null;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Epicor\Customerconnect\Model\ArPayment\Order\CreditmemoFactory $creditmemoFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Epicor\Customerconnect\Model\ArPayment\Order\Payment\Transaction\ManagerInterface $transactionManager
     * @param Transaction\BuilderInterface $transactionBuilder
     * @param Payment\Processor $paymentProcessor
     * @param OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param CreditmemoManager $creditmemoManager
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        PriceCurrencyInterface $priceCurrency,
        \Epicor\Customerconnect\Model\ArPayment\Order\Payment\Processor $paymentProcessor,
        \Epicor\Customerconnect\Model\ArPayment\Order $orderRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->orderRepository = $orderRepository;
        $this->orderPaymentProcessor = $paymentProcessor;
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
        $this->_init(\Epicor\Customerconnect\Model\ArPayment\ResourceModel\Order\Payment::class);
    }

    /**
     * Declare order model object
     *
     * @codeCoverageIgnore
     *
     * @param Order $order
     * @return $this
     */
    public function setOrder(Order $order)
    {
        $this->_order = $order;

        return $this;
    }

    /**
     * Retrieve order model object
     *
     * @codeCoverageIgnore
     * @return Order
     */
    public function getOrder()
    {
        if (!$this->_order && $this->getParentId()) {
            $this->_order = $this->orderRepository->laod($this->getParentId());
        }

        return $this->_order;
    }

    /**
     * Sets transaction id for current payment
     *
     * @param string $transactionId
     * @return $this
     */
    public function setTransactionId($transactionId)
    {
        $this->setData('transaction_id', $transactionId);

        return $this;
    }

    /**
     * Return transaction id
     *
     * @return int
     */
    public function getTransactionId()
    {
        return $this->getData('transaction_id');
    }

    /**
     * Sets transaction close flag
     *
     * @param bool $isClosed
     * @return $this
     */
    public function setIsTransactionClosed($isClosed)
    {
        $this->setData('is_transaction_closed', (bool)$isClosed);

        return $this;
    }

    /**
     * Returns transaction parent
     *
     * @return string
     */
    public function getParentTransactionId()
    {
        return $this->getData(self::PARENT_TXN_ID);
    }

    /**
     * Returns transaction parent
     *
     * @return string
     * @since 100.1.0
     */
    public function setParentTransactionId($txnId)
    {
        return $this->setData(self::PARENT_TXN_ID, $txnId);
    }

    /**
     * Check order payment capture action availability
     *
     * @return bool
     */
    public function canCapture()
    {
       

        return false;
    }

    /**
     * @return bool
     */
    public function canRefund()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function canRefundPartialPerInvoice()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function canCapturePartial()
    {
        return false;
    }

    /**
     * Authorize or authorize and capture payment on gateway, if applicable
     * This method is supposed to be called only when order is placed
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function place()
    {
        $this->_eventManager->dispatch('ecc_ar_sales_order_payment_place_start', ['payment' => $this]);
        $order = $this->getOrder();

        $this->setAmountOrdered($order->getTotalDue());
        $this->setBaseAmountOrdered($order->getBaseTotalDue());
        $this->setShippingAmount($order->getShippingAmount());
        $this->setBaseShippingAmount($order->getBaseShippingAmount());

        $methodInstance= $this->paymentData->getMethodInstance($this->getMethod());
        $methodInstance->setStore($order->getStoreId());

        $orderState = Order::STATE_NEW;
        $orderStatus = $methodInstance->getConfigData('order_status');
        $isCustomerNotified = $order->getCustomerNoteNotify();

        // Do order payment validation on payment method level
//        $methodInstance->validate();
        $action = $methodInstance->getConfigPaymentAction();

        if ($action) {
            if ($methodInstance->isInitializeNeeded()) {
                $orderState = Order::STATE_PROCESSING;
                $this->processAction($action, $order);
                $orderState = $order->getState() ? $order->getState() : $orderState;
                $orderStatus = $order->getStatus() ? $order->getStatus() : $orderStatus;
            } else {
                $orderState = Order::STATE_PROCESSING;
                $this->processAction($action, $order);
                $orderState = $order->getState() ? $order->getState() : $orderState;
                $orderStatus = $order->getStatus() ? $order->getStatus() : $orderStatus;
            }
        } else {
            $order->setState($orderState)
                ->setStatus($orderStatus);
        }

        $isCustomerNotified = $isCustomerNotified ?: $order->getCustomerNoteNotify();

        if (!array_key_exists($orderStatus, $order->getConfig()->getStateStatuses($orderState))) {
            $orderStatus = $order->getConfig()->getStateDefaultStatus($orderState);
        }

        $this->updateOrder($order, $orderState, $orderStatus, $isCustomerNotified);

        $this->_eventManager->dispatch('ecc_ar_sales_order_payment_place_end', ['payment' => $this]);

        return $this;
    }

    /**
     * Set appropriate state to order or add status to order history
     *
     * @param Order $order
     * @param string $orderState
     * @param string $orderStatus
     * @param bool $isCustomerNotified
     * @return void
     */
    protected function updateOrder(Order $order, $orderState, $orderStatus, $isCustomerNotified)
    {
        // add message if order was put into review during authorization or capture
        $message = $order->getCustomerNote();
        $originalOrderState = $order->getState();
        $originalOrderStatus = $order->getStatus();

        switch (true) {
            case ($message && ($originalOrderState == Order::STATE_PAYMENT_REVIEW)):
                $order->addStatusToHistory($originalOrderStatus, $message, $isCustomerNotified);
                break;
            case ($message):
            case ($originalOrderState && $message):
            case ($originalOrderState != $orderState):
            case ($originalOrderStatus != $orderStatus):
                $order->setState($orderState)
                    ->setStatus($orderStatus)
                    ->addStatusHistoryComment($message)
                    ->setIsCustomerNotified($isCustomerNotified);
                break;
            default:
                break;
        }
    }

    /**
     * Perform actions based on passed action name
     *
     * @param string $action
     * @param Order $order
     * @return void
     */
    protected function processAction($action, Order $order)
    {
        $totalDue = $order->getTotalDue();
        $baseTotalDue = $order->getBaseTotalDue();

        switch ($action) {
            case \Magento\Payment\Model\Method\AbstractMethod::ACTION_ORDER:
                $this->_order($baseTotalDue);
                break;
            case \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE:
               // $this->authorize(true, $baseTotalDue);
                // base amount will be set inside
                $this->setAmountAuthorized($totalDue);
                break;
            case \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE_CAPTURE:
                $this->setAmountAuthorized($totalDue);
                $this->setBaseAmountAuthorized($baseTotalDue);
               // $this->capture(null);
                break;
            default:
                break;
        }
    }

   


    /**
     * Process authorization notification
     *
     * @param float $amount
     * @return $this
     * @see self::_authorize()
     */
    public function registerAuthorizationNotification($amount)
    {
        return $this->checkIfTransactionExists() ? $this : $this->authorize(false, $amount);
    }

    /**
     * Register payment fact: update self totals from the invoice
     *
     * @param Invoice $invoice
     * @return $this
     */
    public function pay($invoice)
    {
        $totals = $this->collectTotalAmounts(
            $this->getOrder(),
            ['grand_total', 'base_grand_total', 'shipping_amount', 'base_shipping_amount']
        );
        $this->setAmountPaid($totals['grand_total']);
        $this->setBaseAmountPaid($totals['base_grand_total']);
        $this->setShippingCaptured($totals['shipping_amount']);
        $this->setBaseShippingCaptured($totals['base_shipping_amount']);
        $this->_eventManager->dispatch('ecc_ar_sales_order_payment_pay', ['payment' => $this, 'invoice' => $invoice]);

        return $this;
    }

    /**
     * Cancel specified invoice: update self totals from it
     *
     * @param Invoice $invoice
     * @return $this
     */
    public function cancelInvoice($invoice)
    {
        $this->_updateTotals(
            [
                'amount_paid' => -1 * $invoice->getGrandTotal(),
                'base_amount_paid' => -1 * $invoice->getBaseGrandTotal(),
                'shipping_captured' => -1 * $invoice->getShippingAmount(),
                'base_shipping_captured' => -1 * $invoice->getBaseShippingAmount(),
            ]
        );
        $this->_eventManager->dispatch(
            'ecc_ar_sales_order_payment_cancel_invoice',
            ['payment' => $this, 'invoice' => $invoice]
        );
        return $this;
    }

    /**
     * Create new invoice with maximum qty for invoice for each item
     * register this invoice and capture
     *
     * @return Invoice
     */
    protected function _invoice()
    {
        $invoice = $this->getOrder()->prepareInvoice();

        $invoice->register();
        if ($this->getMethodInstance()->canCapture()) {
            $invoice->capture();
        }

        $this->getOrder()->addRelatedObject($invoice);

        return $invoice;
    }

    /**
     * Check order payment void availability
     *
     * @return bool
     */
    public function canVoid()
    {
        if (null === $this->_canVoidLookup) {
            $this->_canVoidLookup = (bool)$this->getMethodInstance()->canVoid();
            if ($this->_canVoidLookup) {
                $authTransaction = $this->getAuthorizationTransaction();
                $this->_canVoidLookup = (bool)$authTransaction && !(int)$authTransaction->getIsClosed();
            }
        }

        return $this->_canVoidLookup;
    }

    /**
     * Void payment online
     *
     * @param \Magento\Framework\DataObject $document
     * @return $this
     * @see self::_void()
     */
    public function void(\Magento\Framework\DataObject $document)
    {
        $this->_void(true);
        $this->_eventManager->dispatch('ecc_ar_sales_order_payment_void', ['payment' => $this, 'invoice' => $document]);

        return $this;
    }

    /**
     * Process void notification
     *
     * @param float $amount
     * @return $this
     * @see self::_void()
     */
    public function registerVoidNotification($amount = null)
    {
        if (!$this->hasMessage()) {
            $this->setMessage(__('Registered a Void notification.'));
        }

        return $this->_void(false, $amount);
    }

    /**
     * Sets creditmemo for current payment
     *
     * @param Creditmemo $creditmemo
     * @return $this
     */
    public function setCreditmemo($creditmemo)
    {
        $this->setData('creditmemo', $creditmemo);

        return $this;
    }

    /**
     * Returns Creditmemo assigned for this payment
     *
     * @return Creditmemo|null
     */
    public function getCreditmemo()
    {
        return $this->getData('creditmemo') instanceof Creditmemo
            ? $this->getData('creditmemo')
            : null;
    }

   


    /**
     * Cancel a creditmemo: substract its totals from the payment
     *
     * @param Creditmemo $creditmemo
     * @return $this
     */
    public function cancelCreditmemo($creditmemo)
    {
        $this->_updateTotals(
            [
                'amount_refunded' => -1 * $creditmemo->getGrandTotal(),
                'base_amount_refunded' => -1 * $creditmemo->getBaseGrandTotal(),
                'shipping_refunded' => -1 * $creditmemo->getShippingAmount(),
                'base_shipping_refunded' => -1 * $creditmemo->getBaseShippingAmount(),
            ]
        );
        $this->_eventManager->dispatch(
            'ecc_ar_sales_order_payment_cancel_creditmemo',
            ['payment' => $this, 'creditmemo' => $creditmemo]
        );

        return $this;
    }

    /**
     * Order cancellation hook for payment method instance
     * Adds void transaction if needed
     *
     * @return $this
     */
    public function cancel()
    {
        $isOnline = true;
        if (!$this->canVoid()) {
            $isOnline = false;
        }

        if (!$this->hasMessage()) {
            $this->setMessage($isOnline ? __('Canceled order online') : __('Canceled order offline'));
        }

        if ($isOnline) {
            $this->_void($isOnline, null, 'cancel');
        }

        $this->_eventManager->dispatch('ecc_ar_sales_order_payment_cancel', ['payment' => $this]);

        return $this;
    }

    /**
     * Check order payment review availability
     *
     * @return bool
     */
    public function canReviewPayment()
    {
        return (bool)$this->getMethodInstance()->canReviewPayment();
    }

    /**
     * @return bool
     */
    public function canFetchTransactionInfo()
    {
        return (bool)$this->getMethodInstance()->canFetchTransactionInfo();
    }

    /**
     * Accept online a payment that is in review state
     *
     * @return $this
     */
    public function accept()
    {
        $transactionId = $this->getLastTransId();

        /** @var \Magento\Payment\Model\Method\AbstractMethod $method */
        $method = $this->getMethodInstance();
        $method->setStore($this->getOrder()->getStoreId());
        if ($method->acceptPayment($this)) {
            $invoice = $this->_getInvoiceForTransactionId($transactionId);
            $message = $this->_appendTransactionToMessage(
                $transactionId,
                $this->prependMessage(__('Approved the payment online.'))
            );
            $this->updateBaseAmountPaidOnlineTotal($invoice);
            $this->setOrderStateProcessing($message);
        } else {
            $message = $this->_appendTransactionToMessage(
                $transactionId,
                $this->prependMessage(__('There is no need to approve this payment.'))
            );
            $this->setOrderStatePaymentReview($message, $transactionId);
        }

        return $this;
    }

    /**
     * Accept order with payment method instance
     *
     * @param bool $isOnline
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deny($isOnline = true)
    {
        $transactionId = $isOnline ? $this->getLastTransId() : $this->getTransactionId();

        if ($isOnline) {
            /** @var \Magento\Payment\Model\Method\AbstractMethod $method */
            $method = $this->getMethodInstance();
            $method->setStore($this->getOrder()->getStoreId());

            $result = $method->denyPayment($this);
        } else {
            $result = (bool)$this->getNotificationResult();
        }

        if ($result) {
            $invoice = $this->_getInvoiceForTransactionId($transactionId);
            $message = $this->_appendTransactionToMessage(
                $transactionId,
                $this->prependMessage(__('Denied the payment online'))
            );
            $this->cancelInvoiceAndRegisterCancellation($invoice, $message);
        } else {
            $txt = $isOnline ?
                'There is no need to deny this payment.' : 'Registered notification about denied payment.';
            $message = $this->_appendTransactionToMessage(
                $transactionId,
                $this->prependMessage(__($txt))
            );
            $this->setOrderStatePaymentReview($message, $transactionId);
        }

        return $this;
    }

    /**
     * Performs registered payment update.
     *
     * @param bool $isOnline
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function update($isOnline = true)
    {
        $transactionId = $isOnline ? $this->getLastTransId() : $this->getTransactionId();
        $invoice = $this->_getInvoiceForTransactionId($transactionId);

        if ($isOnline) {
            $method = $this->getMethodInstance();
            $method->setStore($this->getOrder()->getStoreId());
            $method->fetchTransactionInfo($this, $transactionId);
        }

        if ($this->getIsTransactionApproved()) {
            $message = $this->_appendTransactionToMessage(
                $transactionId,
                $this->prependMessage(__('Registered update about approved payment.'))
            );
            $this->updateBaseAmountPaidOnlineTotal($invoice);
            $this->setOrderStateProcessing($message);
        } elseif ($this->getIsTransactionDenied()) {
            $message = $this->_appendTransactionToMessage(
                $transactionId,
                $this->prependMessage(__('Registered update about denied payment.'))
            );
            $this->cancelInvoiceAndRegisterCancellation($invoice, $message);
        } else {
            $message = $this->_appendTransactionToMessage(
                $transactionId,
                $this->prependMessage(__('There is no update for the payment.'))
            );
            $this->setOrderStatePaymentReview($message, $transactionId);
        }

        return $this;
    }

    /**
     * Triggers invoice pay and updates base_amount_paid_online total.
     *
     * @param \Epicor\Customerconnect\Model\ArPayment\Order\Invoice|false $invoice
     */
    protected function updateBaseAmountPaidOnlineTotal($invoice)
    {
        if ($invoice instanceof Invoice) {
            $invoice->pay();
            $totals = $this->collectTotalAmounts($this->getOrder(), ['base_grand_total']);
            $this->setBaseAmountPaidOnline($totals['base_grand_total']);
            $this->getOrder()->addRelatedObject($invoice);
        }
    }

    /**
     * Sets order state to 'processing' with appropriate message
     *
     * @param \Magento\Framework\Phrase|string $message
     */
    protected function setOrderStateProcessing($message)
    {
        $this->getOrder()->setState(Order::STATE_PROCESSING)
            ->setStatus($this->getOrder()->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING))
            ->addStatusHistoryComment($message);
    }

    /**
     * Cancel invoice and register order cancellation
     *
     * @param Invoice|false $invoice
     * @param string $message
     */
    protected function cancelInvoiceAndRegisterCancellation($invoice, $message)
    {
        if ($invoice instanceof Invoice) {
            $invoice->cancel();
            $this->getOrder()->addRelatedObject($invoice);
        }
        $this->getOrder()->registerCancellation($message, false);
    }

    /**
     * Sets order state status to 'payment_review' with appropriate message
     *
     * @param string $message
     * @param int|null $transactionId
     */
    protected function setOrderStatePaymentReview($message, $transactionId)
    {
        if ($this->getOrder()->getState() != Order::STATE_PAYMENT_REVIEW) {
            $this->getOrder()->setState(Order::STATE_PAYMENT_REVIEW)
                ->addStatusHistoryComment($message);
            if ($this->getIsFraudDetected()) {
                $this->getOrder()->setStatus(Order::STATUS_FRAUD);
            }
            if ($transactionId) {
                $this->setLastTransId($transactionId);
            }
        } else {
            $this->getOrder()->addStatusHistoryComment($message);
        }
    }

    /**
     * Authorize payment either online or offline (process auth notification)
     * Updates transactions hierarchy, if required
     * Prevents transaction double processing
     * Updates payment totals, updates order status and adds proper comments
     *
     * @param bool $isOnline
     * @param float $amount
     *
     * @return $this
     */
    public function authorize($isOnline, $amount)
    {
       // return $this->orderPaymentProcessor->authorize($this, $isOnline, $amount);
        
         // check for authorization amount to be equal to grand total
        /**
         * @var $payment Payment
         */
//        $this->setShouldCloseParentTransaction(false);
//        $isSameCurrency = $this->isSameCurrency();
//        if (!$isSameCurrency || !$this->isCaptureFinal($amount)) {
//            $this->setIsFraudDetected(true);
//        }
//
//        // update totals
//        $amount = $this->formatAmount($amount, true);
//        $this->setBaseAmountAuthorized($amount);
//
//
//        $message = __('Ordered amount of %1', $amount);
//        // update transactions, order state and add comments
//        $transaction = $this->addTransaction(Transaction::TYPE_AUTH);
//        $message = $this->prependMessage($message);
//        $this->addTransactionCommentsToOrder($transaction, $message);
//        $orderdata  = $this->getOrder()->setTransaction($transaction);
//        $this->setOrder($orderdata);
        return $this;
    }

    /**
     * Void payment either online or offline (process void notification)
     * NOTE: that in some cases authorization can be voided after a capture. In such case it makes sense to use
     *       the amount void amount, for informational purposes.
     * Updates payment totals, updates order status and adds proper comments
     *
     * @param bool $isOnline
     * @param float $amount
     * @param string $gatewayCallback
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _void($isOnline, $amount = null, $gatewayCallback = 'void')
    {
        $order = $this->getOrder();
        $authTransaction = $this->getAuthorizationTransaction();
      
        $this->setShouldCloseParentTransaction(true);

        // attempt to void
        if ($isOnline) {
            $method = $this->getMethodInstance();
            $method->setStore($order->getStoreId());
            $method->{$gatewayCallback}($this);
        }
       

        // if the authorization was untouched, we may assume voided amount = order grand total
        // but only if the payment auth amount equals to order grand total
        if ($authTransaction &&
            $order->getBaseGrandTotal() == $this->getBaseAmountAuthorized() &&
            0 == $this->getBaseAmountCanceled()
        ) {
            if ($authTransaction->canVoidAuthorizationCompletely()) {
                $amount = (double)$order->getBaseGrandTotal();
            }
        }

        if ($amount) {
            $amount = $this->formatAmount($amount);
        }

        // update transactions, order state and add comments
        $transaction = $this->addTransaction(Transaction::TYPE_VOID, null, true);
        $message = $this->hasMessage() ? $this->getMessage() : __('Voided authorization.');
        $message = $this->prependMessage($message);
        if ($amount) {
            $message .= ' ' . __('Amount: %1.', $this->formatPrice($amount));
        }
        $message = $this->_appendTransactionToMessage($transaction, $message);
        $this->setOrderStateProcessing($message);
        $order->setDataChanges(true);

        return $this;
    }

  

    /**
     */
    public function addTransactionCommentsToOrder($transaction, $message)
    {
        $order = $this->getOrder();
        $message = $this->_appendTransactionToMessage($transaction, $message);
        $order->addStatusHistoryComment($message);
    }

    /**
     * Import details data of specified transaction
     *
     * @param Transaction $transactionTo
     * @return $this
     */
    public function importTransactionInfo(Transaction $transactionTo)
    {
        $method = $this->getMethodInstance();
        $method->setStore(
            $this->getOrder()->getStoreId()
        );
        $data = $method->fetchTransactionInfo(
            $this,
            $transactionTo->getTxnId()
        );
        if ($data) {
            $transactionTo->setAdditionalInformation(
                Transaction::RAW_DETAILS,
                $data
            );
        }

        return $this;
    }

    /**
     * Totals updater utility method
     * Updates self totals by keys in data array('key' => $delta)
     *
     * @param array $data
     * @return void
     */
    protected function _updateTotals($data)
    {
        foreach ($data as $key => $amount) {
            if (null !== $amount) {
                $was = $this->getDataUsingMethod($key);
                $this->setDataUsingMethod($key, $was + $amount);
            }
        }
    }

    /**
     * Append transaction ID (if any) message to the specified message
     *
     * @param Transaction|null $transaction
     * @param string $message
     * @return string
     */
    protected function _appendTransactionToMessage($transaction, $message)
    {
        if ($transaction) {
            $txnId = is_object($transaction) ? $transaction->getHtmlTxnId() : $transaction;
            $message .= ' ' . __('Transaction ID: "%1"', $txnId);
        }

        return $message;
    }

    /**
     * Prepend a "prepared_message" that may be set to the payment instance before, to the specified message
     * Prepends value to the specified string or to the comment of specified order status history item instance
     *
     * @param string|\Epicor\Customerconnect\Model\ArPayment\Order\Status\History $messagePrependTo
     * @return string|\Epicor\Customerconnect\Model\ArPayment\Order\Status\History
     */
    public function prependMessage($messagePrependTo)
    {
        $preparedMessage = $this->getPreparedMessage();
        if ($preparedMessage) {
            if (
                is_string($preparedMessage)
                || $preparedMessage instanceof \Magento\Framework\Phrase
            ) {
                return $preparedMessage . ' ' . $messagePrependTo;
            } elseif (is_object(
                    $preparedMessage
                ) && $preparedMessage instanceof \Epicor\Customerconnect\Model\ArPayment\Order\Status\History
            ) {
                $comment = $preparedMessage->getComment() . ' ' . $messagePrependTo;
                $preparedMessage->setComment($comment);

                return $comment;
            }
        }

        return $messagePrependTo;
    }

    /**
     * Round up and cast specified amount to float or string
     *
     * @param string|float $amount
     * @param bool $asFloat
     * @return string|float
     */
    public function formatAmount($amount, $asFloat = false)
    {
        $amount = $this->priceCurrency->round($amount);

        return !$asFloat ? (string)$amount : $amount;
    }

    /**
     * Format price with currency sign
     * @param float $amount
     * @return string
     */
    public function formatPrice($amount)
    {
        return $this->getOrder()->getBaseCurrency()->formatTxt($amount);
    }



    /**
     * Decide whether authorization transaction may close (if the amount to capture will cover entire order)
     *
     * @param float $amountToCapture
     * @return bool
     */
    public function isCaptureFinal($amountToCapture)
    {
        $total = $this->getOrder()->getBaseTotalDue();

        return $this->formatAmount($total, true) == $this->formatAmount($amountToCapture, true);
    }

    /**
     * Check whether payment currency corresponds to order currency
     *
     * @return bool
     */
    public function isSameCurrency()
    {
        return !$this->getCurrencyCode() || $this->getCurrencyCode() == $this->getOrder()->getBaseCurrencyCode();
    }

    /**
     * Additional transaction info setter
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    public function setTransactionAdditionalInfo($key, $value)
    {
        $this->transactionAdditionalInfo[$key] = $value;
    }

    /**
     * Additional transaction info setter
     *
     * @return array
     */
    public function getTransactionAdditionalInfo()
    {
        return $this->transactionAdditionalInfo;
    }

    /**
     * Reset transaction additional info property
     *
     * @return $this
     */
    public function resetTransactionAdditionalInfo()
    {
        $this->transactionAdditionalInfo = [];

        return $this;
    }

    /**
     * Prepare credit memo
     *
     * @param $amount
     * @param $baseGrandTotal
     * @param false|Invoice $invoice
     * @return mixed
     */
    protected function prepareCreditMemo($amount, $baseGrandTotal, $invoice)
    {
         return true;
      
    }


    /**
     * Return invoice model for transaction
     *
     * @param string $transactionId
     * @return Invoice|false
     */
    protected function _getInvoiceForTransactionId($transactionId)
    {
        foreach ($this->getOrder()->getInvoiceCollection() as $invoice) {
            if ($invoice->getTransactionId() == $transactionId) {
                $invoice->load($invoice->getId());

                // to make sure all data will properly load (maybe not required)
                return $invoice;
            }
        }
        foreach ($this->getOrder()->getInvoiceCollection() as $invoice) {
            if ($invoice->getState() == \Epicor\Customerconnect\Model\ArPayment\Order\Invoice::STATE_OPEN && $invoice->load(
                    $invoice->getId()
                )
            ) {
                $invoice->setTransactionId($transactionId);

                return $invoice;
            }
        }

        return false;
    }


    //@codeCoverageIgnoreStart

    /**
     * Returns account_status
     *
     * @return string
     */
    public function getAccountStatus()
    {
        return $this->getData(OrderPaymentInterface::ACCOUNT_STATUS);
    }

    /**
     * Returns additional_data
     *
     * @return string
     */
    public function getAdditionalData()
    {
        return $this->getData(OrderPaymentInterface::ADDITIONAL_DATA);
    }

    /**
     * Returns address_status
     *
     * @return string
     */
    public function getAddressStatus()
    {
        return $this->getData(OrderPaymentInterface::ADDRESS_STATUS);
    }

    /**
     * Returns amount_authorized
     *
     * @return float
     */
    public function getAmountAuthorized()
    {
        return $this->getData(OrderPaymentInterface::AMOUNT_AUTHORIZED);
    }

    /**
     * Returns amount_canceled
     *
     * @return float
     */
    public function getAmountCanceled()
    {
        return $this->getData(OrderPaymentInterface::AMOUNT_CANCELED);
    }

    /**
     * Returns amount_ordered
     *
     * @return float
     */
    public function getAmountOrdered()
    {
        return $this->getData(OrderPaymentInterface::AMOUNT_ORDERED);
    }

    /**
     * Returns amount_paid
     *
     * @return float
     */
    public function getAmountPaid()
    {
        return $this->getData(OrderPaymentInterface::AMOUNT_PAID);
    }

    /**
     * Returns amount_refunded
     *
     * @return float
     */
    public function getAmountRefunded()
    {
        return $this->getData(OrderPaymentInterface::AMOUNT_REFUNDED);
    }

    /**
     * Returns anet_trans_method
     *
     * @return string
     */
    public function getAnetTransMethod()
    {
        return $this->getData(OrderPaymentInterface::ANET_TRANS_METHOD);
    }

    /**
     * Returns base_amount_authorized
     *
     * @return float
     */
    public function getBaseAmountAuthorized()
    {
        return $this->getData(OrderPaymentInterface::BASE_AMOUNT_AUTHORIZED);
    }

    /**
     * Returns base_amount_canceled
     *
     * @return float
     */
    public function getBaseAmountCanceled()
    {
        return $this->getData(OrderPaymentInterface::BASE_AMOUNT_CANCELED);
    }

    /**
     * Returns base_amount_ordered
     *
     * @return float
     */
    public function getBaseAmountOrdered()
    {
        return $this->getData(OrderPaymentInterface::BASE_AMOUNT_ORDERED);
    }

    /**
     * Returns base_amount_paid
     *
     * @return float
     */
    public function getBaseAmountPaid()
    {
        return $this->getData(OrderPaymentInterface::BASE_AMOUNT_PAID);
    }

    /**
     * Returns base_amount_paid_online
     *
     * @return float
     */
    public function getBaseAmountPaidOnline()
    {
        return $this->getData(OrderPaymentInterface::BASE_AMOUNT_PAID_ONLINE);
    }

    /**
     * Returns base_amount_refunded
     *
     * @return float
     */
    public function getBaseAmountRefunded()
    {
        return $this->getData(OrderPaymentInterface::BASE_AMOUNT_REFUNDED);
    }

    /**
     * Returns base_amount_refunded_online
     *
     * @return float
     */
    public function getBaseAmountRefundedOnline()
    {
        return $this->getData(OrderPaymentInterface::BASE_AMOUNT_REFUNDED_ONLINE);
    }

    /**
     * Returns base_shipping_amount
     *
     * @return float
     */
    public function getBaseShippingAmount()
    {
        return $this->getData(OrderPaymentInterface::BASE_SHIPPING_AMOUNT);
    }

    /**
     * Returns base_shipping_captured
     *
     * @return float
     */
    public function getBaseShippingCaptured()
    {
        return $this->getData(OrderPaymentInterface::BASE_SHIPPING_CAPTURED);
    }

    /**
     * Returns base_shipping_refunded
     *
     * @return float
     */
    public function getBaseShippingRefunded()
    {
        return $this->getData(OrderPaymentInterface::BASE_SHIPPING_REFUNDED);
    }

    /**
     * Returns cc_approval
     *
     * @return string
     */
    public function getCcApproval()
    {
        return $this->getData(OrderPaymentInterface::CC_APPROVAL);
    }

    /**
     * Returns cc_avs_status
     *
     * @return string
     */
    public function getCcAvsStatus()
    {
        return $this->getData(OrderPaymentInterface::CC_AVS_STATUS);
    }

    /**
     * Returns cc_cid_status
     *
     * @return string
     */
    public function getCcCidStatus()
    {
        return $this->getData(OrderPaymentInterface::CC_CID_STATUS);
    }

    /**
     * Returns cc_debug_request_body
     *
     * @return string
     */
    public function getCcDebugRequestBody()
    {
        return $this->getData(OrderPaymentInterface::CC_DEBUG_REQUEST_BODY);
    }

    /**
     * Returns cc_debug_response_body
     *
     * @return string
     */
    public function getCcDebugResponseBody()
    {
        return $this->getData(OrderPaymentInterface::CC_DEBUG_RESPONSE_BODY);
    }

    /**
     * Returns cc_debug_response_serialized
     *
     * @return string
     */
    public function getCcDebugResponseSerialized()
    {
        return $this->getData(OrderPaymentInterface::CC_DEBUG_RESPONSE_SERIALIZED);
    }

    /**
     * Returns cc_exp_month
     *
     * @return string
     */
    public function getCcExpMonth()
    {
        return $this->getData(OrderPaymentInterface::CC_EXP_MONTH);
    }

    /**
     * Returns cc_exp_year
     *
     * @return string
     */
    public function getCcExpYear()
    {
        return $this->getData(OrderPaymentInterface::CC_EXP_YEAR);
    }

    /**
     * Returns cc_last_4
     *
     * @return string
     */
    public function getCcLast4()
    {
        return $this->getData(OrderPaymentInterface::CC_LAST_4);
    }

    /**
     * Returns cc_number_enc
     *
     * @return string
     */
    public function getCcNumberEnc()
    {
        return $this->getData(OrderPaymentInterface::CC_NUMBER_ENC);
    }

    /**
     * Returns cc_owner
     *
     * @return string
     */
    public function getCcOwner()
    {
        return $this->getData(OrderPaymentInterface::CC_OWNER);
    }

    /**
     * Returns cc_secure_verify
     *
     * @return string
     */
    public function getCcSecureVerify()
    {
        return $this->getData(OrderPaymentInterface::CC_SECURE_VERIFY);
    }

    /**
     * Returns cc_ss_issue
     *
     * @return string
     * @deprecated 100.1.0 unused
     */
    public function getCcSsIssue()
    {
        return $this->getData(OrderPaymentInterface::CC_SS_ISSUE);
    }

    /**
     * Returns cc_ss_start_month
     *
     * @return string
     * @deprecated 100.1.0 unused
     */
    public function getCcSsStartMonth()
    {
        return $this->getData(OrderPaymentInterface::CC_SS_START_MONTH);
    }

    /**
     * Returns cc_ss_start_year
     *
     * @return string
     * @deprecated 100.1.0 unused
     */
    public function getCcSsStartYear()
    {
        return $this->getData(OrderPaymentInterface::CC_SS_START_YEAR);
    }

    /**
     * Returns cc_status
     *
     * @return string
     */
    public function getCcStatus()
    {
        return $this->getData(OrderPaymentInterface::CC_STATUS);
    }

    /**
     * Returns cc_status_description
     *
     * @return string
     */
    public function getCcStatusDescription()
    {
        return $this->getData(OrderPaymentInterface::CC_STATUS_DESCRIPTION);
    }

    /**
     * Returns cc_trans_id
     *
     * @return string
     */
    public function getCcTransId()
    {
        return $this->getData(OrderPaymentInterface::CC_TRANS_ID);
    }

    /**
     * Returns cc_type
     *
     * @return string
     */
    public function getCcType()
    {
        return $this->getData(OrderPaymentInterface::CC_TYPE);
    }

    /**
     * Returns echeck_account_name
     *
     * @return string
     */
    public function getEcheckAccountName()
    {
        return $this->getData(OrderPaymentInterface::ECHECK_ACCOUNT_NAME);
    }

    /**
     * Returns echeck_account_type
     *
     * @return string
     */
    public function getEcheckAccountType()
    {
        return $this->getData(OrderPaymentInterface::ECHECK_ACCOUNT_TYPE);
    }

    /**
     * Returns echeck_bank_name
     *
     * @return string
     */
    public function getEcheckBankName()
    {
        return $this->getData(OrderPaymentInterface::ECHECK_BANK_NAME);
    }

    /**
     * Returns echeck_routing_number
     *
     * @return string
     */
    public function getEcheckRoutingNumber()
    {
        return $this->getData(OrderPaymentInterface::ECHECK_ROUTING_NUMBER);
    }

    /**
     * Returns echeck_type
     *
     * @return string
     */
    public function getEcheckType()
    {
        return $this->getData(OrderPaymentInterface::ECHECK_TYPE);
    }

    /**
     * Returns last_trans_id
     *
     * @return string
     */
    public function getLastTransId()
    {
        return $this->getData(OrderPaymentInterface::LAST_TRANS_ID);
    }

    /**
     * Returns method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->getData(OrderPaymentInterface::METHOD);
    }

    /**
     * Returns parent_id
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->getData(OrderPaymentInterface::PARENT_ID);
    }

    /**
     * Returns po_number
     *
     * @return string
     */
    public function getPoNumber()
    {
        return $this->getData(OrderPaymentInterface::PO_NUMBER);
    }

    /**
     * Returns protection_eligibility
     *
     * @return string
     */
    public function getProtectionEligibility()
    {
        return $this->getData(OrderPaymentInterface::PROTECTION_ELIGIBILITY);
    }

    /**
     * Returns quote_payment_id
     *
     * @return int
     */
    public function getQuotePaymentId()
    {
        return $this->getData(OrderPaymentInterface::QUOTE_PAYMENT_ID);
    }

    /**
     * Returns shipping_amount
     *
     * @return float
     */
    public function getShippingAmount()
    {
        return $this->getData(OrderPaymentInterface::SHIPPING_AMOUNT);
    }

    /**
     * Returns shipping_captured
     *
     * @return float
     */
    public function getShippingCaptured()
    {
        return $this->getData(OrderPaymentInterface::SHIPPING_CAPTURED);
    }

    /**
     * Returns shipping_refunded
     *
     * @return float
     */
    public function getShippingRefunded()
    {
        return $this->getData(OrderPaymentInterface::SHIPPING_REFUNDED);
    }

    /**
     * {@inheritdoc}
     */
    public function setParentId($id)
    {
        return $this->setData(OrderPaymentInterface::PARENT_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseShippingCaptured($baseShippingCaptured)
    {
        return $this->setData(OrderPaymentInterface::BASE_SHIPPING_CAPTURED, $baseShippingCaptured);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingCaptured($shippingCaptured)
    {
        return $this->setData(OrderPaymentInterface::SHIPPING_CAPTURED, $shippingCaptured);
    }

    /**
     * {@inheritdoc}
     */
    public function setAmountRefunded($amountRefunded)
    {
        return $this->setData(OrderPaymentInterface::AMOUNT_REFUNDED, $amountRefunded);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseAmountPaid($baseAmountPaid)
    {
        return $this->setData(OrderPaymentInterface::BASE_AMOUNT_PAID, $baseAmountPaid);
    }

    /**
     * {@inheritdoc}
     */
    public function setAmountCanceled($amountCanceled)
    {
        return $this->setData(OrderPaymentInterface::AMOUNT_CANCELED, $amountCanceled);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseAmountAuthorized($baseAmountAuthorized)
    {
        return $this->setData(OrderPaymentInterface::BASE_AMOUNT_AUTHORIZED, $baseAmountAuthorized);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseAmountPaidOnline($baseAmountPaidOnline)
    {
        return $this->setData(OrderPaymentInterface::BASE_AMOUNT_PAID_ONLINE, $baseAmountPaidOnline);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseAmountRefundedOnline($baseAmountRefundedOnline)
    {
        return $this->setData(OrderPaymentInterface::BASE_AMOUNT_REFUNDED_ONLINE, $baseAmountRefundedOnline);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseShippingAmount($amount)
    {
        return $this->setData(OrderPaymentInterface::BASE_SHIPPING_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingAmount($amount)
    {
        return $this->setData(OrderPaymentInterface::SHIPPING_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setAmountPaid($amountPaid)
    {
        return $this->setData(OrderPaymentInterface::AMOUNT_PAID, $amountPaid);
    }

    /**
     * {@inheritdoc}
     */
    public function setAmountAuthorized($amountAuthorized)
    {
        return $this->setData(OrderPaymentInterface::AMOUNT_AUTHORIZED, $amountAuthorized);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseAmountOrdered($baseAmountOrdered)
    {
        return $this->setData(OrderPaymentInterface::BASE_AMOUNT_ORDERED, $baseAmountOrdered);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseShippingRefunded($baseShippingRefunded)
    {
        return $this->setData(OrderPaymentInterface::BASE_SHIPPING_REFUNDED, $baseShippingRefunded);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingRefunded($shippingRefunded)
    {
        return $this->setData(OrderPaymentInterface::SHIPPING_REFUNDED, $shippingRefunded);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseAmountRefunded($baseAmountRefunded)
    {
        return $this->setData(OrderPaymentInterface::BASE_AMOUNT_REFUNDED, $baseAmountRefunded);
    }

    /**
     * {@inheritdoc}
     */
    public function setAmountOrdered($amountOrdered)
    {
        return $this->setData(OrderPaymentInterface::AMOUNT_ORDERED, $amountOrdered);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseAmountCanceled($baseAmountCanceled)
    {
        return $this->setData(OrderPaymentInterface::BASE_AMOUNT_CANCELED, $baseAmountCanceled);
    }

    /**
     * {@inheritdoc}
     */
    public function setQuotePaymentId($id)
    {
        return $this->setData(OrderPaymentInterface::QUOTE_PAYMENT_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setAdditionalData($additionalData)
    {
        return $this->setData(OrderPaymentInterface::ADDITIONAL_DATA, $additionalData);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcExpMonth($ccExpMonth)
    {
        return $this->setData(OrderPaymentInterface::CC_EXP_MONTH, $ccExpMonth);
    }

    /**
     * {@inheritdoc}
     * @deprecated 100.1.0 unused
     */
    public function setCcSsStartYear($ccSsStartYear)
    {
        return $this->setData(OrderPaymentInterface::CC_SS_START_YEAR, $ccSsStartYear);
    }

    /**
     * {@inheritdoc}
     */
    public function setEcheckBankName($echeckBankName)
    {
        return $this->setData(OrderPaymentInterface::ECHECK_BANK_NAME, $echeckBankName);
    }

    /**
     * {@inheritdoc}
     */
    public function setMethod($method)
    {
        return $this->setData(OrderPaymentInterface::METHOD, $method);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcDebugRequestBody($ccDebugRequestBody)
    {
        return $this->setData(OrderPaymentInterface::CC_DEBUG_REQUEST_BODY, $ccDebugRequestBody);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcSecureVerify($ccSecureVerify)
    {
        return $this->setData(OrderPaymentInterface::CC_SECURE_VERIFY, $ccSecureVerify);
    }

    /**
     * {@inheritdoc}
     */
    public function setProtectionEligibility($protectionEligibility)
    {
        return $this->setData(OrderPaymentInterface::PROTECTION_ELIGIBILITY, $protectionEligibility);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcApproval($ccApproval)
    {
        return $this->setData(OrderPaymentInterface::CC_APPROVAL, $ccApproval);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcLast4($ccLast4)
    {
        return $this->setData(OrderPaymentInterface::CC_LAST_4, $ccLast4);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcStatusDescription($description)
    {
        return $this->setData(OrderPaymentInterface::CC_STATUS_DESCRIPTION, $description);
    }

    /**
     * {@inheritdoc}
     */
    public function setEcheckType($echeckType)
    {
        return $this->setData(OrderPaymentInterface::ECHECK_TYPE, $echeckType);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcDebugResponseSerialized($ccDebugResponseSerialized)
    {
        return $this->setData(OrderPaymentInterface::CC_DEBUG_RESPONSE_SERIALIZED, $ccDebugResponseSerialized);
    }

    /**
     * {@inheritdoc}
     * @deprecated 100.1.0 unused
     */
    public function setCcSsStartMonth($ccSsStartMonth)
    {
        return $this->setData(OrderPaymentInterface::CC_SS_START_MONTH, $ccSsStartMonth);
    }

    /**
     * {@inheritdoc}
     */
    public function setEcheckAccountType($echeckAccountType)
    {
        return $this->setData(OrderPaymentInterface::ECHECK_ACCOUNT_TYPE, $echeckAccountType);
    }

    /**
     * {@inheritdoc}
     */
    public function setLastTransId($id)
    {
        return $this->setData(OrderPaymentInterface::LAST_TRANS_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcCidStatus($ccCidStatus)
    {
        return $this->setData(OrderPaymentInterface::CC_CID_STATUS, $ccCidStatus);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcOwner($ccOwner)
    {
        return $this->setData(OrderPaymentInterface::CC_OWNER, $ccOwner);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcType($ccType)
    {
        return $this->setData(OrderPaymentInterface::CC_TYPE, $ccType);
    }

    /**
     * {@inheritdoc}
     */
    public function setPoNumber($poNumber)
    {
        return $this->setData(OrderPaymentInterface::PO_NUMBER, $poNumber);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcExpYear($ccExpYear)
    {
        return $this->setData(OrderPaymentInterface::CC_EXP_YEAR, $ccExpYear);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcStatus($ccStatus)
    {
        return $this->setData(OrderPaymentInterface::CC_STATUS, $ccStatus);
    }

    /**
     * {@inheritdoc}
     */
    public function setEcheckRoutingNumber($echeckRoutingNumber)
    {
        return $this->setData(OrderPaymentInterface::ECHECK_ROUTING_NUMBER, $echeckRoutingNumber);
    }

    /**
     * {@inheritdoc}
     */
    public function setAccountStatus($accountStatus)
    {
        return $this->setData(OrderPaymentInterface::ACCOUNT_STATUS, $accountStatus);
    }

    /**
     * {@inheritdoc}
     */
    public function setAnetTransMethod($anetTransMethod)
    {
        return $this->setData(OrderPaymentInterface::ANET_TRANS_METHOD, $anetTransMethod);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcDebugResponseBody($ccDebugResponseBody)
    {
        return $this->setData(OrderPaymentInterface::CC_DEBUG_RESPONSE_BODY, $ccDebugResponseBody);
    }

    /**
     * {@inheritdoc}
     * @deprecated 100.1.0 unused
     */
    public function setCcSsIssue($ccSsIssue)
    {
        return $this->setData(OrderPaymentInterface::CC_SS_ISSUE, $ccSsIssue);
    }

    /**
     * {@inheritdoc}
     */
    public function setEcheckAccountName($echeckAccountName)
    {
        return $this->setData(OrderPaymentInterface::ECHECK_ACCOUNT_NAME, $echeckAccountName);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcAvsStatus($ccAvsStatus)
    {
        return $this->setData(OrderPaymentInterface::CC_AVS_STATUS, $ccAvsStatus);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcNumberEnc($ccNumberEnc)
    {
        return $this->setData(OrderPaymentInterface::CC_NUMBER_ENC, $ccNumberEnc);
    }

    /**
     * {@inheritdoc}
     */
    public function setCcTransId($id)
    {
        return $this->setData(OrderPaymentInterface::CC_TRANS_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setAddressStatus($addressStatus)
    {
        return $this->setData(OrderPaymentInterface::ADDRESS_STATUS, $addressStatus);
    }

  
    /**
     * Sets whether transaction is pending
     *
     * @param bool|int $flag
     * @return $this
     */
    public function setIsTransactionPending($flag)
    {
        $this->setData('is_transaction_pending', (bool)$flag);

        return $this;
    }

    /**
     * Whether transaction is pending
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsTransactionPending()
    {
        return (bool)$this->getData('is_transaction_pending');
    }

    /**
     * Sets whether fraud was detected
     *
     * @param bool|int $flag
     * @return $this
     */
    public function setIsFraudDetected($flag)
    {
        $this->setData('is_fraud_detected', (bool)$flag);

        return $this;
    }

    /**
     * Whether fraud was detected
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsFraudDetected()
    {
        return (bool)$this->getData('is_fraud_detected');
    }

    /**
     * Sets whether should close parent transaction
     *
     * @param int|bool $flag
     * @return $this
     */
    public function setShouldCloseParentTransaction($flag)
    {
        $this->setData('should_close_parent_transaction', (bool)$flag);

        return $this;
    }

    /**
     * Whether should close parent transaction
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getShouldCloseParentTransaction()
    {
        return (bool)$this->getData('should_close_parent_transaction');
    }

    

    //@codeCoverageIgnoreEnd

    /**
     * Collects order invoices totals by provided keys.
     * Returns result as {key: amount}.
     *
     * @param Order $order
     * @param array $keys
     * @return array
     */
    private function collectTotalAmounts(Order $order, array $keys)
    {
        $result = array_fill_keys($keys, 0.00);
        $invoiceCollection = $order->getInvoiceCollection();
        /** @var Invoice $invoice */
        foreach ($invoiceCollection as $invoice) {
            foreach ($keys as $key) {
                $result[$key] += $invoice->getData($key);
            }
        }

        return $result;
    }
}
