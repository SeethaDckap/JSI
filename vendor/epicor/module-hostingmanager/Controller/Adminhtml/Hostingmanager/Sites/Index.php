<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Controller\Adminhtml\Hostingmanager\Sites;

class Index extends \Epicor\HostingManager\Controller\Adminhtml\Hostingmanager\Sites
{

    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();

        $resultPage->setActiveMenu('Epicor_Common::epicor');
        $resultPage->addBreadcrumb(__('Manage Hosting'), __('Manage Hosting'));

        $resultPage->getConfig()->getTitle()->prepend(__('Sites'));

        return $resultPage;
    }

}
