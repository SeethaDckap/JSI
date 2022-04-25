<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount;

class Listerpaccounts extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount
{

    public function execute()
    {
        $resultLayout = $this->_resultLayoutFactory->create();
        if ($this->getRequest()->getParam('grid')) {
            $this->getResponse()->setBody(
                $resultLayout->getLayout()->createBlock('Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Attribute\Grid')->toHtml()
            );
        } else {
            $this->getResponse()->setBody(
                $resultLayout->getLayout()->createBlock('Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Attribute')->toHtml()
            );
        }
    }

}
