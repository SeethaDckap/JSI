<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Customer;

class Listsgrid extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount
{

    public function execute()
    {      
        
        $customer_id = $this->getRequest()->getParam('customer_id');
        $locations = $this->getRequest()->getParam('in_customer_lists_grid');
        $result = $this->_resultLayoutFactory->create();
        $result->getLayout()->getBlock('customer_lists_grid')->setSelected($locations);
        $result->getLayout()->getBlock('customer_lists_grid')->setCustomer($customer_id);
        return $result;
        
    }

}
