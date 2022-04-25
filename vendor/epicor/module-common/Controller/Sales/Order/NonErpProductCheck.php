<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Sales\Order;

class NonErpProductCheck extends \Epicor\Common\Controller\Sales\Order {

    /**
     * Action for reorder
     */
    public function execute() {
        $params = $this->getRequest()->getParams();
        $rfqSkus = false;
        if (isset($params['data'])) {
            $rfqSkus = json_decode($params['data']);
        }

        $source = isset($params['source']) ? $params['source'] : 'checkout';
        $nonErpItemsEnabled = false;
        $options = $this->scopeConfig->getValue('epicor_product_config/non_erp_products/options', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $enabled = $this->scopeConfig->getValue('epicor_product_config/non_erp_products/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $msgText = $this->scopeConfig->getValue('epicor_product_config/non_erp_products/' . $source . '_text', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $guest = false;
        $nonErpItems = false;
        if ($enabled && $options == 'request') {
            $customerSession = $this->customerSession;
            $customer = $customerSession->getCustomer();
            $nonErpItemsEnabled = true;
            $guest = $customerSession->isLoggedIn() ? false : true;
            if ($rfqSkus) {
                foreach ($rfqSkus as $sku) {
                    $productId = $this->catalogProduct->getIdBySku($sku);
                    $productTypeId = $this->catalogProductResourceModel->getAttributeRawValue($productId, 'type_id', $this->storeManager->getStore()->getStoreId());
                    $productStkType = $this->catalogProductResourceModel->getAttributeRawValue($productId, 'ecc_stk_type', $this->storeManager->getStore()->getStoreId());
                    if (!$productStkType && $productTypeId['type_id'] != 'configurable') {
                        $nonErpItems = true;
                        break;
                    }
                }
            } else {

                foreach ($this->checkoutCart->getItems() as $item) {
                    $productTypeId = $this->catalogProductResourceModel->getAttributeRawValue($item->getProduct()->getId(), 'type_id', $this->storeManager->getStore()->getStoreId());
                    $productStkType = $this->catalogProductResourceModel->getAttributeRawValue($item->getProduct()->getId(), 'ecc_stk_type', $this->storeManager->getStore()->getStoreId());
                    if (!$productStkType && $productTypeId['type_id'] != 'configurable') {
                        $nonErpItems = true;
                        break;
                    }
                }
            }
        }
         echo json_encode(array(
            'success' => true,
            'msgText' => $msgText,
            'nonErpItemsEnabled' => $nonErpItemsEnabled,
            'option' => $options,
            'nonErpItems' => $nonErpItems,
            'guest' => $guest)
            //    )
        );
    }

}
