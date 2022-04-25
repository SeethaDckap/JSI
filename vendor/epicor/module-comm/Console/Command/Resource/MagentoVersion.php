<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Console\Command\Resource;

use Epicor\Comm\Console\Command\Resource\HostConfiguration;
use Epicor\Comm\Logger\DatabaseCopy\Logger as DatabaseCopyLogger;
use Epicor\Comm\Console\Command\Resource\MagentoVersionException as MagentoVersionException;

class MagentoVersion
{
    private $hostConfiguration;
    private $sourceMagentoVersion;
    private $destinationMagentoVersion;
    private $logger;

    public function __construct(
        HostConfiguration $hostConfiguration,
        DatabaseCopyLogger $logger
    ) {
        $this->hostConfiguration = $hostConfiguration;
        $this->logger = $logger;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isMatchingMagentoVersion(): bool
    {
        return version_compare($this->getSourceMagentoVersion(), $this->getDestinationMagentoVersion()) === 0;
    }

    /**
     * @return string
     * @throws MagentoVersionException
     */
    public function getSourceMagentoVersion()
    {
        if (!$this->sourceMagentoVersion) {
            $this->sourceMagentoVersion = $this->getHostComposerJsonAsArray('source');
            $this->logger->info('Source Magento version', [$this->sourceMagentoVersion]);
        }

        return $this->sourceMagentoVersion;
    }

    /**
     * @return string
     * @throws MagentoVersionException
     */
    public function getDestinationMagentoVersion()
    {
        if (!$this->destinationMagentoVersion) {
            $this->destinationMagentoVersion = $this->getHostComposerJsonAsArray('destination');
            $this->logger->info('Destination Magento version', [$this->destinationMagentoVersion]);
        }

        return $this->destinationMagentoVersion;
    }


    /**
     * @param $type
     * @return string
     * @throws MagentoVersionException
     */
    private function getHostComposerJsonAsArray($type): string
    {
        $rootPath = $type === 'source' ? $this->hostConfiguration->getSourcePath() :
            $this->hostConfiguration->getDestinationPath();
        $composerJsonFile = $rootPath . 'composer.json';
        if (!file_exists($composerJsonFile)) {
            throw new MagentoVersionException('composer json file can not be found');
        }
        $composerJson = file_get_contents($composerJsonFile);
        $composerData = json_decode($composerJson, true);
        return $composerData['require']['magento/product-community-edition'] ?? '';
    }

}