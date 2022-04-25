<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Syn;

class Index extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Syn
{

   
    public function execute()
    { 
        $resultPage = $this->_initPage();
        $resultPage->getConfig()->getTitle()->prepend(__('Send SYN request'));
        return $resultPage;
    }

}
