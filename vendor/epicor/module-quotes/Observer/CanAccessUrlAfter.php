<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Observer;
//class CanAccessUrlAfter extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
class CanAccessUrlAfter extends AbstractObserver
{
    /**
     * @var \Epicor\Quotes\Helper\Data
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $urlModuleInfo = $observer->getEvent()->getUrlModuleInfo();

        if ($urlModuleInfo['module'] == 'Epicor_Quotes') {

            $transport = $observer->getEvent()->getTransport();
            /* @var $transport Varien_Object */

            $helper = $this->quotesHelper;
            /* @var $helper Epicor_Quotes_Helper_Data */

            if (!$helper->isQuotesEnabledForCustomer()) {
                $transport->setAllowed(false);
            }
        }
    }

}