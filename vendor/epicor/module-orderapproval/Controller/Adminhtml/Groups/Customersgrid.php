<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Controller\Adminhtml\Groups;

use Epicor\OrderApproval\Controller\Adminhtml\Groups;

class Customersgrid extends Groups
{

    /**
     * Customers ajax reload of grid tab
     *
     * @return void
     */
    public function execute()
    {
        $customers = $this->getRequest()->getParam('customers');
        $resultLayout = $this->_resultLayoutFactory->create();

        $block = $resultLayout->getLayout()->getBlock('customers_grid');
        $block->setSelected($customers);

        return $resultLayout;
    }

}
