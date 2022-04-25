<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Adminhtml\Arpayments;

class Index extends \Epicor\Customerconnect\Controller\Adminhtml\Arpayments
{

    public function execute()
    {
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('AR Payments'));
        return $resultPage;
    }
}
