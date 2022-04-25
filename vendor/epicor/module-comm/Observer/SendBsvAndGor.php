<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class SendBsvAndGor extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
//send BSV
        $this->generic->setSkipEvent(true);
        /* @var $order Mage_Sales_Model_Order */
        $customer = $this->customerCustomerFactory->create()->load($order->getCustomerId());
        $customerGroupId = $customer->getGroupId();
        $this->_bsv->setQuote($order);
        $this->_bsv->setCustomer($customer);
        $this->_bsv->setPromotions(false);
        if ($order->getPayment()->getMethod()) {
            $magentoCode = $this->salesOrderPayment->load($order->getId(), 'parent_id')->getMethod();
            $erpCode = $this->commErpMappingPayment->load($magentoCode, 'magento_code')->getErpCode();
        }
        $this->_bsv->setPaymentMethod($erpCode);
        if ($this->_bsv->sendMessage()) {
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
        $order->save();
        #Mage::getSingleton('core/session')->unsErpQuoteNumber();
        $this->registry->unregister('SkipEvent');
    }

}