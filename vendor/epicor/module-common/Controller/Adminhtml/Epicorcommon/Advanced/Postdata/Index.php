<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Advanced\Postdata;

class Index extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Advanced\Postdata
{

    public function execute()
    { 
        $resultPage = $this->_initPage();
        $resultPage->getConfig()->getTitle()->prepend(__('Post Data'));
        return $resultPage;
    }

}
