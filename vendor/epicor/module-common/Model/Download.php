<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Model;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Area;
use Magento\Framework\Logger\Monolog;
use Magento\Framework\Message\ManagerInterface;

class Download
{
    private $response;
    private $filesystem;
    private $state;
    private $contentLength;
    private $fileName;
    private $filePath;
    private $logger;
    private $messageManager;

    /**
     * Download constructor.
     * @param ResponseInterface $response
     * @param Filesystem $filesystem
     * @param State $state
     * @param Monolog $logger
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        ResponseInterface $response,
        Filesystem $filesystem,
        State $state,
        Monolog $logger,
        ManagerInterface $messageManager
    ) {
        $this->logger = $logger;
        $this->response = $response;
        $this->filesystem = $filesystem;
        $this->state = $state;
        $this->messageManager = $messageManager;
    }

    /**
     * @param $fileName
     * @param $basePath
     * @return bool|ResponseInterface
     */
    public function createDownload($fileName, $basePath)
    {
        $result = false;

        try{
            $this->setFileResources($fileName, $basePath);

            if (!$this->isAdminScope()) {
                throw new \Exception('Incorrect scope');
            }

           $result = $this->downloadFile();

        }catch(\Exception $e){
            $this->logger->info($e->getMessage());
            $this->logger->info($e->getTraceAsString());
            $this->messageManager->addErrorMessage($e->getMessage() . ': see epicor_common.log');
        }

        return $result;
    }

    private function downloadFile()
    {
        $this->setDownloadHeaders();
        $this->streamFileData();

        return $this->response;
    }

    private function setFileResources($fileName, $basePath)
    {
        $this->fileName = $fileName;
        $this->filePath = $basePath . $fileName;
        $this->contentLength = filesize($this->filePath);
    }

    private function setDownloadHeaders()
    {
        $this->response->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', 'application/octet-stream', true)
            ->setHeader('Content-Length', $this->contentLength, true)
            ->setHeader('Content-Disposition', $this->getFileAttachmentName(), true)
            ->setHeader('Last-Modified', date('r'), true);
    }

    /**
     * @return bool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isAdminScope(): bool
    {
        return $this->state->getAreaCode() === Area::AREA_ADMINHTML;
    }

    private function getFileAttachmentName()
    {
        return 'attachment; filename="' . $this->fileName . '"';
    }

    private function streamFileData()
    {
        $this->response->sendHeaders();
        readfile($this->filePath);
    }
}