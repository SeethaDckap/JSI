<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount;

class Salesreps extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount
{
    public function execute()
    {
        $this->_initErpAccount();
        $result = $this->_resultLayoutFactory->create();
        $result->getLayout()->getBlock('salesreps_grid')
            ->setSelected($this->getRequest()->getParam('salesreps', null));

        return $result;
    }

}
