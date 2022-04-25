<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Helper;

use Epicor\Comm\Model\Message\Request\Gor as GorMessage;
use Magento\Downloadable\Model\Product\Type as DownLoadableProduct;
use Magento\Sales\Model\Order as OrderStatus;

/**
 * Instead of calling SendOrderToERP event
 * Send BSV AND GOR by calling by Helper
 *
 */
class BsvAndGor extends \Epicor\Comm\Helper\Data
{

    /**
     * @var \Epicor\Comm\Model\Message\Request\GorFactory
     */
    protected $_bsv;

    /**
     * @var \Epicor\Comm\Model\Message\Request\GorFactory
     */
    protected $_gor;

    /**
     * @var \Epicor\Comm\Model\Message\Request\GorFactory
     */
    protected $commMessageRequestGorFactory;

    /**
     * @var \Epicor\Comm\Model\Message\Request\BsvFactory
     */
    protected $commMessageRequestBsvFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Erp\Mapping\Payment\CollectionFactory
     */
    protected $commResourceErpMappingPaymentCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Order\PaymentFactory
     */
    protected $salesOrderPayment;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\PaymentFactory
     */
    protected $commErpMappingPayment;

    /**
     * @var \Magento\Framework\Session\GenericFactory
     */
    protected $generic;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    public function __construct(
        \Epicor\Comm\Helper\Context $context,
        \Epicor\Comm\Model\Message\Request\GorFactory $commMessageRequestGorFactory,
        \Epicor\Comm\Model\Message\Request\BsvFactory $commMessageRequestBsvFactory,
        \Epicor\Comm\Model\ResourceModel\Erp\Mapping\Payment\CollectionFactory $commResourceErpMappingPaymentCollectionFactory,
        \Magento\Sales\Model\Order\PaymentFactory $salesOrderPayment,
        \Epicor\Comm\Model\Erp\Mapping\PaymentFactory $commErpMappingPayment,
        \Magento\Framework\App\ResourceConnection $resource
    )
    {
        $this->commMessageRequestGorFactory = $commMessageRequestGorFactory;
        $this->commMessageRequestBsvFactory = $commMessageRequestBsvFactory;
        $this->commResourceErpMappingPaymentCollectionFactory = $commResourceErpMappingPaymentCollectionFactory;
        $this->salesOrderPayment = $salesOrderPayment;
        $this->commErpMappingPayment = $commErpMappingPayment;
        $this->generic = $context->getGenericFactory();
        $this->url = $context->getUrlBuilder();
        $this->_resource = $resource;
        parent::__construct($context);
    }

