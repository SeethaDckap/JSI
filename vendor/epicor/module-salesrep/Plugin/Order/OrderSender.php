<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Plugin\Order;


class OrderSender
{

    

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    protected $customerCustomerFactory;


    public function __construct(
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        
        $this->customerSessionFactory = $customerSessionFactory;
        $this->checkoutSession = $checkoutSession;
        $this->customerCustomerFactory = $customerCustomerFactory;
    }
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function beforeSend(
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $subject,
        \Magento\Sales\Model\Order $order,
        $forceSyncMode = false
    ) {
        $customerSession = $this->customerSessionFactory->create();
        $customer = $customerSession->getCustomer();
        $quote = $this->checkoutSession->getQuote();

        /* @var $customer Epicor_Comm_Model_Customer */
      
        if ($customer->isSalesRep()) {
            $customerId = $quote->getEccSalesrepChosenCustomerId();
            if ($customerId) {
                $salesRepCustomer = $this->customerCustomerFactory->create()->load($customerId);
                $email = $salesRepCustomer->getEmail();
                $order->setCustomerEmail($email);
                
            } else {
                $customerInfo = unserialize($quote->getEccSalesrepChosenCustomerInfo());
                
                if (isset($customerInfo['email'])) {
                    $email = $customerInfo['email']; 
                    $order->setCustomerEmail($email);
                }
            }
        }            
    }
    
}