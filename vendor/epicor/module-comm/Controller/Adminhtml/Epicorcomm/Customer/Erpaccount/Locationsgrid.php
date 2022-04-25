<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount;

class Locationsgrid extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount
{

    public function execute()
    {
        $this->_initErpAccount();
        $customers = $this->getRequest()->getParam('locations');
        $result = $this->_resultLayoutFactory->create();
        $result->getLayout()->getBlock('erp_locations_grid')->setSelected($customers);

        return $result;
    }

}
