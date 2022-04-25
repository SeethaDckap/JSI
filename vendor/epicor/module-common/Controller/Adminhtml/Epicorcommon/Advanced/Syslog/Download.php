<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Advanced\Syslog;

class Download extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Advanced\Syslog {

    /**
     * @var Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_downloader;

    public function __construct(
    \Epicor\Comm\Controller\Adminhtml\Context $context, \Magento\Backend\Model\Auth\Session $backendAuthSession, \Magento\Framework\App\Filesystem\DirectoryList $directoryList, \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->_downloader = $fileFactory;
        parent::__construct($context, $backendAuthSession, $directoryList);
    }

    public function execute() {
        $filename = base64_decode($this->getRequest()->getParam('filename'));
        $file = $this->_directoryList->getPath('log') . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($file) && !is_dir($file)) {
            return $this->_downloader->create(
                            $filename, @file_get_contents($file)
            );
        } else {
            $this->messageManager->addError(__('Log file not found'));
            $this->_redirect('*/*/index');
        }
    }

}
