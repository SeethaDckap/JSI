<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Observer;

use Epicor\Comm\Model\Queue\Entity\CaapInfoFactory;
use Magento\Framework\MessageQueue\PublisherInterface;

class SendInvoicesToERP extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    
     protected $commMessageRequestCaapFactory;
     
     protected $commonHelper;
     

     protected $scopeConfig;     
    
     protected $commResourceErpMappingPaymentCollectionFactory;
     
     protected $commHelper;
     
     protected $generic;
     
     protected $salesOrderPayment;
     
     protected $commErpMappingPayment;
     
     protected $customerCustomerFactory;
     
     protected $url;

    /**
     * @var PublisherInterface
     */
    private $messagePublisher;

    /**
     * @var CaapInfoFactory
     */
    private $caapInfoFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

     public function __construct(   
        \Epicor\Customerconnect\Model\Message\Request\CaapFactory $commMessageRequestCaapFactory,
        \Epicor\Common\Helper\DataFactory $commonHelper,
        \Epicor\Comm\Helper\DataFactory $commHelper,
        \Magento\Framework\Session\GenericFactory $generic,
        \Epicor\Comm\Model\ResourceModel\Erp\Mapping\Payment\CollectionFactory $commResourceErpMappingPaymentCollectionFactory,
        \Epicor\Customerconnect\Model\ArPayment\Order\PaymentFactory $salesOrderPayment,
        \Epicor\Comm\Model\Erp\Mapping\PaymentFactory $commErpMappingPayment,  
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Epicor\Common\Model\Url $url,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $registry,
        \Psr\Log\LoggerInterface $logger,
        PublisherInterface $publisher,
        CaapInfoFactory $caapInfoFactory
    ) {
         $this->registry = $registry;
         $this->scopeConfig = $scopeConfig;
         $this->commonHelper = $commonHelper;
         $this->generic = $generic;
         $this->commHelper = $commHelper;
         $this->commResourceErpMappingPaymentCollectionFactory = $commResourceErpMappingPaymentCollectionFactory;
         $this->salesOrderPayment = $salesOrderPayment;
         $this->customerCustomerFactory = $customerCustomerFactory;
         $this->commErpMappingPayment = $commErpMappingPayment;
         $this->commMessageRequestCaapFactory = $commMessageRequestCaapFactory;
         $this->url = $url;
         $this->messagePublisher = $publisher;
         $this->caapInfoFactory  = $caapInfoFactory;
         $this->logger           = $logger;

    }//end __construct()


    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->registry->registry('processingSendArOrderToERP')) {
            $this->registry->register('processingSendArOrderToERP', true);
            $helper = $this->commHelper->create();
            /* @var $helper \Epicor\Comm\Helper\Data */
            /*
             * $con = Mage::getStoreConfig('epicor_comm_enabled_messages/gor_request/gor_response_condition');
             */
            $all_orders = array();
            $order_data = $observer->getEvent()->getOrder();
           
            if(!empty($order_data) && $order_data!=null){
                $all_orders[] = $order_data;
            }else{
                 /* If order is multishipping order then get order id from order array from observer param */
                $orders = $observer->getEvent()->getData('orders');
                if(count($orders) >0){
                    $all_orders = $orders;
                }
            }
            
            foreach ($all_orders as $order) {
                if ($this->scopeConfig->isSetFlag('customerconnect_enabled_messages/CAAP_request/caap_response_condition', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
                    || $this->registry->registry("offline_arpaymentorders_{$order->getId()}")
                ) {
                        $this->_caap = $this->commMessageRequestCaapFactory->create();

                        if (
                            $this->registry->registry('SkipEvent') !== true &&
                            $this->_caap->isActive()
                        ) {
                            //$order = $observer->getEvent()->getOrder();

                            $order->load($order->getId());
                            $valid_order_statuses = explode(',', $this->scopeConfig->getValue('customerconnect_enabled_messages/CAAP_request/valid_order_statuses', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));

                            $gor_trigger = $this->commResourceErpMappingPaymentCollectionFactory->create()->addFieldToFilter('magento_code', $order->getPayment()->getMethod())
                                ->addFieldToSelect('gor_trigger')
                                ->getFirstItem()
                                ->getGorTrigger();
                            // check gor trigger to see if gor is to be sent  
                            if ($order->getEccCaapSent() == \Epicor\Customerconnect\Model\Message\Request\Caap::CAAP_STATUS_NOT_SENT &&
                                in_array($order->getStatus(), $valid_order_statuses)
                            ) {
                                $paymentTitle = $this->scopeConfig->getValue('payment/' . $order->getPayment(\Magento\Store\Model\ScopeInterface::SCOPE_STORE)->getMethod() . '/title');
                                switch ($gor_trigger) {
                                    case 'any':
                                        $this->sendCaap($order);
                                        break;
                                    case 'paid':
                                        if ($order->getBaseTotalDue() == 0) {
                                            $this->sendCaap($order);
                                        }
                                        break;
                                    case 'authorised';
                                        $this->sendCaap($order);
                                        break;
                                    case 'paidauthorised';
                                        if ($order->getBaseTotalDue() == 0) {
                                            $this->sendCaap($order);
                                        } else {
                                            $this->sendCaap($order);
                                        }
                                        break;
                                    case '':                                // if blank, assume gor trigger is 'paid'  
                                        if ($order->getBaseTotalDue() == 0) {
                                            $this->sendCaap($order);
                                        }
                                        $helper->sendMagentoMessage("Payment Method : {$paymentTitle} does not have an Order trigger set", "Payment Method", \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL);
                                        break;
                                    default;
                                        $helper->sendMagentoMessage("Payment Method : {$paymentTitle} does not have an Order trigger set", "Payment Method", \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL);
                                }
                            }
                        }
                        $this->registry->unregister('processingSendArOrderToERP');
                } else {
                    try {
                        // End Background CAAP With Magento Queue Mechanism.
                        /** @var \Epicor\Comm\Model\Queue\Entity\CaapInfo $caapInfoDataObject */
                        $caapInfoDataObject = $this->caapInfoFactory->create();
                        $caapInfoDataObject->setOrderId($order->getId());
                        $this->messagePublisher->publish('ecc.message.caap', $caapInfoDataObject);
                    } catch (\Exception $e) {
                        $this->logger->error(__('Please correct the data sent value.'));
                        $this->logger->critical($e);
                    }//end try
                }//end if
            }//end foreach
        }//end if

        return $this;

    }//end execite()

    public function authorisedCheck($order)
    {
        if ($order->getPayment()->getBaseAmountAuthorized() >= $order->getPayment()->getBaseAmountOrdered() ||
            ( // SagePay Authenticated Check
                $order->getSagepayInfo() &&
                (
                    in_array($order->getSagepayInfo()->getStatus(), array('OK', 'AUTHENTICATED', 'REGISTERED')) ||
                    in_array($order->getSagepayInfo()->getTxStateId(), array('14', '15', '16', '21'))
                )
            )
        ) {
            $this->sendCaap($order);
        }
    }

    public function sendCaap($order)
    {
        $this->generic->create()->setSkipEvent(true);
        $customer = $this->customerCustomerFactory->create()->load($order->getCustomerId());
        $customerGroupId = $customer->getGroupId();
        if ($order->getPayment()->getMethod()) {
            $magentoCode = $this->salesOrderPayment->create()->load($order->getId(), 'parent_id')->getMethod();
            $erpCode = $this->commErpMappingPayment->create()->load($magentoCode, 'magento_code')->getErpCode();
        }
        if ($this->_caap->isActive() && !$order->getEccCaapSent()) {
            $this->_caap->setOrder($order);
            $this->_caap->setCustomer($customer);
            $this->_caap->setPromotions(false);
            $this->_caap->sendMessage();
            $this->_caap->getLog()->setMessageSubject($customer->getEmail());
            if (!$this->_caap->getConnectionSuccessful()) {        // if not successful connection has timedout
                $order->setEccCaapSent(\Epicor\Customerconnect\Model\Message\Request\Caap::CAAP_STATUS_NOT_SENT);
                $order->setEccCaapMessage('CAAP Failed -- Message Timed Out');
                $this->_caapTimedOut = true;
            }
            $this->registry->unregister('last_log');
            $this->registry->register('last_log', $this->_caap->getLog());            
        } else {
            $this->registry->unregister('last_log');
            if ($this->_caap->getConnectionSuccessful()) {
                $order->setEccCaapSent(Epicor_Customerconnect_Model_Message_Request_Caap::CAAP_STATUS_ERROR);
                $order->setEccCaapMessage('CAAP Failed -- ' . $this->_caap->getStatusCode() );
            } else {
                $order->setEccCaapMessage('CAAP Connection Failure');
            }
        }
        $order->save();
        $this->registry->unregister('SkipEvent');
    }    

}