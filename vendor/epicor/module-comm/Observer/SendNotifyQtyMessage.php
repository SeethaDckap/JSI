<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Observer;

class SendNotifyQtyMessage extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface {

    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */
    protected $checkoutSession;
    protected $messageManager;
    protected $scopeConfig;

    public function __construct(
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Checkout\Model\SessionFactory $checkoutSession, \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $qtyenabled = $this->scopeConfig->getValue('epicor_comm_enabled_messages/bsv_request/notify_qty_change', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $qtyMessage = $observer->getEvent()->getQtyMessage();
        if ($qtyenabled && $qtyMessage) {
            foreach ($qtyMessage as $key => $value) {
                $message = __('Product %1 not available in this quantity.', $value);
                $this->messageManager->addNoticeMessage($message);
            }
        } else {
            $this->checkoutSession->unsBsvErpQtyMisMatch($qtyMessage);
        }
    }

}
