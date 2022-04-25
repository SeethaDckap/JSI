<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Advanced\Entityreg;

class Index extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Advanced\Entityreg
{
    public function execute()
    { 
        
        $resultPage = $this->_initPage();
        $resultPage->getConfig()->getTitle()->prepend(__('Uploaded Data'));
        return $resultPage;
    }
    
}
