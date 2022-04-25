<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\B2b\Observer;

class CheckStoreSelector extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Get Captcha String
     *
     * @param \Magento\Framework\DataObject $request
     * @param string $formId
     * @return string
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($module != 'Epicor_ErpSimulator' && !($module == 'Epicor_Comm' && $controller == 'Data') && !($module == 'Epicor_Comm' && $controller == 'Message') && ($controller != 'store' && $action != 'selector') && ($controller != 'store' && $action != 'select') && ($controller != 'file' && $action != 'request')
        ) {
            $helper = $this->commHelper;
            $storeSelectorEnabled = $this->scopeConfig->isSetFlag('Epicor_Comm/brands/show_store_selector', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            if ($storeSelectorEnabled) {
                /* @var $helper Epicor_Comm_Helper_Data */

                //--SF   $stores = $helper->getBrandSelectStores();
                $stores = $this->checkAvailableStoresForWebsite();

                $redirect = false;

                if ($action == 'loginPost') {
                    $store = $this->storeManager->getStore();

                    if (!in_array($store->getId(), array_keys($stores))) {
                        $redirect = true;
                    }
                } else {
                    if (!$this->customerSession->getHasStoreSelected()) {
                        $redirect = true;
                    }
                }

                if ($redirect) {
                    $this->customerSession->setHasStoreSelected(false);
                    //M1 > M2 Translation Begin (Rule p2-3)
                    /*Mage::app()->getResponse()->setRedirect(Mage::getUrl('epicor_comm/store/selector'));
                    die(Mage::app()->getResponse());*/
                    //M1 > M2 Translation Begin (Rule p2-4)
                    //$response = $this->response->setRedirect(Mage::getUrl('epicor_comm/store/selector'))->sendResponse();
                    $response = $this->response->setRedirect($this->urlBuilder->getUrl('epicor_comm/store/selector'))->sendResponse();
                    //M1 > M2 Translation End
                    die($response);
                    //M1 > M2 Translation End
                } else {
                    $this->customerSession->setHasStoreSelected(true);
                }
                $helper->checkForceMasqurading();
            }
        }
    }

}