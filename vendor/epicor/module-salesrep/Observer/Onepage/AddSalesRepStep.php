<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Observer\Onepage;

class AddSalesRepStep extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\Registry
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->salesRepCheckoutHelper;
        /* @var $helper Epicor_Salesrep_Helper_Checkout */

        if ($helper->isChooseContactEnabled()) {
            $steps = $observer->getEvent()->getSteps();
            /* @var $steps Varien_Object */

            $newStep = array('salesrep_contact');

            $stepData = $steps->getSteps();

            $newSteps = array_merge($newStep, $stepData);

            $steps->setSteps($newSteps);
        }
    }

}