<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Advanced\Cleardata;

class Index extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Advanced\Cleardata
{

    public function execute()
    { 
        $this->messageManager->addWarning(__('Using this feature will delete data permanently from this system. Use with caution.'));
        $resultPage = $this->_initPage();
        return $resultPage;
    }
    
}
