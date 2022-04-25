<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class SetCartErpAccount extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        /* @var $quote \Epicor\Comm\Model\Quote */

        $helper = $this->commHelper->create();
        /* @var $helper \Epicor\Comm\Helper\Data */

        $erpAccount = $helper->getErpAccountInfo();
        /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount */

        if (!$quote->getEccErpAccountId() || $erpAccount->getId() != $quote->getEccErpAccountId()) {
            $quote->setEccErpAccountId($erpAccount->getId());
        }
    }

}