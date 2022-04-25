<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Controller\Adminhtml\Download;

use Epicor\Common\Model\Download;
use Epicor\Common\Model\LogView;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Logger\Monolog;

class Log extends \Magento\Framework\App\Action\Action
{
    const DOWNLOAD_ACTION_LINK = 'logfile/download/log';
    const NGINX_LOG_BASE_PATH = '/var/log/nginx/';

    private $downloader;
    private $logView;
    private $type;
    private $fileName;
    private $directoryList;
    private $logger;

    public function __construct(
        Context $context,
        DirectoryList $directoryList,
        LogView $logView,
        Download $download,
        Monolog $logger
    ) {
        $this->downloader =  $download;
        $this->logView = $logView;
        $this->logger = $logger;

        parent::__construct($context);
        $this->directoryList = $directoryList;
    }

    public function execute()
    {
        $response = false;

        try {
            $this->setParams();
            $response = $this->downloader->createDownload($this->fileName, $this->getLogFileBasePath());
            if(!$response){
                $this->_redirect($this->_redirect->getRefererUrl());
            }
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage());
            $this->logger->addError($e->getTraceAsString());
        }

        return $response;
    }

    private function setParams()
    {
        $this->type = base64_decode($this->getRequest()->getParam('type'));
        $this->fileName = base64_decode($this->getRequest()->getParam('filename'));
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function getLogFileBasePath()
    {
        if($this->type === 'system'){
            return $this->directoryList->getPath('log') . DIRECTORY_SEPARATOR;
        }

        if($this->type === 'nginx'){
            return self::NGINX_LOG_BASE_PATH;
        }
    }
}
