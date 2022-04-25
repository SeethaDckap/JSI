<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Console\Command\Resource;

use Epicor\Comm\Console\Command\Resource\DbConnection;
use Epicor\Comm\Console\Command\Resource\HostConfiguration as HostConfiguration;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;

class BackupDatabase
{
    const DATABASE_BACKUP_DIR = 'ecc_db_backup';

    private $hostConfiguration;
    private $sourceHostDumpFile;
    private $destinationHostDumpFile;
    private $sourceHostUrls;
    private $destinationHostUrls;
    private $sourceHostDatabaseInfo;
    private $destinationHostDatabaseInfo;
    private $dataObjectFactory;
    private $dbConnection;

    public function __construct(
        DbConnection $dbConnection,
        HostConfiguration $hostConfiguration,
        DataObjectFactory $dataObjectFactory
    )
    {
        $this->dbConnection = $dbConnection;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->hostConfiguration = $hostConfiguration;
    }

    public function getBackupPath()
    {
        return $this->hostConfiguration->getSourcePath() . self::DATABASE_BACKUP_DIR . '/';
    }

    public function getSourceHostDumpFile()
    {
        if (!$this->sourceHostDumpFile) {
            $this->sourceHostDumpFile = $this->getHostConfigData('source', 'dbname') . '_' . time() . '.sql';
        }

        return $this->sourceHostDumpFile;
    }

    public function getDestinationHostDumpFile()
    {
        if (!$this->destinationHostDumpFile) {
            $this->destinationHostDumpFile = $this->getHostConfigData('destination', 'dbname') . '_' . time() . '.sql';
        }

        return $this->destinationHostDumpFile;
    }

    public function getSourceDumpFilePath(): string
    {
        return $this->getBackupPath() .$this->getSourceHostDumpFile();
    }

    public function getDestinationDumpFilePath(): string
    {
        return $this->getBackupPath() . $this->getDestinationHostDumpFile();
    }

    public function getSourceDatabaseName()
    {
        return $this->getHostConfigData('source', 'dbname');
    }

    public function getDestinationDatabaseName()
    {
        return $this->getHostConfigData('destination', 'dbname');
    }

    public function getSourceDatabaseUser()
    {
        return $this->getHostConfigData('source', 'username');
    }

    public function getDestinationDatabaseUser()
    {
        return $this->getHostConfigData('destination', 'username');
    }

    public function getSourceDatabasePassword()
    {
        return $this->getHostConfigData('source', 'password');
    }

    public function getDestinationDatabasePassword()
    {
        return $this->getHostConfigData('destination', 'password');
    }

    public function getDestinationDatabaseHost()
    {
        return $this->getHostConfigData('destination', 'host');
    }

    public function dumpDatabase($type): bool
    {
        exec($this->getDumpCommand($type), $output, $return);

        return $return === 0;
    }

    private function getHostConfigData($type, $data)
    {
        if ($type === 'source') {
            $databaseInfo = $this->getSourceDatabaseInfo();
            if ($databaseInfo instanceof DataObject) {
                return $databaseInfo->getData($data);
            }
        } else {
            $databaseInfo = $this->getDestinationDatabaseInfo();
            if ($databaseInfo instanceof DataObject) {
                return $databaseInfo->getData($data);
            }
        }
    }

    private function getDumpCommand($type = 'source'): string
    {
        $user = $type === 'source' ? $this->getHostConfigData('source', 'username') :
            $this->getHostConfigData('destination', 'username');
        $databaseName = $type === 'source' ? $this->getHostConfigData('source', 'dbname') :
            $this->getHostConfigData('destination', 'dbname');
        $hostDumpFile = $type === 'source' ? $this->getSourceHostDumpFile() : $this->getDestinationHostDumpFile();
        $password = $type === 'source' ? $this->getHostConfigData('source', 'password') :
            $this->getHostConfigData('destination', 'password');

        return 'mysqldump -u' . $user . ' -p' . $password . ' ' . $databaseName . ' > ' .
            $this->getBackupPath() . $hostDumpFile;
    }


    public function getSourceHostUrls()
    {
        if (!$this->sourceHostUrls) {
            $config = $this->getDatabaseConfig('source');
            $this->sourceHostUrls = $this->getHostDbConfigUrlData($config);
        }

        return $this->sourceHostUrls;
    }

