<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Advanced\Syslog;

class View extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Advanced\Syslog
{

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultPage = $this->_initAction();
        $logFilename = base64_decode($this->getRequest()->getParam('filename'));
        if ($logFilename) {
            $logFilePath = $this->_directoryList->getPath(
                    \Magento\Framework\App\Filesystem\DirectoryList::LOG) . DIRECTORY_SEPARATOR . $logFilename;

            if (file_exists($logFilePath)) {
                $this->_registry->register('sysLogFilename', $logFilename);
            }
        }  

        if ($this->_registry->registry('sysLogFilename') === null) {
            $this->messageManager->addError(__('Log file not found.'));
            return $resultRedirect->setPath('*/*/index');
        }

        $resultPage->getConfig()->getTitle()->prepend(__('Viewing ' . $logFilename));

        return $resultPage;
    }
}

