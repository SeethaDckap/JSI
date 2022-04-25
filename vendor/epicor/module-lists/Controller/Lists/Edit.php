<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Lists;

class Edit extends \Epicor\Lists\Controller\Lists
{

    const FRONTEND_RESOURCE = 'Epicor_Customer::my_account_lists_edit';
    public function execute()
    {
 // This is no longer required if customers are able to view lists not owned by themselves. If that changes, this can be reimplemented
//        $list = $this->loadEntity();
//        $listData = $list->getData();
//        if (!empty($listData)) {
//            //$check = $this->loadEntity()->isValidForCustomer(Mage::getSingleton('customer/session')->getCustomer());
//            //A master shopper only sees (and can only amend and delete) lists with a list type of pre-defined or favourite
//            //and which are assigned to his ERP Account and no other ERP Account
//            $checkMasterErp = $this->loadEntity()->isValidEditForErpAccount($this->customerSession->getCustomer(), $list->getId());
//            //non master shopper/Registered shopper/Registered Guest only sees (and can only amend and delete) lists with a list type of pre-defined or favourite
//            //and which are assigned to his ERP Account and customer and no other ERP Account / customer
//            $checkCustomer = $this->loadEntity()->isValidEditForCustomers($this->customerSession->getCustomer(), $list->getId(), $list->getOwnerId());
//            if ((!$checkMasterErp) || (!$checkCustomer)) {/
//
//                $this->messageManager->addError(__('This list type cannot be edited'));
//                session_write_close();
//                $this->_redirect('*/*/');
//                }
//            }
//        }

       // $this->loadLayout();
       // $this->renderLayout();
        $resultPage = $this->resultPageFactory->create();

        return $resultPage;
    }

}
