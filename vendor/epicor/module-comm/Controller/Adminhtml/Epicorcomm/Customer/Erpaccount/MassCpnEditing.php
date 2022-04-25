<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount;

class MassCpnEditing extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount
{

    public function execute()
    {
        $accountIds = (array) $this->getRequest()->getParam('accounts');
        $cpnEditing = $this->getRequest()->getParam('cpnEditing');
        $cpnEditing = $cpnEditing == '' ? null : $cpnEditing;

        foreach ($accountIds as $accountId) {
            $model = $this->commCustomerErpaccountFactory->create()->load($accountId);
            $model->setCpnEditing($cpnEditing);
            $model->save();
        }
        $this->backendSession->addSuccess(__('The CPN Editing values have been changed.'));
        $this->_redirect('*/*/');
    }

    }
