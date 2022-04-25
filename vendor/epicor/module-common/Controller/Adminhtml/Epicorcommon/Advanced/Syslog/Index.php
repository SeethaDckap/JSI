<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Advanced\Syslog;

class Index extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Advanced\Syslog
{

    public function execute()
    {
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('System Logs'));

        return $resultPage;
    }
}
