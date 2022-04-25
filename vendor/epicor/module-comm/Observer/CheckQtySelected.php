<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class CheckQtySelected extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $nothingSelected = true;
        $data = $this->request->getParams();

        //products and supergroup are mutually exclusive, but neither may be present
        switch (true) {
            case (array_key_exists('products', $data) && count($data['products']) == 1):
                foreach ($data['products'] as $key => $product) {
                    if (array_key_exists('multiple', $product)) {
                        foreach ($product['multiple'] as $location) {
                            if ($location['qty'] > 0) {
                                $nothingSelected = false;
                                break 2;
                            }
                        }
                    }
                }
                break;
            case(array_key_exists('super_group_locations', $data)):
                foreach ($data['super_group_locations'] as $location) {
                    foreach ($location as $key => $value) {
                        if ($value) {
                            $nothingSelected = false;
                            break 2;
                        }
                    }
                }
                break;
            default:
                // if it gets here there is no products or super_group do nothing as qty will default to 1
                break;
        }

        $this->checkBsvRestricted();

        $this->registry->unregister('Epicor_No_Valid_Qty_Selected');
        $this->registry->register('Epicor_No_Valid_Qty_Selected', $nothingSelected);

        return $this;
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