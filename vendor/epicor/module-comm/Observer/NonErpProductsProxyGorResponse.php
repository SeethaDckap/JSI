<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class NonErpProductsProxyGorResponse extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $options = $this->scopeConfig->getValue('epicor_product_config/non_erp_products/options', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $enabled = $this->scopeConfig->getValue('epicor_product_config/non_erp_products/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if($enabled && $options == 'proxy'){
            $message = $observer->getEvent()->getMessage();
            $erpOrderNumber = $observer->getEvent()->getMessage()->getResponse()->getOrderNumber();
            $this->registry->unregister('GOR_ERP_order_number');
            $this->registry->register('GOR_ERP_order_number', $erpOrderNumber);
            $this->commHelper->create()->retrieveNonErpProductsInCart($this->registry->registry('GOR_request_message'), true);
        }    
    }    
}