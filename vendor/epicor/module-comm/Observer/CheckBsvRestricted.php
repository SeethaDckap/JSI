<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class CheckBsvRestricted  implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */
    protected $checkoutSession;
    protected $registry;
    protected $scopeConfig;

    public function __construct(
           \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Magento\Framework\Registry $registry,
             \Magento\Checkout\Model\Session $checkoutSession
     ){
         $this->scopeConfig = $scopeConfig;
         $this->checkoutSession = $checkoutSession;
         $this->registry = $registry;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $bsvForCart = $this->scopeConfig->getValue('epicor_comm_enabled_messages/bsv_request/bsv_for_cart', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $bsvTriggerForCart = $this->scopeConfig->getValue('epicor_comm_enabled_messages/bsv_request/bsv_trigger_cart', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $emptyCheck = ($bsvForCart && !$bsvTriggerForCart);
        if ($emptyCheck) {
            $this->registry->unregister('dont_send_bsv');
            $this->registry->register('dont_send_bsv', true, true);
        }
    }

}
