<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Account\Manage;

class Save extends \Epicor\SalesRep\Controller\Account\Manage
{

    public function execute()
    {
        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper \Epicor\SalesRep\Helper\Account\Manage */

        $salesRepAccount = $helper->getManagedSalesRepAccount();

        $data = $this->getRequest()->getParams();
        if ($data) {
            $salesRepAccount->setName($data['name']);
            $salesRepAccount->save();

            $this->messageManager->addSuccessMessage(__('Sales Rep Account Updated Successfully'));
        }

        $this->_redirect('*/*/');
    }

}
