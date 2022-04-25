<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Model;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Epicor\Common\Controller\Adminhtml\Download\Log as DownloadAction;
use Magento\Store\Model\ScopeInterface;
use Epicor\Common\Controller\Adminhtml\Download\Log;

class LogView
{
    private $downLoadFileSize;
    private $filePath;
    private $url;
    private $directoryList;
    private $logType;
    private $fileName;
    private $scopeConfig;

    public function __construct(
        DirectoryList $directoryList,
        ScopeConfigInterface $scopeConfig,
        UrlInterface $url,
        String $filePath = '',
        String $logType = 'system',
        String $fileName = ''
    ) {
        $this->filePath = $filePath;
        $this->fileName = $fileName;
        $this->url = $url;
        $this->directoryList = $directoryList;
        $this->logType = $logType;
        $this->scopeConfig = $scopeConfig;
        $this->setDownloadFileSize();
    }

    /**
     * @return bool
     */
    public function getLogFileContents()
    {
        if (!$this->filePath) {
            return false;
        }
        $this->isOverFileSizeLimit()
            ? $this->createDownloadFileOption() : $this->streamFile();
    }

    public function getViewDownLoad($fileName, $type)
    {
        return "<a  href='" . $this->getViewUrl($fileName) . "'>View</a>
                <span class='action-divider'> | </span>
                <a  href='" . $this->getDownloadViewUrl($fileName, $type) . "'>Download</a>";
    }

    private function setDownloadFileSize()
    {
      $this->downLoadFileSize = $this->getDownloadFileSizeConfig();
    }

    private function getDownloadFileSizeConfig()
    {
        return $this->scopeConfig->getValue(
            'epicor_commmon_logconfig/log_view_config/max_logfile_size',
            ScopeInterface::SCOPE_STORE);
    }

    private function isOverFileSizeLimit(): bool
    {
        return filesize($this->filePath) > $this->downLoadFileSize;
    }

    private function createDownloadFileOption()
    {
        echo '<p>' . __('The above file is larger then ') . $this->downLoadFileSize . __(' bytes:')
            . __(' you can download this file using this link') . '</p>';

        echo '<a href="' . $this->getDownLoadUrl() .'">' . __('Download file') . '</a>';
    }

    private function streamFile()
    {
        if (substr($this->filePath, -2) == 'gz') {
            $handle = gzopen($this->filePath, 'rb');
            while (!gzeof($handle)) {
                echo nl2br(gzread($handle, 4096), true);
            }
            fclose($handle);
        } else {
            $handle = fopen($this->filePath, 'rb');
            while (!feof($handle)) {
                echo nl2br(fread($handle, 4096), true);
            }
            fclose($handle);
        }
    }

    private function getDownLoadUrl()
    {
        return $this->url->getUrl(
            DownloadAction::DOWNLOAD_ACTION_LINK,
            ['type'=> base64_encode($this->logType), 'filename' => base64_encode($this->fileName)]
        );
    }

    private function getDownloadViewUrl($fileName, $type)
    {
        return $this->url->getUrl(
            Log::DOWNLOAD_ACTION_LINK,
            ['type' => base64_encode($type), 'filename' => base64_encode($fileName)]
        );
    }

    private function getViewUrl($fileName)
    {
        return $this->url->getUrl('*/*/view', ['filename' => base64_encode($fileName)]);
    }
}
