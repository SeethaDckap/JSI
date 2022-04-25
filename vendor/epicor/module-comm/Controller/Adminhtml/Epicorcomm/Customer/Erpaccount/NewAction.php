<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount;

class NewAction extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount
{

    public function execute()
    {
        $model = $this->commCustomerErpaccountFactory->create();
        $this->registry->register('customer_erp_account', $model);

        $result = $this->_resultPageFactory->create();

        return $result;
    }

}
