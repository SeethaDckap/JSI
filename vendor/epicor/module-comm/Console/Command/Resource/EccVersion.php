<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Console\Command\Resource;

use Epicor\Comm\Console\Command\Resource\EccVersionException as EccVersionException;
use Epicor\Comm\Console\Command\Resource\HostConfiguration as HostConfiguration;
use Epicor\Comm\Logger\DatabaseCopy\Logger as DatabaseCopyLogger;
use Epicor\Comm\Model\GlobalConfig\Config as GlobalConfig;

class EccVersion
{
    private $hostConfiguration;
    private $logger;
    private $globalConfig;

    public function __construct(
        HostConfiguration $hostConfiguration,
        DatabaseCopyLogger $logger,
        GlobalConfig $globalConfig
    ) {
        $this->hostConfiguration = $hostConfiguration;
        $this->logger = $logger;
        $this->globalConfig = $globalConfig;
    }

    /**
     * @return bool
     * @throws EccVersionException
     */
    public function isMatchingEccVersions(): bool
    {
        $currentHostEccVersion = $this->getSourceHostEccVersion();
        $this->logger->info($currentHostEccVersion, ['ECC version on destination host']);

        $otherHostEccVersion = $this->getDestinationHostEccVersion();
        $this->logger->info($otherHostEccVersion, ['ECC version on destination host']);

        return $currentHostEccVersion === $otherHostEccVersion;
    }

    public function getSourceHostEccVersion()
    {
        $this->logger->info($this->hostConfiguration->getSourcePath(), ['Getting source host Ecc version']);
        $versionConfig = $this->getLocalSourceEccVersion();
        $versions = [];
        if (is_array($versionConfig)) {
            foreach ($versionConfig as $moduleVersion) {
                $versions[] = $moduleVersion['version'] ?? '';
            }
        }
        $finalVersion = array_unique($versions);
        if (is_array($finalVersion) && count($finalVersion) === 1 && isset($finalVersion[0])) {
            return $finalVersion[0];
        }
    }

    private function getLocalSourceEccVersion()
    {
        return $this->globalConfig->get('ecc_version_info');
    }

    /**
     * @return bool
     * @throws EccVersionException
     */
    public function getDestinationHostEccVersion()
    {
        $rootPath = $this->hostConfiguration->getDestinationPath();
        $vendorPath = $rootPath . 'vendor/epicor/';

        if (file_exists($vendorPath) && is_dir($vendorPath)) {
            $this->logger->info('Getting destination ECC version from vendor dir');
            return $this->getEccGlobalVersion('vendor_epicor', $rootPath);
        }
        $this->logger->info('Getting destination ECC version from app/code');
        return $this->getEccGlobalVersion('app_code', $rootPath);
    }

    /**
     * @param $type
     * @param $rootPath
     * @return bool
     * @throws EccVersionException
     */
    private function getEccGlobalVersion($type, $rootPath)
    {
        $directoryList = [];

        $path = $type === 'vendor_epicor' ? $rootPath . 'vendor/epicor/' : $rootPath . 'app/code/Epicor/';
        $this->logger->info('Ecc code location on destination',[$path]);

        if ($handle = opendir($path)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry !== '.' && $entry !== '..' && is_dir($path . $entry)) {
                    $directoryList[] = $path . $entry . '/';
                }
            }
            closedir($handle);
        }

        $moduleVersion = [];
        foreach ($directoryList as $moduleDirectory) {
            if (file_exists($moduleDirectory . 'etc/global.xml')) {
                $configFile = file_get_contents($moduleDirectory . 'etc/global.xml');
                $dom = new \SimpleXMLElement($configFile);
                $version = $dom->xpath('global/ecc_version_info//version');
                $moduleVersion[] = (string) $version[0];
            }
        }

        if (is_array($moduleVersion) && count(array_unique($moduleVersion)) === 1) {
            $version = array_unique($moduleVersion);
            return $version[0] ?? false;
        }

        throw new EccVersionException('Version can\'t be determined, ECC global.xml has different versions.');
    }
}