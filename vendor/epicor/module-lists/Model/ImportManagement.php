<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Lists
 * @subpackage Model
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Epicor\Lists\Api\ImportRepositoryInterface;
use Epicor\Lists\Api\Data\ImportInterfaceFactory;
use Epicor\Lists\Model\Queue\Entity\MassUploadFactory;
use Magento\Framework\MessageQueue\PublisherInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ImportRepository
 *
 * @package Epicor\Lists\Model
 */
class ImportManagement
{
    /**
     * Pending Status.
     */
    const STATUS_PENDING = "Pending";

    /**
     * Pending Status.
     */
    const STATUS_SUCCESS = "Success";

    /**
     * Pending Status.
     */
    const STATUS_SUCCESS_WITH_WARNING = "Success with Warning";

    /**
     * Pending Status.
     */
    const STATUS_ERROR = "Error";

    /**
     * New folder inside pub media
     * for mass list.
     */
    const CSV_FOLDER = "masslist";

    /**
     * @var ImportRepositoryInterface
     */
    private $repository;

    /**
     * @var ImportInterfaceFactory
     */
    private $importFactory;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var MassUploadFactory
     */
    private $massUploadFactory;

    /**
     * @var PublisherInterface
     */
    private $messagePublisher;

    /**
     * @var
     */
    private $logger;

    /**
     * ImportManagement constructor.
     *
     * @param ImportRepositoryInterface $repository
     * @param ImportInterfaceFactory    $importFactory
     * @param DirectoryList             $directoryList
     * @param MassUploadFactory         $massUploadFactory
     * @param PublisherInterface        $publisherInterface
     * @param LoggerInterface           $logger
     */
    public function __construct(
        ImportRepositoryInterface $repository,
        ImportInterfaceFactory $importFactory,
        DirectoryList $directoryList,
        MassUploadFactory $massUploadFactory,
        PublisherInterface $publisherInterface,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->importFactory = $importFactory;
        $this->directoryList = $directoryList;
        $this->massUploadFactory = $massUploadFactory;
        $this->messagePublisher = $publisherInterface;
        $this->logger = $logger;
    }//end __construct()

    /**
     * Validate Folder
     *
     * @return bool
     */
    public function checkFileDir()
    {
        $filePath = $this->getFolderPath();
        if (!file_exists($filePath)) {
            mkdir($filePath, 0777, true);
            // possibly here create a .htaccess file that prevents access
        }

        return file_exists($filePath);
    }

    /**
     * Add to DB.
     *
     * @param $fileName
     */
    public function addNewFile($fileName)
    {
        /** @var Import $import */
        $import = $this->importFactory->create();
        $import->setFileName($fileName);
        $import->setStatus(self::STATUS_PENDING);
        //save
        $this->saveMassLog($import);
        $this->repository->save($import);

        try {
            // End mass list csv With Magento Queue Mechanism.
            /** @var \Epicor\Lists\Model\Queue\Entity\MassUpload $massUpload */
            $massUpload = $this->massUploadFactory->create();
            $massUpload->setFileName($fileName);
            $massUpload->setId($import->getId());
            $this->messagePublisher->publish('ecc.list.mass', $massUpload);
        } catch (\Exception $e) {
            $this->logger->error(__('Please correct the data sent value.'));
            $this->logger->critical($e);
        }//end try
    }

    /**
     * @param string $file
     *
     * @return string
     */
    public function getFilePath($file)
    {
        return $this->getFolderPath().DIRECTORY_SEPARATOR.$file;
    }

    /**
     * Path Folder Selection.
     *
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getFolderPath()
    {
        return $this->directoryList->getPath(
                \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
            ).DIRECTORY_SEPARATOR.self::CSV_FOLDER;
    }

    /**
     * @param $id string
     *
     * @return Import
     */
    public function getMassLogById($id)
    {
        return $this->repository->getById($id);
    }

    /**
     * Save.
     *
     * @param Import $import
     */
    public function saveMassLog($import)
    {
        return $this->repository->save($import);
    }


}//end class

