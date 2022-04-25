<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class GetAccountDetails extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * run AST for get account details for a customer and 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getEvent()->getModel();
        $ast = $this->commMessageRequestAstFactory->create();
        /* @var $ast Epicor_Comm_Model_Message_Request_Ast */
        $ast->setCustomer($customer);
        $ast->sendMessage();
        return $this;
    }

}