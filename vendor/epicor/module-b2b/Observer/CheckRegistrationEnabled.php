<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\B2b\Observer;

class CheckRegistrationEnabled extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Check Registration Enabled or Not
     * When Home Registration is disabled, you can't still access it by entering URL 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $registrationEnabled = $this->scopeConfig->isSetFlag('epicor_b2b/registration/reg_customer', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (empty($registrationEnabled)) {
            //M1 > M2 Translation Begin (Rule p2-3)
            //Mage::app()->getResponse()->setRedirect(Mage::getUrl('customer/account/login'))->sendResponse();
            //M1 > M2 Translation Begin (Rule p2-4)
            //$this->responseFactory->create()->setRedirect(Mage::getUrl('customer/account/login'))->sendResponse();
            $this->response->setRedirect($this->urlBuilder->getUrl('customer/account/login'))->sendResponse();
            //M1 > M2 Translation End
            //M1 > M2 Translation End
            exit();
        }
    }

}