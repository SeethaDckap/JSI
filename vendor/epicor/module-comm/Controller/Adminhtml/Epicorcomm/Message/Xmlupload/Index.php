<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Xmlupload;

class Index extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Xmlupload
{
    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    )
    {
        parent::__construct($context, $backendAuthSession);
    }


    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Xml Upload'));
        return $resultPage;
    }

}
