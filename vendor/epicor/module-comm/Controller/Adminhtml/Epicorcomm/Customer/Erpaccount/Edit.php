<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount;

class Edit extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount
{

    public function execute()
    {

        $id = $this->getRequest()->getParam('id', null);
        $model = $this->commCustomerErpaccountFactory->create();
        if ($id) {
            $model->load($id);
            if ($model->getId()) {
                $data = $this->backendSession->getFormData(true);
                if ($data) {
                    $model->setData($data)->setId($id);
                }
            } else {
                $this->messageManager->addErrorMessage(__('Customer Erp Account not found'));
                $this->_redirect('*/*/');
            }
        }

        $this->registry->register('customer_erp_account', $model);

        $resultPage = $this->_resultPageFactory->create();
        //$resultPage->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        return $resultPage;
    }

}