    /**
     *
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     */
    public function SendOrderToErp($order)
    {
        if(!$this->_gor){
            $this->_gor = $this->commMessageRequestGorFactory->create();
            /* @var $this ->_gor \Epicor\Comm\Model\Message\Request\Gor */
        }

        //do not send order to erp if GOR not active, set GOR order status as required
        if (!$this->_gor->isActive()) {
            $this->orderNotSentCheck($order);
            return $order;
        }

        if (!$this->registry->registry('processingSendOrderToERP')) {
            $this->registry->register('processingSendOrderToERP', true);

            if ($this->scopeConfig->isSetFlag('epicor_comm_enabled_messages/gor_request/gor_response_condition', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ||
                $this->registry->registry("offline_order_{$order->getId()}")
            ) {
                $this->_bsv = $this->commMessageRequestBsvFactory->create();
                /* @var $this ->\bsv \Epicor\Comm\Model\Message\Request\Bsv */

                if ($this->registry->registry('SkipEvent') !== true &&
                    $this->_gor->isActive() &&
                    $this->_bsv->isActive()
                ) {

                    $order->load($order->getId());
                    $valid_order_statuses = explode(',', $this->scopeConfig->getValue('epicor_comm_enabled_messages/gor_request/valid_order_statuses', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));

                    $gor_trigger = $this->commResourceErpMappingPaymentCollectionFactory->create()->addFieldToFilter('magento_code', $order->getPayment()->getMethod())
                        ->addFieldToSelect('gor_trigger')
                        ->getFirstItem()
                        ->getGorTrigger();

                    if ($order->getArpaymentsQuote()) {
                        return false;
                    }

                    if (!$this->isGorStatusSent($order) && $this->isOnlyDownloadableItemsInOrder($order)) {
                        $valid_order_statuses[] = $order->getStatus();
                    }
                    // check gor trigger to see if gor is to be sent
                    if ($order->getEccGorSent() == \Epicor\Comm\Model\Message\Request\Gor::GOR_STATUS_NOT_SENT && in_array($order->getStatus(), $valid_order_statuses)) {
                        $paymentTitle = $this->scopeConfig->getValue('payment/' . $order->getPayment(\Magento\Store\Model\ScopeInterface::SCOPE_STORE)->getMethod() . '/title');
                        switch ($gor_trigger) {
                            case 'any':
                                $this->sendBsvAndGor($order);
                                break;
                            case 'paid':
                                if ($order->getBaseTotalDue() == 0) {
                                    $this->sendBsvAndGor($order);
                                }
                                break;
                            case 'authorised';
                                $this->authorisedCheck($order);
                                break;
                            case 'paidauthorised';
                                if ($order->getBaseTotalDue() == 0) {
                                    $this->sendBsvAndGor($order);
                                } else {
                                    $this->authorisedCheck($order);
                                }
                                break;
                            case '':                                // if blank, assume gor trigger is 'paid'
                                if ($order->getBaseTotalDue() == 0) {
                                    $this->sendBsvAndGor($order);
                                }
                                $this->sendMagentoMessage("Payment Method : {$paymentTitle} does not have an Order trigger set", "Payment Method", \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL);
                                break;
                            default;
                                $this->sendMagentoMessage("Payment Method : {$paymentTitle} does not have an Order trigger set", "Payment Method", \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL);
                        }
                    }
                }
            } else { //Generate GOR as background task? : yes
                //Note: else function should not work with admin url.
                $url = $this->url->getUrl('comm/message/gor', array('id' => $order->getId()));
                $this->sendAsyncRequest($url);
                $this->checkoutSession->unsetData('ecc_customer_order_ref');
                $this->checkoutSession->unsetData('ecc_tax_exempt_reference');
            }
            $this->registry->unregister('processingSendOrderToERP');
        }

        $orderShippingAddress = $order->getShippingAddress();
        if ($orderShippingAddress && !is_null($orderShippingAddress) && $orderShippingAddress->getCustomerAddressId()) {
            $totalsData = $this->customerSessionFactory->create()->getBsvTriggerTotals();
            $totalsData['shipping_address'] = $orderShippingAddress->getCustomerAddressId();
            $this->customerSessionFactory->create()->setBsvTriggerTotals($totalsData);
        }

        return $order;
    }

    /**
     * @param $order
     * @return bool
     */
    private function isGorStatusSent($order): bool
    {
        return (int)$order->getEccGorSent() === GorMessage::GOR_STATUS_SENT;
    }

    /**
     * @param $order
     * @return bool
     */
    private function isOnlyDownloadableItemsInOrder($order): bool
    {
        $downLoadableItemsCount = 0;
        $onlyDownloadableItemsInOrder = true;
        foreach ($order->getAllItems() as $item) {
            if ($this->isTypeDownloadable($item)) {
                $downLoadableItemsCount++;
            } else {
                $onlyDownloadableItemsInOrder = false;
                break;
            }
        }

        return $downLoadableItemsCount > 0 && $onlyDownloadableItemsInOrder;
    }

    /**
     * @param $item
     * @return bool
     */
    private function isTypeDownloadable($item): bool
    {
        return $item->getProductType() === DownLoadableProduct::TYPE_DOWNLOADABLE;
    }

    /**
     * @param $customerSession
     * @param $addresses
     * @return bool|\Magento\Customer\Model\Address|mixed|null
     */
    public function getCustomerDefaultShippingAddress($customerSession, $addresses)
    {
        if (!$customerSession instanceof \Magento\Customer\Model\Session) {
            return false;
        }
        $guestAddress = null;
        if (is_array($addresses) && !$customerSession->isLoggedIn()) {
            $guestAddress = $this->getGuestAddress($addresses);

            return $guestAddress;
        }

        return $customerSession->getCustomer()->getDefaultShippingAddress();
    }

    /**
     * @param $addresses
     * @return mixed|null
     */
    public function getGuestAddress($addresses)
    {
        $guestAddress = null;
        if (is_array($addresses)) {
            $guestAddress = reset($addresses);
        }

        return $guestAddress;
    }

    /**
     *
     * @param \Magento\Sales\Model\Order $order
     */
    public function authorisedCheck($order)
    {
        if ($order->getPayment()->getBaseAmountAuthorized() >= $order->getPayment()->getBaseAmountOrdered() ||
            ( // SagePay Authenticated Check
                $order->getSagepayInfo() &&
                (in_array($order->getSagepayInfo()->getStatus(), array('OK', 'AUTHENTICATED', 'REGISTERED')) || in_array($order->getSagepayInfo()->getTxStateId(), array('14', '15', '16', '21')))
            )
        ) {
            $this->sendBsvAndGor($order);
        }
    }

    /**
     * Sent GOR and BSV
     * @param \Magento\Sales\Model\Order $order
     */
    public function sendBsvAndGor($order)
    {

        $this->generic->create()->setSkipEvent(true);
        /* @var $order \Magento\Sales\Model\Order */
        $customer = $this->customerCustomerFactory->create()->load($order->getCustomerId());
        $customerGroupId = $customer->getGroupId();
        $this->_bsv->setQuote($order);
        $this->_bsv->setCustomer($customer);
        $this->_bsv->setPromotions(false);
        if ($order->getPayment()->getMethod()) {
            $magentoCode = $this->salesOrderPayment->create()->load($order->getId(), 'parent_id')->getMethod();
            $erpCode = $this->commErpMappingPayment->create()->load($magentoCode, 'magento_code')->getErpCode();
        }
        $this->_bsv->setPaymentMethod($erpCode);

        $gorCount = $order->getEccGorSentCount();
        $retryCount = $this->scopeConfig->getValue('epicor_comm_enabled_messages/gor_request/retry_count', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?: 0;
        if ($retryCount && $this->registry->registry("isAdminGORForce") && $retryCount <= $gorCount) {
            $gorNewCount = 1;
        } else {
            $gorNewCount = $gorCount + 1;
        }

        if (!$this->registry->registry("isAdminGORForce") && !$this->isGorRetry($order)) {
            $order->setEccGorSent(\Epicor\Comm\Model\Message\Request\Gor::GOR_STATUS_ERROR_RETRY);
        } else {
            if ($this->_bsv->sendMessage()) {
                $order->setEccGorSentCount($gorNewCount);
                $gorFlowStart = true;
                $tableName = $this->_resource->getTableName('sales_order');
                $connection = $this->_resource->getConnection();

                try {
                    $fields = array('ecc_gor_flow');
                    $sql = $connection->select()
                        ->from($tableName, $fields)
                        ->where('entity_id IN(?)', $order->getEntityId());
                    $result = $connection->fetchCol($sql);
                    if (count($result) > 0) {
                        if ($result[0] == \Epicor\Comm\Model\Message\Request\Gor::GOR_FLOW_PROCESSING
                            || $result[0] == \Epicor\Comm\Model\Message\Request\Gor::GOR_FLOW_SUCCESS) {
                            $gorFlowStart = false;
                        }
                    }
                } catch (\Exception $e) {
                }

                if ($gorFlowStart == true) {
                    try {
                        $connection->update($tableName,
                            ['ecc_gor_flow' => \Epicor\Comm\Model\Message\Request\Gor::GOR_FLOW_PROCESSING],
                            ['entity_id = ?' => $order->getEntityId()]);
                    } catch (\Exception $e) {
                    }

                    $this->_gor->setOrder($order);
                    $this->_gor->setCustomer($customer);
                    $this->_gor->setPromotions(false);
                    $this->_gor->sendMessage();
                    if (!$this->_gor->getConnectionSuccessful()) {        // if not successful connection has timedout
                        $order->setEccGorSent(\Epicor\Comm\Model\Message\Request\Gor::GOR_STATUS_NOT_SENT);
                        $order->setEccGorMessage('GOR Failed -- Message Timed Out');
                    }
                    $this->registry->unregister('last_log');
                    $this->registry->register('last_log', $this->_gor->getLog());
                }
            } else {
                $this->registry->unregister('last_log');
                $this->registry->register('last_log', $this->_bsv->getLog());
                if ($this->_bsv->getConnectionSuccessful()) {
                    $order->setEccGorSent(\Epicor\Comm\Model\Message\Request\Gor::GOR_STATUS_ERROR);
                    $order->setEccGorMessage('BSV Failed -- ' . $this->_bsv->getStatusCode() . " : " . $this->_bsv->getStatusDescription());
                } else {
                    $order->setEccGorMessage('BSV Connection Failure');
                }
            }
        }
        $order->save();
        $this->registry->unregister('SkipEvent');
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    private function orderNotSentCheck(\Magento\Sales\Model\Order $order)
    {
        //only executed when gor is not active, so set order status to value in config

        //if gor sent variable not set ( ie when order created), use defaults
        if (!$order->getEccGorSent()) {
            $gorSendConfig = $this->scopeConfig->getValue(self::GOR_SENT_INDICATOR,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            if ($gorSendConfig == 'order_not_sent') {
                $order->setEccGorSent(\Epicor\Comm\Model\Message\Request\Gor::GOR_STATUS_NOT_SENT);
            } else {
                $order->setEccGorSent(\Epicor\Comm\Model\Message\Request\Gor::GOR_STATUS_NEVER_SEND);
                $order->setEccGorMessage('Never Send');
                $order->getResource()->saveAttribute($order, 'ecc_gor_message');
            }

            $order->getResource()->saveAttribute($order, 'ecc_gor_sent');
        }
    }
}
