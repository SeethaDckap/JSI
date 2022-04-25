<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Plugin;

use Magento\Framework\Filesystem\Driver\Http;
use Magento\Framework\App\Filesystem\DirectoryList;

class LogData
{

    /**
     * @var Http
     */
    protected $_http;

    /**
     * Backup type constant for database backup
     */
    const TYPE_LOG = 'syslog';

    protected $_request;
    protected $_helper;
    protected $_fileSystem;

    /**
     * Construct
     *
     * @param Filesystem $filesystem
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Backup\Helper\Data $helper,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->_request = $request;
        $this->_helper = $helper;
        $this->_fileSystem = $filesystem;
    }
    
    /**
     * Get all types to extensions map including log files extensions
     *
     * @return array
     */
    public function aroundGetExtensions(\Magento\Backup\Helper\Data $subject, \Closure $proceed)
    {
        if ($this->_request->getFullActionName() === 'backup_index_index') {

            $result = $proceed();
            return $result;
        }
        return [
            \Magento\Framework\Backup\Factory::TYPE_SYSTEM_SNAPSHOT => 'tgz',
            \Magento\Framework\Backup\Factory::TYPE_SNAPSHOT_WITHOUT_MEDIA => 'tgz',
            \Magento\Framework\Backup\Factory::TYPE_MEDIA => 'tgz',
            \Magento\Framework\Backup\Factory::TYPE_DB => 'sql',
            \Epicor\Common\Plugin\LogData::TYPE_LOG => 'log'
        ];
    }

    public function aroundExtractDataFromFilename(\Magento\Backup\Helper\Data $subject, \Closure $proceed, $filename)
    {
        if (($this->_request->getFullActionName() === 'backup_index_index')||($this->_request->getFullActionName() === 'backup_index_download')) {

            $result = $proceed($filename);
            return $result;
        }

        $filenameWithExtension = $filename;

        $filePath = $this->_fileSystem->getDirectoryRead(DirectoryList::LOG)
                ->getAbsolutePath() . $filenameWithExtension;

        $fileInformation = stat($filePath);
        $fileModificationTime = $fileInformation['mtime'];

        $result = new \Magento\Framework\DataObject();
        $result->addData(['name' => $filenameWithExtension, 'time' => $fileModificationTime]);

        return $result;
    }
}
