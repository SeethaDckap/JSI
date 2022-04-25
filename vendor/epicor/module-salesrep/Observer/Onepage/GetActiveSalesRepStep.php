<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Observer\Onepage;

class GetActiveSalesRepStep extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\Registry
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->salesRepCheckoutHelper;
        /* @var $helper Epicor_Salesrep_Helper_Checkout */

        if ($helper->isChooseContactEnabled()) {
            $step = $observer->getEvent()->getStep();
            /* @var $step Varien_Object */

            $step->setStep('salesrep_contact');
        }
    }

}