<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\OrderApproval\Controller\Manage;

class Index extends \Epicor\Lists\Controller\Lists
{
    const FRONTEND_RESOURCE = 'Epicor_Customer::my_account_group_read';

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->messageManager->addNotice(
            __("A minimum of two groups organized in hierarchy is required for the Order Approval Process.")
        );
        return $this->resultPageFactory->create();
    }
}
