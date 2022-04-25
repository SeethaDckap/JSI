<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Controller\Adminhtml\Groups;

use Epicor\OrderApproval\Controller\Adminhtml\Groups;

class Erpaccountsgrid extends Groups
{
    /**
     * ERP Accounts grid load by ajax
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $erpAccounts = $this->getRequest()->getParam('erpaccounts');
        $resultLayout = $this->_resultLayoutFactory->create();

        /** @var \Epicor\OrderApproval\Block\Adminhtml\Groups\Edit\Tab\Erpaccounts $block */
        $block = $resultLayout->getLayout()->getBlock('erpaccounts_grid');
        $block->setSelected($erpAccounts);

        return $resultLayout;
    }
}
