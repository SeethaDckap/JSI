<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Lists;

class MassDelete extends \Epicor\Lists\Controller\Lists
{

    /**
     * Deletes array of given List
     *
     * @return void
     */
    public function execute()
    {
        $ids = (array) $this->getRequest()->getParam('listid');
        if (strpos($ids[0], ',') !== false) {
            $ids = explode(',', $ids[0]);
        }
        $helper = $this->listsHelper;
        /* @var $list Epicor_Lists_Helper_Data */
        $ListDatas = $helper->getListDatas($ids);
        $listModel = $this->listsListModelFactory->create();
        /* @var $list Epicor_Lists_Model_ListModel */
        $customerSession = $this->customerSession->getCustomer();
        foreach ($ListDatas as $ListSeparateData) {
            //get List Id
            $id = $ListSeparateData->getId();
            //get List Erp code
            $erpCode = $ListSeparateData->getErpCode();
            //get List Owner Id
            $ownerId = $ListSeparateData->getOwnerId();
            $checkMasterErp = $listModel->isValidEditForErpAccount($customerSession, $id);
            $checkCustomer = $listModel->isValidEditForCustomers($customerSession, $id, $ownerId);
            if ((!$checkMasterErp) || (!$checkCustomer)) {
                //get the erp code
                $errorIds[] = $erpCode;
            } else {
                $successIds[] = $id;
                $deleteList = $this->delete($id, true);
                $successErps[] = $deleteList;
            }
        }
        if (!empty($errorIds)) {
            $errorLists = implode(', ', $errorIds);
            $this->messageManager->addError(__('Could not delete ' . count(array_keys($errorIds)) . ' Lists. ' . "List Reference Code: (" . $errorLists . ")"));
        }
        if (!empty($successIds)) {
            $successList = implode(', ', $successErps);
            $this->messageManager->addSuccess(__(count(array_keys($successIds)) . ' Lists deleted. ' . "List Reference Code: (" . $successList . ")"));
        }
        $this->_redirect('*/*/');
    }

}
