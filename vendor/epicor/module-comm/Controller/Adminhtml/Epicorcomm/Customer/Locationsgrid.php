<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer;

class Locationsgrid extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount
{
    
     public function execute()
     {
        $this->_initErpAccount();
        $customers = $this->getRequest()->getParam('in_locations_lists_grid');
        $result = $this->_resultLayoutFactory->create();
        $result->getLayout()->getBlock('locations.grid')->setSelected($customers);
        return $result;
    }

}
