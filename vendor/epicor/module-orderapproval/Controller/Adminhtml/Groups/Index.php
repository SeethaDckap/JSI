<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Controller\Adminhtml\Groups;

use Epicor\Comm\Controller\Adminhtml\Context;
use Magento\Backend\Model\Auth\Session;
use Epicor\OrderApproval\Model\GroupsFactory;

class Index extends \Epicor\OrderApproval\Controller\Adminhtml\Groups
{
    public function execute()
    {
        $this->messageManager->addNotice(
            __("A minimum of two groups organized in hierarchy is required for the Order Approval Process.")
        );
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Approval Groups'));

        return $resultPage;
    }

}
