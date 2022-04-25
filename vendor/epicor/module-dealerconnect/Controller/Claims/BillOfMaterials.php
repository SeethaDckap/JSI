<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Claims;

class BillOfMaterials extends \Epicor\Dealerconnect\Controller\Inventory\BillOfMaterials
{
    /**
     * Index action
     */
    public function execute()
    {
        $details = $this->_getBomDetails();
        $debm = $this->dealerconnectMessageRequestDebm;
        $helper = $this->customerconnectHelper;
        $messageTypeCheck = $debm->getHelper()->getMessageType('DEBM');
        if ($debm->isActive() && $messageTypeCheck && isset($details)) {
            $debmData = $this->registry->registry('debm_details');
            $resultPage = $this->resultLayoutFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_LAYOUT);
            return $resultPage;
        } else {
            $this->messageManager->addErrorMessage(__("ERROR - Inventory Details not available"));
            if ($this->messageManager->getMessages()->getItems()) {
                session_write_close();
                return $this->resultRedirectFactory->create()->setPath('customer/account/index');
            }
        }
    }
    
    /**
     * Performs a DEBM request
     * 
     * @return boolean
     */
    protected function _getBomDetails()
    {
        $results = false;
        $locationInfo = $this->request->getParam('location');
        $helper = $this->customerconnectHelper;
        $erpAccountNumber = $helper->getErpAccountNumber();
        if ($locationInfo != '') {
            $debm = $this->dealerconnectMessageRequestDebm;
            $debm->setAccountNumber($erpAccountNumber)
                 ->setLocationNumber($locationInfo)
                 ->setLanguageCode($helper->getLanguageMapping($this->_localeResolver->getLocale()));
            if ($debm->sendMessage()) {            
                $debmData = $debm->getResults();
                $debmTrans = $debm->getTransResults();
                $this->registry->register('debm_details', $debmData);
                $this->registry->register('debm_trans_details', $debmTrans);
            }            
            $results = $locationInfo;
        } else {
            $results = false;
        }
        return $results;
    }
}