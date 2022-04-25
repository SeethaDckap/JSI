<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\HostingManager\Model;

use DirectoryIterator;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class Host
{
    private $storeManager;
    private $directoryIterator;
    private $hostName;
    private $nginxVhostPath = '/var/www/vhost/';
    private $defaultSitesEnabledPath = '/etc/nginx/sites-enabled/';
    private $nginxConfigPath = '/etc/nginx/nginx.conf';
    private $nginxLogNames = [];
    private $accessLogPath;
    private $errorLogPath;
    private $logger;

    public function __construct(
        LoggerInterface $logger,       
        StoreManagerInterface $storeManager
    ){
        $this->logger = $logger;
        $this->storeManager = $storeManager;       
    }

    /**
     * This method gets the nginx log file paths based on the current
     * nginx configuration, when the nginx config is set with an includes
     * path to the vhost directory then the vhost files in this location will
     * be used, if this is not set then the vhost details will be got from
     * /etc/nginx/sites-enabled
     *
     * @return array
     */
    public function getNginxLogNames()
    {
        try {
            if ($this->isNginxVhostPathSet()) {
                $this->directoryIterator = new \DirectoryIterator($this->nginxVhostPath);
            } else {
                $this->directoryIterator = new \DirectoryIterator($this->nginxVhostPath);
            }

            $this->getLogNames();

        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getTraceAsString());
        }

        return $this->nginxLogNames;
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getUrlHostName() {
        $urldata = array();
        foreach ($this->storeManager->getStores(false) as $store) {
            $url = $store->getBaseUrl();
            $urlData = parse_url($url);
            $urldata[$urlData['host']] = $urlData['host'] ?? false;
        }
        return $urldata;
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getLogNames() {
        $urldata = $this->getUrlHostName();
        foreach ($urldata as $host) {
            if ($this->hostName = $host) {
                $this->getLogNamesFromVhostLocation();
            }
        }
        return $this->nginxLogNames;
    }

    private function getLogNamesFromVhostLocation()
    {
        foreach ($this->directoryIterator as $file) {
            /** @var $file \DirectoryIterator */
            if ($file->isDot()) {
                continue;
            }
            $vhostConfig = file_get_contents($file->getPathname());

            if (!$this->isNavigationType($file) && $this->isHostUrlInConf($vhostConfig)) {
                $this->setNginxLogNames($vhostConfig);
                break;
            }
        }
    }

    private function isNavigationType(DirectoryIterator $file)
    {
        return $file->getFilename() === "." || $file->getFilename() === "..";
    }

    private function setNginxLogNames($fileData)
    {
        if ($this->isAccessAndErrorFilesPathSet($fileData)) {
            $this->nginxLogNames['access'][] = $this->getLogNameFromPath($this->accessLogPath);
            $this->nginxLogNames['error'][] = $this->getLogNameFromPath($this->errorLogPath);
        }
    }

    private function getLogNameFromPath($path)
    {
        if (preg_match('/\/var\/log\/nginx.*\.log/', $path, $matches)) {
            return $this->getBaseNameForPath($matches);
        }
    }

    private function getBaseNameForPath($matches)
    {
        if ($path = $matches[0] ?? false) {
            return basename($path);
        }
    }

    private function isHostUrlInConf($fileData): bool
    {
        return (bool)preg_match('/server_name\s*' . $this->hostName . '/', $fileData, $matches);
    }

    private function isAccessAndErrorFilesPathSet($fileData): bool
    {
        return $this->isAccessLogPathSet($fileData) && $this->isErrorLogPathSet($fileData);
    }

    private function isAccessLogPathSet($fileData): bool
    {
        if (preg_match('/access_log\s*\/.*/', $fileData, $accessMatches)) {
            $this->accessLogPath = $accessMatches[0];
            return true;
        }

        return false;
    }

    private function isErrorLogPathSet($fileData): bool
    {
        if (preg_match('/error_log\s*\/.*/', $fileData, $errorMatches)) {
            $this->errorLogPath = $errorMatches[0];
            return true;
        }

        return false;
    }

    private function isNginxVhostPathSet(): bool
    {
        $nginxConfig = file_get_contents($this->nginxConfigPath);
        return (bool)preg_match('/[\n\r]\s*include\s*.*vhost/', $nginxConfig, $matches);
    }
}
