<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Controller\Manage;


use Magento\Framework\App\ResponseInterface;

class Approvals extends \Epicor\Lists\Controller\Lists
{
    const FRONTEND_RESOURCE = 'Epicor_Customer::my_account_approvals_read';

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}