<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount;

class Delete extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount
{

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id != null) {
            $this->delete($id);
        } else {
            $this->messageManager->addErrorMessage(__('Unable to find the ERP Account to delete.'));
        }
        $this->_redirect('*/*/');
    }

}
