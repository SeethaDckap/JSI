<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class BaseUrlChangeAfter extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Triggered when the base url changes, sends a SYN message with the provided new url
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $url = $observer->getEvent()->getUrl();

        if (!empty($url)) {
            $syn = $this->commMessageRequestSynFactory->create();
            /* @var $syn Epicor_Comm_Model_Message_Request_Syn */
            $syn->setSyncUrl($url);
            if ($syn->sendMessage()) {
                $this->backendSession->addSuccess(__('Website URL Changed. SYN request successfully sent'));
            } else {
                //M1 > M2 Translation Begin (Rule 55)
                //$this->backendSession->addError(__('Website URL Changed. SYN request failed. Status description - %s', $syn->getStatusDescriptionText()));
                $this->backendSession->addError(__('Website URL Changed. SYN request failed. Status description - %1', $syn->getStatusDescriptionText()));
                //M1 > M2 Translation End
            }
        }
    }

}