<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Console\Command\Resource;

use Epicor\Comm\Logger\DatabaseCopy\Logger as DatabaseCopyLogger;

class HostConfiguration
{
    const ENV_CONFIG_PATH = 'app/etc/env.php';

    private $sourceEnvFile;
    private $destinationEnvFile;
    private $hostTypes = ['source', 'destination'];
    private $logger;
    private $directoryList;
    private $sourcePath;
    private $destinationPath;

    public function __construct(
        DatabaseCopyLogger $logger
    )
    {
        $this->logger = $logger;
    }

    public function getHostTypes(): array
    {
        return $this->hostTypes;
    }

    public function setSourcePath($path)
    {
        $this->sourcePath = $path;
    }

    public function setDestinationPath($path)
    {
        $this->destinationPath = $path;
    }

    public function getSourcePath()
    {
        return $this->sourcePath;
    }

    public function getDestinationPath()
    {
        return $this->destinationPath;
    }

    public function isIdenticalHostPaths()
    {
        if ($this->getSourcePath() && $this->getDestinationPath()) {
            return $this->getSourcePath() === $this->getDestinationPath();
        }
    }

    public function getSourceEnvFile()
    {
        if (!$this->sourceEnvFile) {
            $this->setHostEnvFilePaths();
        }

        return $this->sourceEnvFile;
    }

    public function getDestinationEnvFile()
    {
        if (!$this->destinationEnvFile) {
            $this->setHostEnvFilePaths();
        }

        return $this->destinationEnvFile;
    }

    private function setHostEnvFilePaths()
    {
        $this->logger->info('Setting host env file paths');

        if ($this->sourcePath && $this->destinationPath) {
            $this->sourceEnvFile = $this->sourcePath . self::ENV_CONFIG_PATH;
            $this->logger->info('Source Env file:', [$this->sourceEnvFile]);
            $this->destinationEnvFile = $this->destinationPath . self::ENV_CONFIG_PATH;
            $this->logger->info('Destination Env file:', [$this->destinationEnvFile]);
        } else {
            throw new \RuntimeException('Host path not set check configuration');
        }

    }
}