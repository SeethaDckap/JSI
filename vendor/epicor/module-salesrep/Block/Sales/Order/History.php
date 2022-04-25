<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Sales\Order;


/**
 * SalesRep Order history block override
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class History extends \Epicor\Common\Block\Order\History
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $salesResourceModelOrderCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $salesOrderConfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        array $data = [])
    {

        $this->customerSession = $customerSession;
        $this->salesResourceModelOrderCollectionFactory = $orderCollectionFactory;
        $this->salesOrderConfig = $orderConfig;
        parent::__construct($context, $orderCollectionFactory, $customerSession, $orderConfig, $data);

        $customer = $this->customerSession->getCustomer();
        /* @var $customer \Epicor\Comm\Model\Customer */

        if ($customer->isSalesRep()) {

            $orders = $this->salesResourceModelOrderCollectionFactory->create();
            /* @var $orders \Magento\Sales\Model\ResourceModel\Order\Collection */

            $orders->addFieldToSelect('*')
                ->addFieldToFilter('state', array('in' => $this->salesOrderConfig->getVisibleOnFrontStatuses()))
                ->setOrder('created_at', 'desc');

            $salesRepId = $customer->getId();

            $orders->addFieldToFilter(array('customer_id' => 'customer_id', 'ecc_salesrep_customer_id' => 'ecc_salesrep_customer_id'), array('customer_id' => $salesRepId, 'ecc_salesrep_customer_id' => $salesRepId));

            $this->setOrders($orders);
        }
    }
    
    public function getOrders()
    {
       
         $customer = $this->customerSession->getCustomer();
        /* @var $customer \Epicor\Comm\Model\Customer */

        if ($customer->isSalesRep()) {

            $orders = $this->salesResourceModelOrderCollectionFactory->create();
            /* @var $orders \Magento\Sales\Model\ResourceModel\Order\Collection */

            $orders->addFieldToSelect('*')
                ->setOrder('created_at', 'desc');
            $orders->addFieldToSelect('*')
                ->setOrder('created_at', 'desc');
 
            $salesRepId = $customer->getId();

            $orders->addFieldToFilter(array('customer_id' => 'customer_id', 'ecc_salesrep_customer_id' => 'ecc_salesrep_customer_id'), array('customer_id' => $salesRepId, 'ecc_salesrep_customer_id' => $salesRepId));
            $this->setOrders($orders);
            
        
        return $orders;
        }else{
            return parent::getOrders();
        }
    }

    /**
     * @param object $order
     * @return string
     */
    public function getViewUrl($order)
    {
        return $this->getUrl('salesrep/order/view', ['order_id' => $order->getId()]);
    }

}
