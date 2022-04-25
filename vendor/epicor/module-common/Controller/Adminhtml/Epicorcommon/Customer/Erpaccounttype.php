<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Customer;


class Erpaccounttype extends \Magento\Customer\Controller\Adminhtml\Index
{
    public function execute()
    {
        $this->initCurrentCustomer();


        $resultLayout = $this->resultLayoutFactory->create();

        return $resultLayout;
    }
}