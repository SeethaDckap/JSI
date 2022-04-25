<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Controller\Manage;

class NewAction extends \Epicor\Lists\Controller\Lists
{
    const FRONTEND_RESOURCE = 'Epicor_Customer::my_account_group_create';
    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
