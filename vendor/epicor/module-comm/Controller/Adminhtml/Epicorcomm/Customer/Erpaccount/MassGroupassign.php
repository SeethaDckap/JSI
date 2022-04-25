<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount;

class MassGroupassign extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount
{

    public function execute()
    {
        $accountIds = (array)$this->getRequest()->getParam('accounts');
        $groupId = $this->getRequest()->getParam('customerGroup');
        foreach ($accountIds as $accountId) {
            $model = $this->commCustomerErpaccountFactory->create()->load($accountId);
            $model->setmagentoId($groupId);
            $model->save();
        }
        $this->messageManager->addSuccessMessage(__('The Customer Groups have been assigned.'));
        $this->_redirect('*/*/');
    }

}