    /**
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    public function getDestinationHostUrls()
    {
        if (!$this->destinationHostUrls) {
            $config = $this->getDatabaseConfig('destination');
            $this->destinationHostUrls = $this->getHostDbConfigUrlData($config);
        }

        return $this->destinationHostUrls;
    }

    public function getDatabaseConfig($type): array
    {
        return [
            'driver' => 'Mysqli',
            'dbname' => $type === 'source' ?
                $this->getSourceDatabaseName() : $this->getDestinationDatabaseName(),
            'username' => $type === 'source' ?
                $this->getSourceDatabaseUser() : $this->getDestinationDatabaseUser(),
            'password' => $type === 'source' ?
                $this->getSourceDatabasePassword() : $this->getDestinationDatabasePassword(),
            'host' => $type === 'source' ?
                $this->getHostConfigData('source', 'host') : $this->getHostConfigData('destination', 'host')
        ];
    }

    public function setBackupDir()
    {
        if (!file_exists($this->getBackupPath())) {
            if (!mkdir($concurrentDirectory = $this->getBackupPath()) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }
    }

    /**
     * @param $type
     * @throws \Exception
     */
    private function setHostInfo($type)
    {
        if ($type === 'source') {
            $this->sourceHostDatabaseInfo = $this->getEnvDatabaseInfo($type);
        } else {
            $this->destinationHostDatabaseInfo = $this->getEnvDatabaseInfo($type);
        }
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getSourceDatabaseInfo()
    {
        if (!$this->sourceHostDatabaseInfo) {
            $this->setHostInfo('source');
        }

        return $this->sourceHostDatabaseInfo;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getDestinationDatabaseInfo()
    {
        if (!$this->destinationHostDatabaseInfo) {
            $this->setHostInfo('destination');
        }

        return $this->destinationHostDatabaseInfo;
    }

    /**
     * @param $type
     * @return DataObject
     * @throws \Exception
     */
    private function getEnvDatabaseInfo($type): DataObject
    {
        $envDataInfo = $this->getDataObject();

        $envPath = $this->getEnvFilePath($type);
        $this->setConfigurationObject($envPath, $envDataInfo);

        return $envDataInfo;
    }

    /**
     * @param $type
     * @return mixed
     */
    private function getEnvFilePath($type)
    {
        if (!$this->hostConfiguration->getSourceEnvFile() || !$this->hostConfiguration->getDestinationEnvFile()) {
            throw new \RuntimeException('Host paths not set for ' . $type);
        }
        return $type === 'source' ? $this->hostConfiguration->getSourceEnvFile() :
            $this->hostConfiguration->getDestinationEnvFile();
    }

    private function getDataObject(): DataObject
    {
        return $this->dataObjectFactory->create();
    }

    private function setConfigurationObject($envPath, $envDataInfo)
    {
        $envData = include $envPath;
        $dbName = $envData['db']['connection']['default']['dbname'] ?? '';
        $userName = $envData['db']['connection']['default']['username'] ?? '';
        $password = $envData['db']['connection']['default']['password'] ?? '';
        $host = $envData['db']['connection']['default']['host'] ?? '';

        if ($dbName && $userName && $password) {
            $envDataInfo->setData('dbname', $dbName);
            $envDataInfo->setData('username', $userName);
            $envDataInfo->setData('password', $password);
            $envDataInfo->setData('host', $host);
        }

        return $envDataInfo;
    }

    /**
     * @param $configArray
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    private function getHostDbConfigUrlData($configArray): array
    {
        $hostUrls = [];

        $add = $this->dbConnection->getDbConnection($configArray);
        $result = $add->query(
            'SELECT value, scope_id FROM core_config_data WHERE path = "web/unsecure/base_url"'
        )->fetchAll();
        foreach ($result as $storeUrl) {
            $stringReplace = str_replace('/', '', $storeUrl['value']);
            $stringReplace = str_replace(':', '', $stringReplace);
            $stringReplace = str_replace('http', '', $stringReplace);
            $hostUrls[$storeUrl['scope_id']] = $stringReplace;
        }

        return $hostUrls;
    }
}