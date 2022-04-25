<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount;

class Mastershopper extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount
{

    public function execute()
    {
        $this->_initErpAccount();
        $result = $this->_resultLayoutFactory->create();
        $result->getLayout()->getBlock('erp_mastershopper_grid')
            ->setSelected($this->getRequest()->getParam('ecc_master_shopper', null));

        return $result;
    }

}
