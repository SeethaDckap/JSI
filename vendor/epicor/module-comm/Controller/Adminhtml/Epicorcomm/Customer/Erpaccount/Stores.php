<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount;

class Stores extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount
{

    public function execute()
    {
        $this->_initErpAccount();
        $result = $this->_resultLayoutFactory->create();
        $result->getLayout()->getBlock('stores_grid')
            ->setSelected($this->getRequest()->getParam('stores', null));

        return $result;
    }

}
