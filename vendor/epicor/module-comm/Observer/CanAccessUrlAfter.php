<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class CanAccessUrlAfter extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $url = $observer->getEvent()->getUrl();

        $transport = $observer->getEvent()->getTransport();
        /* @var $transport Varien_Object */

        $commHelper = $this->commHelper->create();
        /* @var $commHelper Epicor_Comm_Helper_Data */

        if (!empty($url) && !$commHelper->canCustomerAccessUrl($url)) {
            $transport->setAllowed(false);
        }

        if (strpos($url, 'cart/csvupload') !== false && $commHelper->isFunctionalityDisabledForCustomer('cart')) {
            $transport->setAllowed(false);
        }

//
//  Needs to be checked by Gareth James as this is causing a redirect loop.
//           
//        if (strpos($url, 'store/selector') !== false) {
//            $helper = Mage::helper('epicor_comm');
//            /* @var $helper Epicor_Comm_Helper_Data */
//
//            $stores = $helper->getBrandSelectStores();
//            
//            if(count($stores) == 1) {
//                $transport->setAllowed(false);
//            }
//        }
    }

}