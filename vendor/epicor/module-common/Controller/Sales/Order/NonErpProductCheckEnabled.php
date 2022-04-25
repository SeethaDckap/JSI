<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Sales\Order;

class NonErpProductCheckEnabled extends \Epicor\Common\Controller\Sales\Order
{

    /**
     * Action for reorder
     */
    public function execute()
    {
        $nonErpProductCheckEnabled = false;
        $options = $this->scopeConfig->getValue('epicor_product_config/non_erp_products/options', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $checkoutText = $this->scopeConfig->getValue('epicor_product_config/non_erp_products/checkout_text', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($this->scopeConfig->isSetFlag('epicor_product_config/non_erp_products/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) && $options == 'request') {
            $nonErpProductCheckEnabled = true;
        }
        $this->getResponse()->setBody(json_encode(array(
                                                        'success' => true, 
                                                        'nonErpProductCheckEnabled' => $nonErpProductCheckEnabled,
                                                        'checkoutText' => $checkoutText)
                                                    )
                                    );                        
    }

}
