<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Advanced\Errors;

class Index extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Advanced\Errors
{

    public function execute()
    {
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Error Reports'));

        return $resultPage;
    }
}
