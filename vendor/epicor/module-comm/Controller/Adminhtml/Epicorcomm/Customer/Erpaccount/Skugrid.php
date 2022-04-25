<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount;

class Skugrid extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount
{


    public function execute()
    {
        $this->_initErpAccount();

        $result = $this->_resultLayoutFactory->create();

        return $result;
    }

}
