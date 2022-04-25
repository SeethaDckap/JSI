<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount;

class Deliverygrid extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount
{

    public function execute()
    {
        $this->_initErpAccount();
        $result = $this->_resultLayoutFactory->create();
        $result->getLayout()->getBlock('erp_delivery_grid')
            ->setSelected($this->getRequest()->getPost('delivery', null));

        return $result;
    }

}
