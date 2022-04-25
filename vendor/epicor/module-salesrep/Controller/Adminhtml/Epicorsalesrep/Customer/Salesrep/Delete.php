<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Salesrep;

class Delete extends \Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Salesrep
{

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id != null) {
            $this->delete($id);
        } else {
            $this->messageManager->addErrorMessage(__('Unable to find the Sales Rep Account to delete.'));
        }
        $this->_redirect('*/*/');
    }

}
