<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer\Msq;

class MsqForConfiguration extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor_Comm_Model_Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->scopeConfig->isSetFlag('epicor_comm_enabled_messages/msq_request/triggers_quickorderpad_config_products', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
                && $this->request->getControllerName() == 'form') {        //only send if config value set    
            $configuration = $observer->getEvent()->getConfiguration();
            $this->commMessagingHelper->sendMsq($configuration, 'quickorderpad_config_products');
        }
    }

}