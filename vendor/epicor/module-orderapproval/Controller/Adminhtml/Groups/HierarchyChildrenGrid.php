<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Controller\Adminhtml\Groups;

use Epicor\OrderApproval\Controller\Adminhtml\Groups;

class HierarchyChildrenGrid extends Groups
{
    /**
     * Hierarchy children grid load by ajax
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultLayout = $this->_resultLayoutFactory->create();
        /** @var \Epicor\OrderApproval\Block\Adminhtml\Groups\Edit\Tab\Erpaccounts $block */
        $block = $resultLayout->getLayout()->getBlock('children_grid');

        return $resultLayout;
    }
}
