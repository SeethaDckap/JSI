<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Observer\Customer;

class CheckAccess extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{



    /**
     * @var \Epicor\SalesRep\Model\AccountFactory
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->salesRepHelper;
        $error = __('You are no longer able to access this store');

        $customerSession = $this->customerSession;
        /* @var $customerSession \Magento\Customer\Model\Session */

        $customer = $customerSession->getCustomer();
        /* @var $customer \Epicor\Comm\Model\Customer */

        if ($customerSession->isLoggedIn() && $customer->isSalesRep() && !$helper->isEnabled()) {
            $customerSession->unsetAll();
            $customerSession->addError($error);
            $helper->wipeCart();
            //M1 > M2 Translation Begin (Rule p2-3)
            /*Mage::app()->getResponse()->setRedirect(Mage::getUrl('customer/account/login', array('access' => 'denied'), 403));
            die(Mage::app()->getResponse());*/
            //M1 > M2 Translation Begin (Rule p2-4)
            //$this->response->setRedirect(Mage::getUrl('customer/account/login', array('access' => 'denied'), 403));
            $this->response->setRedirect($this->urlBuilder->getUrl('customer/account/login', array('access' => 'denied'), 403));
            //M1 > M2 Translation End
            die($this->response->sendResponse());
            //M1 > M2 Translation End
        }
    }

}