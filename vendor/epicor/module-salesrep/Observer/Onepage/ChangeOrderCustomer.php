<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Observer\Onepage;

class ChangeOrderCustomer extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\Registry
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        /* @var $order \Magento\Sales\Model\Order */

        if ($order->isObjectNew()) {

            $customerSession = $this->customerSession;
            /* @var $customerSession \Magento\Customer\Model\Session */

            $customer = $customerSession->getCustomer();
            /* @var $customer \Epicor\Comm\Model\Customer */

            $helper = $this->commHelper;
            /* @var $helper \Epicor\Comm\Helper\Data */

            if ($customer->isSalesRep() && $helper->isMasquerading() && $order->getEccSalesrepChosenCustomerId()) {
                if ($order->getCustomerId() != $order->getEccSalesrepChosenCustomer()) {
                    $customerId = $order->getEccSalesrepChosenCustomerId();

                    $salesRepCustomer = $this->customerCustomerFactory->create()->load($customerId);
                    /* @var $salesRepCustomer \Epicor\Comm\Model\Customer */

                    $order->setCustomer($salesRepCustomer);
                    $order->setCustomerDob($salesRepCustomer->getDob());
                    $order->setCustomerEmail($salesRepCustomer->getEmail());
                    $order->setCustomerFirstname($salesRepCustomer->getFirstname());
                    $order->setCustomerGender($salesRepCustomer->getGender());
                    $order->setCustomerGroupId($salesRepCustomer->getGroupId());
                    $order->setCustomerId($salesRepCustomer->getId());
                    $order->setCustomerLastname($salesRepCustomer->getLastname());
                    $order->setCustomerMiddlename($salesRepCustomer->getMiddlename());
                    $order->setCustomerPrefix($salesRepCustomer->getPrefix());
                    $order->setCustomerSuffix($salesRepCustomer->getSuffix());
                }
            }
        }
    }

}