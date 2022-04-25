<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount;

class MassDelete extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount
{

    public function execute()
    {
        $ids = (array)$this->getRequest()->getParam('accounts');

        foreach ($ids as $id) {
            $this->delete($id, true);
        }

        $this->messageManager->addSuccessMessage(__(count($ids) . ' ERP Accounts deleted'));

        $this->_redirect('*/*/');
    }

}
