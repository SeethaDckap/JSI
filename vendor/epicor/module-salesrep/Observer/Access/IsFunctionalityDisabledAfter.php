<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Observer\Access;

class IsFunctionalityDisabledAfter extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customerSession = $this->customerSession;
        /* @var $customerSession Mage_Customer_Model_Session */

        $customer = $customerSession->getCustomer();
        /* @var $customer Epicor_Comm_Model_Customer */

        if ($customer->isSalesRep()) {

            $helper = $this->salesRepHelper;
            /* @var $helper Epicor_SalesRep_Helper */

            $functionality = $observer->getEvent()->getFunctionality();
            $transport = $observer->getEvent()->getTransport();
            /* @var $transport Varien_Object */

            $isDisabled = $transport->getDisabled();

            if (!$helper->isMasquerading() && $customer->isSalesRep()) {
                if (in_array($functionality, array('multishipping', 'checkout', 'cart', 'wishlist'))) {
                    $isDisabled = true;
                }
            }

            $transport->setDisabled($isDisabled);
            /* @var $transport Varien_Object */
        }
    }

}