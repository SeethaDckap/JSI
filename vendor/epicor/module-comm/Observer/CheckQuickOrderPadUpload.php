<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class CheckQuickOrderPadUpload extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        /* @var $product \Epicor\Comm\Model\Product */

        $helper = $this->commProductHelper->create();
        /* @var $helper \Epicor\Comm\Helper\Product */

        $this->checkBsvRestricted();

        $helper->removeConfigureListProduct($product->getId());
    }

    protected function checkBsvRestricted()
    {
        $bsvForCart = $this->scopeConfig->getValue('epicor_comm_enabled_messages/bsv_request/bsv_for_cart', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $bsvTriggerForCart = $this->scopeConfig->getValue('epicor_comm_enabled_messages/bsv_request/bsv_trigger_cart', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $quote = $this->checkoutSession->create()->getQuote();
        $emptyCheck = ($bsvForCart && !$bsvTriggerForCart);
        if ($emptyCheck) {
            $this->registry->unregister('dont_send_bsv');
            $this->registry->register('dont_send_bsv', true, true);
        }
    }
}