<?php
/**
 * Copyright © 2020 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message\Upload;

use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\ShipmentFactory;

/**
 * Response SOU - Sales order update
 *
 * If the status of a sales order originating from ECC changes, then notify ECC.
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Sou extends \Epicor\Comm\Model\Message\Upload
{

    const ADD = 0;
    const SUBSTRACT = 1;
    const REVERT = 2;
    const ONLINE = 'online';
    const OFFLINE = 'offline';

    /**
     * @var Shipment
     */
    private $preparedShipment;

    /**
     * @var bool
     */
    private $canShip = false;

    /**
     * @var bool
     */
    private $newItem = false;

    /**
     * @var array
     */
    private $shipItems;

    /**
     * @var array
     */
    private $trackingNumbers = [];

    /**
     * @var array
     */
    private $noInvoice = [];

    /**
     * @var array
     */
    private $itemShipmentNumber;

    /**
     *  @var \Magento\Sales\Model\Order
     */
    protected $_order;
    protected $_origOrderItemQty = array();

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $salesOrderFactory;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\OrderstatusFactory
     */
    protected $commErpMappingOrderstatusFactory;

    /**
     * @var \Magento\Sales\Model\Order\Invoice\ItemFactory
     */
    protected $salesOrderInvoiceItemFactory;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\ItemFactory
     */
    protected $salesOrderShipmentItemFactory;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $salesOrderItemFactory;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\TrackFactory
     */
    protected $salesOrderShipmentTrackFactory;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo\ItemFactory
     */
    protected $salesOrderCreditmemoItemFactory;

    /**
     * @var \Magento\Sales\Model\Service\OrderService
     */
    protected $salesOrderServiceFactory;

    /**
     * @var ShipmentFactory
     */
    protected $shipmentFactory;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * @var \Magento\Sales\Model\Order\CreditmemoFactory
     */
    protected $creditmemoFactory;

    /**
     * @var  Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    protected $invoiceEmailSender;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var \Epicor\Comm\Model\Customer\Erpaccount
     */
    protected $epicorCommModelCustomerErpaccount;

    protected $_orderItemName;

    /**
     * @var  Magento\Sales\Model\Order\Email\Sender\ShipmentSender
     */
    protected $shipmentEmailSender;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Epicor\Comm\Api\Data\SouInvoiceInterface
     */
    private $souInvoice;

    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Magento\Sales\Model\OrderFactory $salesOrderFactory,
        \Epicor\Comm\Model\Erp\Mapping\OrderstatusFactory $commErpMappingOrderstatusFactory,
        \Magento\Sales\Model\Order\Invoice\ItemFactory $salesOrderInvoiceItemFactory,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Sales\Model\Order\Shipment\ItemFactory $salesOrderShipmentItemFactory,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Sales\Model\Order\ItemFactory $salesOrderItemFactory,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $salesOrderShipmentTrackFactory,
        ShipmentFactory $shipmentFactory,
        \Magento\Sales\Model\Order\Creditmemo\ItemFactory $salesOrderCreditmemoItemFactory,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender  $invoiceEmailSender,
        \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagement,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Epicor\Comm\Api\Data\SouInvoiceInterface $souInvoice,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->salesOrderFactory = $salesOrderFactory;
        $this->salesOrderServiceFactory = $orderService;
        $this->commErpMappingOrderstatusFactory = $commErpMappingOrderstatusFactory;
        $this->salesOrderInvoiceItemFactory = $salesOrderInvoiceItemFactory;
        $this->invoiceService = $invoiceService;
        $this->transactionFactory = $transactionFactory;
        $this->salesOrderShipmentItemFactory = $salesOrderShipmentItemFactory;
        $this->salesOrderItemFactory = $salesOrderItemFactory;
        $this->salesOrderShipmentTrackFactory = $salesOrderShipmentTrackFactory;
        $this->salesOrderCreditmemoItemFactory = $salesOrderCreditmemoItemFactory;
        $this->shipmentFactory = $shipmentFactory;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->invoiceEmailSender = $invoiceEmailSender;
        $this->customerSession = $context->getCustomerSession();
        $this->epicorCommModelCustomerErpaccount = $context->getEpicorCommModelCustomerErpaccount();
        $this->invoiceManagementInterface = $invoiceManagement;
        $this->shipmentEmailSender = $context->getShipmentEmailSender();
        $this->invoiceRepository = $invoiceRepository;
        $this->customerFactory = $customerFactory;
        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->setConfigBase('epicor_comm_field_mapping/sou_mapping/');
        $this->setMessageType('SOU');
        $this->setLicenseType(array('Consumer', 'Customer'));
        $this->setMessageCategory(self::MESSAGE_CATEGORY_ORDER);
        $this->setStatusCode(self::STATUS_SUCCESS);
        $this->setStatusDescription('');
        $this->souInvoice = $souInvoice;
    }

    /**
     * Process the upload request
     */
    public function processAction()
    {
        $this->erpData = $this->getRequest()->getOrder();

        $brand = $this->getRequest()->getBrand();
        if (empty($brand) || !$brand->getCompany())
            //M1 > M2 Translation Begin (Rule p2-6.5)
            //$brand = $this->getHelper()->getStoreBranding(Mage::app()->getDefaultStoreView()->getId());
            $brand = $this->getHelper()->getStoreBranding($this->storeManager->getDefaultStoreView()->getId());
            //M1 > M2 Translation End

        $company = $brand->getCompany();

        if (!empty($company)) {
            $delimiter = $this->getHelper()->getUOMSeparator();
            $accountCode = $this->erpData->getAccountNumber();
            $this->erpData->setAccountNumber($company . $delimiter . $accountCode);
        }

        $orderNumber = $this->erpData->getOrderNumber();
        $accountNumber = $this->erpData->getAccountNumber();
        $orderReference = $this->erpData->getOrderReference();

        $statusCode = $this->erpData->getStatusCode();

        $this->setMessageSubject($orderNumber);

        if (empty($orderNumber)) {
            throw new \Exception('Order Number not defined in message', self::STATUS_GENERAL_ERROR);
        }

        if (empty($accountNumber)) {
            throw new \Exception('Account Number not defined in message', self::STATUS_GENERAL_ERROR);
        }

        if (empty($orderReference)) {
            throw new \Exception('Order Reference not defined in message', self::STATUS_GENERAL_ERROR);
        }

        if (empty($statusCode)) {
            throw new \Exception('Status Code not defined in message', self::STATUS_GENERAL_ERROR);
        }

        $this->_order = $this->salesOrderFactory->create()->load($orderNumber, 'ecc_erp_order_number');

        if ($this->_order->isObjectNew()) {
            throw new \Exception("Order $orderReference does not exist. ", self::STATUS_ORDER_NOT_FOUND);
        }
        $storePrefix = $this->scopeConfig->getValue('epicor_comm_enabled_messages/gor_request/gor_order_prefix',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_order->getStoreId());
        $expectedOrderReference = $storePrefix.$this->_order->getIncrementId();
        if ($orderReference != $expectedOrderReference) {
            throw new \Exception("Order reference $orderReference does not match order reference expected for order number $orderNumber. Order reference expected: " . $expectedOrderReference, self::STATUS_VALUES_DO_NOT_TALLY);
        }

        $orderStatusMapping = $this->commErpMappingOrderstatusFactory->create()->load($statusCode, 'code');
        /* @var $orderStatusMapping Epicor_Comm_Model_Erp_Mapping_Orderstatus */

        if ($orderStatusMapping && !$orderStatusMapping->isObjectNew()) {
            if ($this->erpData->getDispatch()) {
                $this->_processDispatch();
            }
            $mapping = $this->getHelper()->getOrderMapping($statusCode);
            $this->_order->setManualStatus($mapping->getStatus());
            $this->_order->setManualState($mapping->getState());
            $this->_order->save();
        } else {
            throw new \Exception('Order Status "' . $statusCode . '" unknown', self::STATUS_UNKNOWN);
        }
    }

    /**
     * Processes the dispatch information received
     */
    protected function _processDispatch()
    {
        $state = $this->_order->getState();
        if (!$this->_order->isCanceled() && $state !== \Magento\Sales\Model\Order::STATE_COMPLETE && $state !== \Magento\Sales\Model\Order::STATE_CLOSED) {
            // loop through lines and build up, shipment / invoice / credit memo

            $linesGroup = $this->erpData->getDispatch()->getLines();
            $payment = $this->erpData->getDispatch()->getPayment();
            $grandTotal = ($payment) ? $payment->getGrandTotalInc() : 0;
            $this->setPaymentCollectedCode($this->getHelper()->getPaymentMethodMap($this->_order->getPayment()->getMethod())->getPaymentCollected());

            $orderItems = array();
            foreach ($this->_order->getAllItems() as $orderItem) {
                $orderItems[$orderItem->getId()] = $orderItem->getId();
                $this->_orderItemName[$orderItem->getId()] = $orderItem->getName();
            }
            $allItems=[];
            if (!empty($linesGroup)) {

                $sumQtyTracker = [];
                $lines = $linesGroup->getasarrayLine();
                foreach ($lines as $key => $line) {
                    $lineNum = $line->getData('_attributes')->getNumber();
                    if (!empty($sumQtyTracker[$lineNum])) {
                        $sumQty = $sumQtyTracker[$lineNum]['qty'] + $line->getQuantity();
                        $sumLineValue = $sumQtyTracker[$lineNum]['lineValue'] + $line->getLineValue();
                        $sumLineValueInc = $sumQtyTracker[$lineNum]['lineValueInc'] + $line->getLineValueInc();
                        $line->setQuantity($sumQty);
                        $line->setLineValue($sumLineValue);
                        $line->setLineValueInc($sumLineValueInc);
                        unset($lines[$sumQtyTracker[$lineNum]['idx']]);
                    }
                    $sumQtyTracker[$lineNum]['idx'] = $key;
                    $sumQtyTracker[$lineNum]['qty'] = $line->getQuantity();
                    $sumQtyTracker[$lineNum]['lineValue'] = $line->getLineValue();
                    $sumQtyTracker[$lineNum]['lineValueInc'] = $line->getLineValueInc();
                }

                $shipItems = array();
                $cancelItems = array();
                $refundItems = array();
                $invoiceItems = array();
                $creditItems = array();

                // note for invoices we need to set all lines to 0 then add quantity for the items we want to invoice

                foreach ($lines as $line) {

                    $lineNum = $line->getData('_attributes')->getNumber();
                    $action = $line->getData('_attributes')->getAction();

                    if (in_array($lineNum, $orderItems)) {
                        $allItems[$lineNum] = $lineNum;
                        switch ($action) {
                            case 'R' :
                                $refundItems[] = $line;
                                break;
                            case 'C' :
                                $cancelItems[] = $line;
                                break;
                            case 'I' :
                                $invoiceItems[] = $line;
                                break;
                            case 'SI' :
                                $invoiceItems[] = $line;
                                $shipItems[] = $line;
                                break;
                            case 'S' :
                                $shipItems[] = $line;
                                break;
                            case 'A' :
                                $creditItems[] = $line;
                                break;
                        }
                    }
                }
            }
            if (!empty($shipItems)) {
                $resultShipment = $this->raiseShipment($shipItems);
            }

            if ((!empty($invoiceItems) || (count($allItems) == 0 && $grandTotal > 0)) && (in_array($this->getPaymentCollectedCode(), array('D', 'A')))) {
                $canInvoice = true;
                $omitLines = [];
                if($action === 'SI'){
                    $canInvoice = $resultShipment['canInvoice'];
                    if(isset($resultShipment['omitLines'])){
                        $omitLines = $resultShipment['omitLines'];
                    }
                }
                if($canInvoice){
                    $eccErpInvoiceNumber = $payment ? $payment->getInvoiceNumber() : null;
                    $this->_raiseInvoice($invoiceItems, $action, $eccErpInvoiceNumber, $omitLines);
                }
            }
            $this->shipItems();
            if (!empty($refundItems) || (count($allItems) == 0 && $grandTotal < 0)) {
                $this->_raiseRefund($refundItems);
            }

            if (!empty($cancelItems)) {
                // NOTE no way of cancelling items really in magento at present, without cancelling whole order/line!!!
                $this->_raiseRefund($cancelItems, 'offline');
            }

            if (!empty($creditItems)) {
                $this->_raiseRefund($creditItems, 'offline');
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('Unable to update order. The order state is set to: ' . $this->_order->getState()));
        }
    }

    /**
     * Update Order Item Quanties
     * @param array $invoiceItems
     */
    protected function _alterOrderQuanties($invoiceItems, $modifier = self::ADD)
    {
        foreach ($invoiceItems as $invoiceItem) {
            $line = $this->_order->getItemById($invoiceItem->getData('_attributes')->getNumber());
            /* @var $line Mage_Sales_Model_Order_Item */
            $qty = $line->getQtyOrdered();
            switch ($modifier) {
                case self::ADD:
                    if (array_key_exists($line->getId(), $this->_origOrderItemQty)) {
                        $qty += $invoiceItem->getQuantity();
                    } else {
                        if ($line->getQtyOrdered() - $line->getQtyInvoiced() < $invoiceItem->getQuantity()) {
                            $this->_origOrderItemQty[$line->getId()] = $line->getQtyOrdered();
                            $qty = $line->getQtyInvoiced() + $invoiceItem->getQuantity();
                        }
                    }
                    break;
                case self::REVERT:
                    $qty = array_key_exists($line->getId(), $this->_origOrderItemQty) ? $this->_origOrderItemQty[$line->getId()] : $qty;
                    break;
            }
            $line->setQtyOrdered($qty);
            $line->save();
        }
    }

    protected function _raiseInvoice($invoiceItems, $action, $eccErpInvoiceNumber, $omitLines)
    {
        try {
            $visibleItems = $this->_order->getVisibleItems();
            if (!$this->_order->canInvoice()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Cannot create an invoice for this order. '));
            }
            //M1 > M2 Translation Begin (Rule p2-1)
            //$invoice = Mage::getModel('sales/service_order', $this->_order)->prepareInvoice();
            $isNew = true;
            $invoiceExists = $this->_order->getInvoiceCollection()->getItemsByColumnValue('ecc_erp_invoice_number', $eccErpInvoiceNumber);
            if(!is_null($eccErpInvoiceNumber) && !empty($invoiceExists) && $invoiceExists[0]->getId()){
                $invoice = $this->invoiceRepository->get($invoiceExists[0]->getId());
                $isNew = false;
            }else{
                $invoice = $this->invoiceService->prepareInvoice($this->_order);
                $invoice->setEccErpInvoiceNumber($eccErpInvoiceNumber);
            }
            //M1 > M2 Translation End
            /* @var $invoice Mage_Sales_Model_Order_Invoice */

            $invoice->getItemsCollection()->clear();

            foreach ($invoiceItems as $invoiceItem) {
                if($isNew && (empty($omitLines) || in_array($invoiceItem->getShipmentNumber(), $omitLines))){
                    $invItem = $this->salesOrderInvoiceItemFactory->create();
                    /* @var $invItem Mage_Sales_Model_Order_Invoice_Item */
                    $invItem->setOrderItemId($invoiceItem->getData('_attributes')->getNumber());
                    $invItem->setData('qty', $invoiceItem->getQuantity());
                    $invItem->setPrice($invoiceItem->getPrice());
                    $invItem->setPriceInclTax($invoiceItem->getPriceInc());
                    $invItem->setRowTotal($invoiceItem->getLineValue());
                    $invItem->setRowTotalInclTax($invoiceItem->getLineValueInc());
                    if ($invoiceItem->getDiscount() instanceof \Magento\Framework\DataObject) {
                        if ($invoiceItem->getDiscount()->getValue()) {
                            $invItem->setDiscountAmount($invoiceItem->getDiscount()->getValue());
                        } else {
                            $invItem->setDiscountAmount(0);
                        }
                    }
                    $invItem->setProductId(3);
                    $invItem->setSku($invoiceItem->getProductCode());
                    $invItem->setUom($invoiceItem->getUnitOfMeasureCode());
                    $invItem->setName($this->_orderItemName[$invItem->getOrderItemId()]);

                    $invoice->addItem($invItem);
                }
            }

            $payment = $this->erpData->getDispatch()->getPayment();

            $grandTotal = $invoice->getGrandTotal();
            $goodsTotal = $invoice->getSubtotal();
            $tax = $invoice->getTaxAmount();
            $shipping = $invoice->getShippingAmount();
            $shippingTax = $invoice->getShippingTaxAmount();

            if ($payment && $payment->getGrandTotalInc() > 0) {
                if ($payment->getGrandTotalInc()) {
                    $grandTotal = $payment->getGrandTotalInc() > 0 ? $payment->getGrandTotalInc() : 0;
                    if ($payment->getGoodsTotal()) {
                        $goodsTotal = $payment->getGoodsTotal();
                    }
                }
                if ($payment->getCarriageAmount()) {
                    $shipping = $payment->getCarriageAmount();
                    if ($payment->getCarriageAmountInc()) {
                        $shippingTax = $payment->getCarriageAmountInc() - $shipping;
                    }
                }
                $tax = $grandTotal > 0 ? $grandTotal - $goodsTotal - $shipping : 0;
            }

            $invoice->setGrandTotal($grandTotal);
            $invoice->setBaseGrandTotal($grandTotal);
            $invoice->setSubtotal($goodsTotal);
            $invoice->setBaseSubtotal($goodsTotal);
            $invoice->setTaxAmount($tax);
            $invoice->setBaseTaxAmount($tax);
            $invoice->setShippingAmount($shipping);
            $invoice->setBaseShippingAmount($shipping);
            $invoice->setShippingTaxAmount($shippingTax);
            $invoice->setBaseShippingTaxAmount($shippingTax);

            $order = $invoice->getOrder();
            if ($order->getBaseShippingAmount() < $order->getBaseShippingInvoiced() - $order->getBaseShippingRefunded() - $order->getBaseShippingTaxRefunded()) {
                $order->setBaseShippingAmount($order->getBaseShippingInvoiced() - $order->getBaseShippingRefunded() - $order->getBaseShippingTaxRefunded());
                $order->setShippingAmount($order->getShippingInvoiced() - $order->getShippingRefunded() - $order->getShippingTaxRefunded());

                $order->setBaseGrandTotal($order->getBaseSubtotal() + $order->getBaseShippingAmount());
                $order->setGrandTotal($order->getSubtotal() + $order->getShippingAmount());
            }

            $captureMethod = \Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE;
            if ($this->getPaymentCollectedCode() == 'D' && $grandTotal > 0) {
                $captureMethod = \Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE;
            }
            $invoice->setRequestedCaptureCase($captureMethod);

            if($isNew){
                $invoice->register();
            }else{
                $invoice->save();
            }

            /* @var $transactionSave Mage_Core_Model_Resource_Transaction */
            $transactionSave = $this->transactionFactory->create()
                ->addObject($invoice)
                ->addObject($invoice->getOrder());

            $transactionSave->save();
            if ($invoice->getState() != \Magento\Sales\Model\Order\Invoice::STATE_PAID) {
                $invoice->delete();
                throw new \Magento\Framework\Exception\LocalizedException(__('Payment Failed'));
            }

            if(in_array($action, ['I', 'SI']) && $isNew){
                if($eccErpInvoiceNumber){
                    $invoice->setEccErpInvoiceNumber($eccErpInvoiceNumber);
                }
                $this->_registry->register("sou_shipment_email",true);
                $this->souInvoice->setSouInvoiceEmailFlag(true);
                $this->sendCustomerInvoiceEmail($invoice, $this->_order->getCustomerId(), $this->_order->getCustomerIsGuest());
                $this->souInvoice->setSouInvoiceEmailFlag(false);
                $this->_registry->unregister("sou_shipment_email");
            }

        } catch (Mage_Core_Exception $e) {
            #$this->_alterOrderQuanties($invoiceItems, self::REVERT);
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Prepare shipment
     *
     * @param \Magento\Sales\Model\Order $order
     * @return \Magento\Sales\Model\Order\Shipment|false
     */
    protected function _prepareShipment($order,$items)
    {
        $shipment = $this->shipmentFactory->create($order,$items);
        if (!$shipment->getTotalQty()) {
            return false;
        }

        return $shipment->register();
    }

    /**
     * @param array $shipItems
     * @return array|false[]
     */
    protected function raiseShipment($shipItems)
    {
        try {
            $this->canShip = false;

            $this->trackingNumbers = [];
            $this->shipItems = $allItems = $this->itemShipmentNumber = [];

            $orderShipments = $this->_order->getShipmentsCollection();
            $shipmentCount = $orderShipments->count();

            if ($shipmentCount > 0) {
                foreach ($orderShipments as $orderShipment) {
                    if (is_array($orderShipment->getItemsCollection())) {
                        $_items = $orderShipment->getItemsCollection();
                    } else {
                        $_items = $orderShipment->getItemsCollection()->getItems();
                    }
                    foreach ($_items as $item) {
                        $allItems[$item->getOrderItemId()][$item->getEccErpShipmentNumber()] = $item;
                    }
                }
            }
            $this->noInvoice = [];
            $this->setTracking($shipItems, $allItems);
            if ($this->newItem === true) {
                return $this->createPreparedShipment();
            } else {
                return ['canInvoice' => false];
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @param array $shipItems
     * @param array $allItems
     */
    private function setTracking($shipItems, $allItems)
    {
        try {
            $this->newItem = false;
            $helper = $this->commMessagingHelper->create();
            foreach ($shipItems as $shipmentItem) {
                $trackingNum = $shipmentItem->getTrackingNumber();
                $orderItemId = $shipmentItem->getData('_attributes')->getNumber();
                $shipmentErpNumber = $shipmentItem->getShipmentNumber();
                if (isset($allItems[$orderItemId][$shipmentErpNumber]) &&
                    ($shipmentItem->getQuantity() != $allItems[$orderItemId][$shipmentErpNumber]->getQty())
                    && ($shipmentErpNumber)) {
                    $shipItem = $allItems[$orderItemId][$shipmentErpNumber];
                    $_shipment = $this->shipmentFactory->create($this->_order)->load($shipItem->getParentId());
                    $totalQty = $_shipment->getTotalQty() + $shipmentItem->getQuantity() - $shipItem->getQty();
                    $shipItem->setData('qty', $shipmentItem->getQuantity())->save();
                    $_shipment->setTotalQty($totalQty)->save();
                } elseif ((!isset($allItems[$orderItemId][$shipmentErpNumber])) || (!$shipmentErpNumber)) {
                    $this->newItem = true;
                    $this->shipItems[$orderItemId] = $shipmentItem->getQuantity();
                    $this->itemShipmentNumber[$orderItemId] = $shipmentErpNumber;
                    if (!isset($this->trackingNumbers[$trackingNum])) {
                        $this->trackingNumbers[$trackingNum] = array(
                            'url' => '',
                            'products' => array(),
                        );
                    }
                    $this->trackingNumbers[$trackingNum]['url'] = $shipmentItem->getTrackingUrl();
                    $this->trackingNumbers[$trackingNum]['methodCode'] = $shipmentItem->getMethodCode();
                    $this->trackingNumbers[$trackingNum]['products'][] = $helper
                            ->qtyRounding($shipmentItem->getQuantity()) . ' x ' . $shipmentItem->getProductCode();
                    array_push($this->noInvoice, $shipmentErpNumber);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @return array
     */
    private function createPreparedShipment()
    {
        $this->preparedShipment = $this->_prepareShipment($this->_order, $this->shipItems);
        if ($this->preparedShipment) {
            foreach ($this->preparedShipment->getItems() as $_shippedItem) {
                if ($this->isErpShipmentNumberSet($_shippedItem)) {
                    $eccErpShipmentNumber = $this->itemShipmentNumber[$_shippedItem->getOrderItemId()];
                    $_shippedItem->setEccErpShipmentNumber($eccErpShipmentNumber);
                }
            }
            foreach ($this->trackingNumbers as $trackingNumber => $tracking) {
                $track = $this->salesOrderShipmentTrackFactory->create();
                $track->setNumber($trackingNumber);
                $track->setCarrierCode($tracking['methodCode'] ?? "custom");
                $track->setTitle('Shipment Tracking Number');
                $track->setDescription(implode("\n", $tracking['products'] ?? []));
                $track->setUrl($tracking['url'] ?? '');
                $this->preparedShipment->addTrack($track);
            }
            $this->canShip = true;
        }
        return ['canInvoice' => true, 'omitLines' => $this->noInvoice];
    }

    /**
     * @param \Magento\Sales\Api\Data\ShipmentItemInterface $_shippedItem
     * @return bool
     */
    private function isErpShipmentNumberSet($_shippedItem)
    {
        return isset($this->itemShipmentNumber[$_shippedItem->getOrderItemId()])
            && is_null($_shippedItem->getEccErpShipmentNumber());
    }

    /**
     * @return void
     */
    private function shipItems()
    {
        if ($this->canShip && $this->preparedShipment instanceof Shipment) {
            try {
                $this->transactionFactory->create()
                    ->addObject($this->preparedShipment)
                    ->addObject($this->preparedShipment->getOrder())
                    ->save();
                $this->_registry->register("sou_shipment_email", true);
                $this->sendShipmentEmail(
                    $this->preparedShipment,
                    $this->_order->getCustomerId(),
                    $this->_order->getCustomerIsGuest()
                );
                $this->_registry->unregister("sou_shipment_email");
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }

    protected function _raiseRefund($refundItems, $type = self::ONLINE)
    {
        try {
            if (!$this->_order->canCreditmemo()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Cannot create a credit memo for this order. '));
            }
            $creditMemo = null;
            if ($type == self::ONLINE) {
                if ($this->_order->hasInvoices()) {
                    foreach ($this->_order->getInvoiceCollection() as $invoice) {
                        /* @var $invoice Mage_Sales_Model_Order_Invoice */
                        if ($invoice->canRefund()) {
                            //M1 > M2 Translation Begin (Rule p2-1)
                            //$creditMemo = Mage::getModel('sales/service_order', $this->_order)->prepareInvoiceCreditmemo($invoice);
                            $creditMemo = $this->creditmemoFactory->createByInvoice($invoice);
                            //M1 > M2 Translation End
                            break;
                        }
                    }
                }
            } else {
                //M1 > M2 Translation Begin (Rule p2-1)
                //$creditMemo = Mage::getModel('sales/service_order', $this->_order)->prepareCreditMemo();
                $creditMemo = $this->creditmemoFactory->createByOrder($this->_order);
                //M1 > M2 Translation End
            }
            /* @var $creditMemo Mage_Sales_Model_Order_Creditmemo */
            $creditMemo->getItemsCollection()->clear();

            foreach ($refundItems as $refundItem) {
                $invItem = $this->salesOrderCreditmemoItemFactory->create();
                /* @var $invItem Mage_Sales_Model_Order_Creditmemo_Item */
                $invItem->setOrderItemId($refundItem->getData('_attributes')->getNumber());
                $invItem->setData('qty', $refundItem->getQuantity());
                $invItem->setPrice($refundItem->getPrice());
                $invItem->setPriceInclTax($refundItem->getPriceInc());
                $invItem->setRowTotal($refundItem->getLineValue());
                $invItem->setRowTotalInclTax($refundItem->getLineValueInc());
                if ($refundItem->getDiscount() instanceof \Magento\Framework\DataObject) {
                    if ($refundItem->getDiscount()->getValue()) {
                        $invItem->setDiscountAmount($refundItem->getDiscount()->getValue());
                    } else {
                        $invItem->setDiscountAmount(0);
                    }
                }
                $creditMemo->addItem($invItem);
            }
            $transactionSave = $this->transactionFactory->create();

            $payment = $this->erpData->getDispatch()->getPayment();

            $grandTotal = 0;
            $goodsTotal = 0;
            $goodsTotalIncl = 0;
            $tax = 0;
            $shipping = 0;
            $shippingIncl = 0;
            $shippingTax = 0;


            if ($payment && $payment->getGrandTotalInc() < 0) {
                if ($payment->getGrandTotalInc()) {
                    $grandTotal = $payment->getGrandTotalInc() < 0 ? abs($payment->getGrandTotalInc()) : 0;
                    if ($payment->getGoodsTotal()) {
                        $goodsTotal = abs($payment->getGoodsTotal());
                        if ($payment->getGoodsTotalInc()) {
                            $goodsTotalIncl = abs($payment->getGoodsTotalInc());
                        }
                    }
                }
                if ($payment->getCarriageAmount()) {
                    $shipping = abs($payment->getCarriageAmount());
                    if ($payment->getCarriageAmountInc()) {
                        $shippingIncl = abs($payment->getCarriageAmountInc());
                        $shippingTax = $shippingIncl - $shipping;
                    }
                }
                $tax = $grandTotal > 0 ? $grandTotal - $goodsTotal - $shipping : 0;
            }

            $creditMemo->setGrandTotal($grandTotal);
            $creditMemo->setBaseGrandTotal($grandTotal);
            $creditMemo->setSubtotal($goodsTotal);
            $creditMemo->setBaseSubtotal($goodsTotal);
            $creditMemo->setSubtotalInclTax($goodsTotalIncl);
            $creditMemo->setBaseSubtotalInclTax($goodsTotalIncl);
            $creditMemo->setTaxAmount($tax);
            $creditMemo->setBaseTaxAmount($tax);
            $creditMemo->setShippingAmount($shipping);
            $creditMemo->setBaseShippingAmount($shipping);
            $creditMemo->setShippingInclTax($shippingIncl);
            $creditMemo->setBaseShippingInclTax($shippingIncl);
            $creditMemo->setShippingTaxAmount($shippingTax);
            $creditMemo->setBaseShippingTaxAmount($shippingTax);
            $subTotalIncl = $goodsTotalIncl + $shippingIncl;

//            if ($subTotalIncl < $goodsTotalIncl) {
//                $creditMemo->setBaseAdjustment($goodsTotalIncl - $subTotalIncl);
//                $creditMemo->setAdjustment($goodsTotalIncl - $subTotalIncl);
//            }
            $creditMemo->setRefundRequested(true);

            if ($type == self::OFFLINE || $goodsTotalIncl == 0) {
                $creditMemo->setOfflineRequested(true);
            }

            if ($creditMemo->getState() != \Magento\Sales\Model\Order\Creditmemo::STATE_REFUNDED) {
                $creditMemo->delete();
                throw new \Magento\Framework\Exception\LocalizedException(__('Refund Failed'));
            }
            $transactionSave
                ->addObject($creditMemo)
                ->addObject($this->_order)
                ->save();
        } catch (Mage_Core_Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    protected function sendShipmentEmail($shipment, $customerId, $isguest = false)
    {
        $sendShipment = $this->scopeConfig->getValue('epicor_comm_field_mapping/sou_mapping/send_shipment_email_for_b2c', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if($isguest){
            if($sendShipment){
                $this->shipmentEmailSender->send($shipment);
            }
            return;
        }
        //if sou enabled, for erp or for b2b or b2c
        $customer = $this->customerFactory->create()->load($customerId);
        if ($customer->isguest() && !$customer->getEccErpaccountId()) {
            if ($sendShipment) {
                $this->shipmentEmailSender->send($shipment);
            }
            return;
        }
        $erpAccount = $this->epicorCommModelCustomerErpaccount->load($this->erpData->getAccountNumber(), 'erp_code');
        $erpAccountType = strtolower($erpAccount->getAccountType());
        $erpSendSouShipmentOptions = $erpAccount->getSouShipmentOptions();
        $sendShipment = $this->scopeConfig->getValue('epicor_comm_field_mapping/sou_mapping/send_shipment_email_for_' . $erpAccountType, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        // if erp says to send shipment or erp set to global default and admin says to send shipment
        if ($erpSendSouShipmentOptions == '1' || $erpSendSouShipmentOptions == '' && $sendShipment) {
            $this->shipmentEmailSender->send($shipment);
        }
    }

    /**
     * send Customer Invoice Email
     */

    protected function sendCustomerInvoiceEmail($invoice, $customerId, $isguest = false)
    {
        $sendInvoice = $this->scopeConfig->getValue('epicor_comm_field_mapping/sou_mapping/send_invoice_email_for_b2c', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if($isguest){
            if($sendInvoice){
                $this->invoiceEmailSender->send($invoice);
            }
            return;
        }
        //if sou enabled, for erp or for b2b or b2c
        $customer = $this->customerFactory->create()->load($customerId);
        if ($customer->isguest() && !$customer->getEccErpaccountId()) {
            if ($sendInvoice) {
                $this->invoiceEmailSender->send($invoice);
            }
            return;
        }
        $erpAccount = $this->epicorCommModelCustomerErpaccount->load($this->erpData->getAccountNumber(), 'erp_code');
        $erpAccountType = strtolower($erpAccount->getAccountType());
        $erpSendSouInvoiceOptions = $erpAccount->getSouInvoiceOptions();
        $sendInvoice = $this->scopeConfig->getValue('epicor_comm_field_mapping/sou_mapping/send_invoice_email_for_' . $erpAccountType, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        // if erp says to send invoice or erp set to global default and admin says to send invoice
        if ($erpSendSouInvoiceOptions == '1' || $erpSendSouInvoiceOptions == '' && $sendInvoice) {
            $this->invoiceEmailSender->send($invoice);
        }
    }

//    protected function getRefundTotalsForInvoices()
//    {
//        $invoiceQtysRefunded = array();
//        foreach ($this->_order->getCreditmemosCollection() as $createdCreditmemo) {
//            if ($createdCreditmemo->getState() != Mage_Sales_Model_Order_Creditmemo::STATE_CANCELED) {
//                if (!isset($invoiceQtysRefunded[$createdCreditmemo->getInvoiceId()])) {
//                    $invoiceQtysRefunded[$createdCreditmemo->getInvoiceId()] = array();
//                }
//
//                foreach ($createdCreditmemo->getAllItems() as $createdCreditmemoItem) {
//                    $orderItemId = $createdCreditmemoItem->getOrderItem()->getId();
//                    if (isset($invoiceQtysRefunded[$createdCreditmemo->getInvoiceId()][$orderItemId])) {
//                        $invoiceQtysRefunded[$createdCreditmemo->getInvoiceId()][$orderItemId] += $createdCreditmemoItem->getQty();
//                    } else {
//                        $invoiceQtysRefunded[$createdCreditmemo->getInvoiceId()][$orderItemId] = $createdCreditmemoItem->getQty();
//                    }
//                }
//            }
//        }
//
//        return $invoiceQtysRefunded;
//    }
}
