<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\HostingManager\Controller\Adminhtml\Hostingmanager;

/**
 * Nginx log admin controller
 * 
 * @category   Epicor
 * @package    Epicor_HostingManager
 * @author     Epicor Websales Team
 */
abstract class Nginxlog extends \Epicor\Comm\Controller\Adminhtml\Generic
{

    protected function _initPage()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Epicor_Common::epicor');
        $resultPage->getConfig()->getTitle()->prepend(__('Nginx logs'));

        return $resultPage;
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'view':
                return $this->backendAuthSession->isAllowed('hostingmanager/nginxlog/view');
                break;
            default:
                return $this->backendAuthSession->isAllowed('Epicor_HostingManager::nginx_log');
                break;
        }
    }

}
