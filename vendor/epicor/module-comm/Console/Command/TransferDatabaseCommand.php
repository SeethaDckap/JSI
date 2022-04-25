<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Console\Command;

use Epicor\Comm\Console\Command\Resource\DbConnection;
use Epicor\Comm\Console\Command\Resource\HostConfiguration as HostConfiguration;
use Epicor\Comm\Logger\DatabaseCopy\Logger as DatabaseCopyLogger;
use Epicor\Comm\Console\Command\Resource\EccVersion;
use Epicor\Comm\Console\Command\Resource\MagentoVersion;
use Epicor\Comm\Console\Command\Resource\BackupDatabase;
use RuntimeException;
use Exception;
use Epicor\Comm\Console\Command\Resource\MagentoVersionException as MagentoVersionException;
use Epicor\Comm\Console\Command\Resource\EccVersionException as EccVersionException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TransferDatabaseCommand extends Command
{
    private $outputStyle;
    private $output;
    private $input;
    private $hostTypes = ['source', 'destination'];
    private $envFilePath = 'app/etc/env.php';
    private $availableScopes;
    private $dbConnection;
    private $logger;
    private $hostConfiguration;
    private $eccVersion;
    private $magentoVersion;
    private $backupDatabase;
    private $isDestinationDatabaseDumped = false;
    private $eccVersionMatch = true;
    private $magentoVersionMatch = true;

    public function __construct(
        DbConnection $dbConnection,
        DatabaseCopyLogger $logger,
        HostConfiguration $hostConfiguration,
        EccVersion $eccVersion,
        MagentoVersion $magentoVersion,
        BackupDatabase $backupDatabase
    ) {
        parent::__construct();
        $this->dbConnection = $dbConnection;
        $this->logger = $logger;
        $this->hostConfiguration = $hostConfiguration;
        $this->eccVersion = $eccVersion;
        $this->magentoVersion = $magentoVersion;
        $this->backupDatabase = $backupDatabase;
    }

    protected function configure()
    {
        $this->setName('comm:transfer:database');
        $this->setDescription('Transfers the current database from one host to another');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->setHelperSet($this->getApplication()->getHelperSet());
        $this->outputStyle = new SymfonyStyle($this->input, $this->output);
        $this->logger->info('beginning database transfer');

        $this->transferDatabase();
    }

    private function transferDatabase()
    {
        $this->logger->info('Transfer started by ' . $this->getCurrentUser());
        try {
            if ($this->getHostRootPaths() && $this->backupDatabases()) {
                $this->replaceUrlsInDatabaseDump();
                $this->dropTargetDatabase();
                $this->createDestinationDatabase();
                $this->importDatabase();
                $this->setDefaultAdminPassword();
                $this->writeLine('Operation complete');
                $this->logger->info('Transfer completed for user ' . $this->getCurrentUser());
            }
        } catch (RuntimeException $e) {
            $this->reportException($e);
            if($this->isDestinationDatabaseDumped){
                $this->restoreDestinationDatabase();
            }
        } catch (Exception $e) {
            $this->reportException($e);
            if($this->isDestinationDatabaseDumped){
                $this->restoreDestinationDatabase();
            }
        }
    }

    private function reportException(Exception $e)
    {
        $this->writeErrorLine($e->getMessage());
        $this->logger->error($e->getMessage());
        $this->logger->error($e->getTraceAsString());
    }

    private function getCurrentUser()
    {
        return exec('who');
    }

    private function restoreDestinationDatabase()
    {
        try {
            $this->writeLine('Performing restore of Destination database');
            $this->createDestinationDatabase();
            $databaseName = $this->backupDatabase->getDestinationDatabaseName();
            $host = $this->backupDatabase->getDestinationDatabaseHost();
            $userName = $this->backupDatabase->getDestinationDatabaseUser();
            $password = $this->backupDatabase->getDestinationDatabasePassword();
            $backupPath = $this->backupDatabase->getBackupPath() . $this->backupDatabase->getDestinationHostDumpFile();
            $command = "mysql -h$host -u$userName -p$password $databaseName < $backupPath";
            if (!$this->runShellCommand($command)) {
                throw new RuntimeException('Error restoring database');
            }
            $this->writeLine('Restore complete');
        } catch (RuntimeException $e) {
            $this->reportException($e);
        } catch (Exception $e) {
            $this->reportException($e);
        }
    }


    /**
     * @throws \Zend_Db_Statement_Exception
     */
    private function createDestinationDatabase()
    {
        $sourceDbConfig = $this->backupDatabase->getDatabaseConfig('source');
        $destinationDatabase = $this->backupDatabase->getDestinationDatabaseName();
        $connection = $this->dbConnection->getDbConnection($sourceDbConfig);
        $connection->query(
            "create database if not exists $destinationDatabase"
        )->execute();
    }

    private function setHostPath($type)
    {
        $message = $type === 'source' ? 'Enter the source host root path' : 'Enter the destination host root path';
        if ($type === 'source') {
            $this->hostConfiguration->setSourcePath($this->getCurrentPath() . '/');
        } else {
            $responseDestinationPath = $this->getUserResponse($message);
            $this->hostConfiguration->setDestinationPath($responseDestinationPath);
        }
    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function getHostRootPaths(): bool
    {
        foreach ($this->hostTypes as $type) {
            $this->setHostPath($type);
        }

        if ($this->hostConfiguration->isIdenticalHostPaths()) {
            $this->writeErrorLine('The source and destination paths are the same.');

            return false;
        }

        if (!$this->isUserInValidRootPath()) {
            $this->writeErrorLine(
                'Your present working directory is not the root of source or the destination host'
            );

            return false;
        }

        try {
            if (!$this->eccVersion->isMatchingEccVersions()) {
                $this->eccVersionMatch = false;
                throw new EccVersionException('The ECC versions for source and destination do not match!');
            }
        } catch (EccVersionException $e) {
            $this->reportException($e);
        } catch (Exception $e) {
            $this->reportException($e);
            exit();
        }
        try {
            if (!$this->magentoVersion->isMatchingMagentoVersion()) {
                $this->magentoVersionMatch = false;
                throw new MagentoVersionException('Magento versions for source and destination do not match!');
            }
        } catch (MagentoVersionException $e) {
            $this->reportException($e);
        }

        if ($this->eccVersionMatch) {
            $this->writeLine('Ecc versions: ', 'Match ok!');
        }
        if ($this->magentoVersionMatch) {
            $this->writeLine('Magento versions: ', 'Match ok!');
        }
        if (!$this->eccVersionMatch || !$this->magentoVersionMatch) {
            throw new RuntimeException('Error checking version compatibility!');
        }

        $this->confirmHostUrls();
        $this->displayAndConfirmDetails();

        if ($this->isTargetHostConfirmed()) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function backupDatabases()
    {
        $this->backupDatabase->setBackupDir();
        $this->writeLine('Backing up source database ...');
        if (!$this->backupDatabase->dumpDatabase('source')) {
            throw new RuntimeException('Error backing up source database, execution halted');
        }
        $this->writeLine('Source backup complete >> ' . $this->backupDatabase->getSourceDumpFilePath());
        $this->writeLine('Backing up destination database ...');
        if (!$this->backupDatabase->dumpDatabase('destination')) {
            throw new RuntimeException('Error backing up destination database, execution halted');
        }
        $this->writeLine('Source backup complete >> ' . $this->backupDatabase->getDestinationDumpFilePath());

        return true;
    }

    private function getCurrentPath()
    {
        return exec('pwd');
    }

    private function isUserInValidRootPath(): bool
    {
        return $this->getCurrentPath() . '/' === $this->hostConfiguration->getSourcePath();
    }

    /**
     * @throws \Zend_Db_Statement_Exception
     */
    private function setDefaultAdminPassword()
    {
        $this->writeLine('Setting admin user password for destination DB');
        $password = $this->getPasswordFromUser();
        try {
            $setSalt = "SET @salt = MD5(UNIX_TIMESTAMP());";
            $updateSql = "UPDATE admin_user 
                            SET password = CONCAT(SHA2(CONCAT(@salt, '$password'), 256), ':', @salt, ':1') 
                            WHERE username = 'admin';";
            $destinationDbConfig = $this->backupDatabase->getDatabaseConfig('destination');
            $connection = $this->dbConnection->getDbConnection($destinationDbConfig);
            $connection->query($setSalt);
            $connection->query($updateSql);
            $this->logger->info('Password set for admin');
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    private function getPasswordFromUser()
    {
        $matchPasswords = false;
        $userPassword = '';
        while (!$matchPasswords) {
            if ($userPassword = $this->validatePasswordMatch()) {
                $matchPasswords = true;
            }
        }

        return $userPassword;
    }

    private function validatePasswordMatch()
    {
        $validation = function ($userResponse) {
            if (strlen($userResponse) < 8) {
                $this->writeLine('Password is too short, it needs to be 8 or more characters', '', true);
                return false;
            }
            return $userResponse;
        };

        $firstResponse = $this->outputStyle->askHidden('Enter your admin password: ', $validation);
        if (!$firstResponse) {
            return false;
        }
        $secondResponse = $this->outputStyle->askHidden('Reenter password: ', $validation);

        return $firstResponse === $secondResponse ?
            $firstResponse : $this->writeLine('Passwords do not match..', '', true);
    }

    private function isMatchingUrlScopes(): bool
    {
        return empty(array_diff_key(
            $this->backupDatabase->getDestinationHostUrls(),
            $this->backupDatabase->getSourceHostUrls()
        )) ? true : false;
    }

    /**
     * @throws \Exception
     */
    private function confirmHostUrls()
    {
        if (!$this->isMatchingUrlScopes()) {
            throw new \Exception('Number of stores differ unable to match host urls');
        }

        $this->setAvailableScopes();
    }

    private function setAvailableScopes()
    {
        $this->availableScopes = array_keys(
            array_intersect_key(
                $this->backupDatabase->getSourceHostUrls(),
                $this->backupDatabase->getDestinationHostUrls()
            ));
    }

    private function displayHostUrl()
    {
        $tableData = [];

        $this->outputStyle->title('Host url replacements');
        $this->outputStyle->note(
            [
                'This displays what urls will be set in destination database ',
                'Example if your destination host is test-site.com and the source ',
                'is live-site.com, the destination database will have its url set to ',
                'test-site.com'
            ]
        );
        $tableHeader[] = [
            $this->getCommentText('scope'),
            $this->getCommentText('source url'),
            $this->getCommentText('replaced with'),
        ];

        foreach ($this->availableScopes as $scope) {
            $sourceHostUrls = $this->backupDatabase->getSourceHostUrls();
            $destinationHostUrls = $this->backupDatabase->getDestinationHostUrls();
            $tableData[] = [
                $this->getInfoText($scope),
                $this->getInfoText($sourceHostUrls[$scope] ?? ''),
                $this->getInfoText($destinationHostUrls[$scope] ?? ''),
            ];
        }

        $this->outputStyle->table($tableHeader, $tableData);
    }

    private function displayAndConfirmDetails()
    {
        $this->displayHostInfo();
        $this->displayHostUrl();

        $this->outputStyle->caution(
            [
                'The following process involves replacing the Destination database.',
                'Check the above details before proceeding !!!'
            ]
        );
    }

    private function getUserConfirmation()
    {
        return $this->outputStyle->confirm('Are you sure you want to continue?', false);
    }

    private function isTargetHostConfirmed(): bool
    {
        $userResponse = $this->getUserConfirmation();
        if (!$userResponse) {
            $this->logger->info('Transfer cancelled by user ');
            $this->outputStyle->note('Transfer cancelled by user');
            return false;
        }
        $this->logger->info('User confirms transfer to proceed');

        return true;
    }

    private function writeLine($comment, $info = '', $errorType = false)
    {
        if ($errorType) {
            $this->logger->error($comment);
            $this->output->writeln("<error>$comment</error>");
        } else {
            $this->logger->info($comment);
            $this->output->writeln($this->getCommentInfoText($comment, $info));
        }
    }

    private function getInfoText($text): string
    {
        return "<info>$text</info>";
    }

    private function getCommentText($text): string
    {
        return "<comment>$text</comment>";
    }

    private function writeErrorLine($message)
    {
        $this->outputStyle->error($message);
    }

    private function getCommentInfoText($comment, $info = ''): string
    {
        return $info === '' ?
            $this->getCommentText($comment) : $this->getCommentText($comment) . $this->getInfoText($info);
    }

    /**
     * @throws \Exception
     */
    private function importDatabase()
    {
        $this->writeLine('Importing to destination database...');
        $user = $this->backupDatabase->getDestinationDatabaseUser();
        $password = $this->backupDatabase->getDestinationDatabasePassword();
        $dbName = $this->backupDatabase->getDestinationDatabaseName();
        $sourceHostDumpFile = $this->backupDatabase->getSourceHostDumpFile();
        $dumpFilePath = $this->backupDatabase->getBackupPath() . $sourceHostDumpFile;

        if (!$this->runShellCommand("mysql -u $user -p$password $dbName < $dumpFilePath")) {
            throw new \Exception('Error importing database to destination');
        }
        $this->writeLine('Import complete...');
    }

    /**
     * @throws \Zend_Db_Statement_Exception
     */
    private function replaceUrlsInDatabaseDump()
    {
        $this->logger->info('Replacing urls for destination database');
        $sourceHostUrls = $this->backupDatabase->getSourceHostUrls();
        $destinationHostUrls = $this->backupDatabase->getDestinationHostUrls();
        foreach ($this->availableScopes as $scopeKey) {
            $sourceUrl = $sourceHostUrls[$scopeKey];
            $destinationUrl = $destinationHostUrls[$scopeKey];
            $this->replaceUrls($sourceUrl, $destinationUrl);
        }
    }

    /**
     * @param $sourceUrl
     * @param $destinationUrl
     * @throws \Exception
     */
    private function replaceUrls($sourceUrl, $destinationUrl)
    {
        $sourceHostDumpFile = $this->backupDatabase->getSourceHostDumpFile();
        $dumpFilePath = $this->backupDatabase->getBackupPath() . $sourceHostDumpFile;
        $sedExpression = "sed -i 's/$sourceUrl/$destinationUrl/g' $dumpFilePath";
        $this->writeLine('Replacing source url ' . $sourceUrl . ' with destination url ' . $destinationUrl);
        if (!$this->runShellCommand($sedExpression)) {
            throw new \Exception('Error replacing urls in destination file. Execution halted');
        }
    }

    private function getCleanPath($userResponse)
    {
        if ($this->isPathWithoutTrailingSlash($userResponse)) {
            return $userResponse . '/';
        }

        if ($this->isPathWithTrailingSlash($userResponse)) {
            return $userResponse;
        }
    }

    private function isPathWithTrailingSlash($path): bool
    {
        return preg_match('/^\/[a-z0-9\/]*[\/]$/', $path) === 1;
    }

    private function isPathWithoutTrailingSlash($path): bool
    {
        return preg_match('/^\/[a-z0-9\/]*[^\/]$/', $path) === 1;
    }

    /**
     * @throws \Exception
     */
    private function dropTargetDatabase()
    {
        $databaseName = $this->backupDatabase->getDestinationDatabaseName();
        $host = $this->backupDatabase->getDestinationDatabaseHost();
        $userName = $this->backupDatabase->getDestinationDatabaseUser();
        $password = $this->backupDatabase->getDestinationDatabasePassword();
        $command = "mysqladmin -f -h$host -u$userName -p$password drop $databaseName";
        if (!$this->runShellCommand($command)) {
            throw new \Exception('Error occurred dropping database, execution halted');
        }
        $this->isDestinationDatabaseDumped = true;
        $this->writeLine('Dropped destination database');
    }

    private function runShellCommand($command): bool
    {
        exec($command, $output, $return);
        return $return === 0;
    }

    private function displayHostInfo()
    {
        $this->writeLine('');
        $hostInfoTitles = ['source' => 'Source host details', 'destination' => 'Destination host details'];
        foreach ($this->hostTypes as $type) {
            $title = $hostInfoTitles[$type] ?? '';
            $this->outputStyle->title($title);
            $rootPath = $type === 'source' ? $this->hostConfiguration->getSourcePath() :
                $this->hostConfiguration->getDestinationPath();
            $dbName = $type === 'source' ? $this->backupDatabase->getSourceDatabaseName() :
                $this->backupDatabase->getDestinationDatabaseName();
            $dbUser = $type === 'source' ? $this->backupDatabase->getSourceDatabaseUser() :
                $this->backupDatabase->getDestinationDatabaseUser();
            $dbPassword = $type === 'source' ? $this->backupDatabase->getSourceDatabasePassword() :
                $this->backupDatabase->getDestinationDatabasePassword();

            $this->outputStyle->table([],
                [
                    [
                        $this->getCommentText('Root path:'),
                        $this->getInfoText($rootPath)
                    ],
                    [
                        $this->getCommentText('Database name:  '),
                        $this->getInfoText($dbName)
                    ],
                    [
                        $this->getCommentText('Database user name:  '),
                        $this->getInfoText($dbUser)
                    ],
                    [
                        $this->getCommentText('Password:  '),
                        $this->getInfoText($dbPassword)
                    ]
                ]);
        }
    }

    private function getUserResponse($message)
    {
        $userResponse = false;
        try {
            while ($userResponse === false) {
                $userResponse = $this->outputStyle->ask($message, null, function ($response) {
                    $hostPath = $this->getCleanPath($response);
                    $envPath = $hostPath . $this->envFilePath;
                    if (!$hostPath || !file_exists($envPath)) {
                        throw new \Exception('Not a valid root path');
                    }
                    return $hostPath;
                });
            }
        } catch (\Exception $e) {
            $this->outputStyle->warning($e->getMessage());
            $userResponse = false;
        }

        return $userResponse;
    }
}
