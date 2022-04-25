<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Controller\Adminhtml\Quotes\Quotes;

class Index extends \Epicor\Quotes\Controller\Adminhtml\Quotes\Quotes
{
    
    public function execute()
    {   
        $resultPage = $this->_initPage();
        $resultPage->getConfig()->getTitle()->prepend(__('Quotes'));
        return $resultPage;
    }
}
