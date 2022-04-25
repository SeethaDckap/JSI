<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class CommsQuoteSaveAfter extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Quote save after - Triggers GQR
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $gqr = $this->commMessageRequestGqrFactory->create();
        /* @var $gqr Epicor_Comm_Model_Message_Request_Gqr */

        if (!$this->registry->registry('SkipEvent') && $gqr->isActive() && !$this->registry->registry('gqr-processing') && !$this->registry->registry('gqr-accept')) {
            $gqr->setQuote($observer->getEvent()->getQuote());
            $gqr->sendMessage();
            $observer->getEvent()->setQuote($gqr->getQuote());
        }

        return $this;
    }

}