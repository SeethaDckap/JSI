<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\File\WriteInterface;

/**
 * Class Export
 */
class Export extends AbstractModel
{

    /**
     * Additional path to folder
     *
     * @var string
     */
    private $path = 'export/list/';

    /**
     * Additional path to folder
     *
     * @var string
     */
    private $filename = 'list';

    /**
     * File path with file name.
     *
     * @var string
     */
    public $filePath = '';

    /**
     * Header Data.
     *
     * @var array
     */
    private $header = [];

    /**
     * File Extension.
     *
     * @var string
     */
    private $fileExtension = '.csv';

    /**
     * Directory WriteInterface
     *
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $directory;


    /**
     * Export constructor.
     *
     * @param Context               $context            Context.
     * @param Registry              $registry           Registry.
     * @param Filesystem            $filesystem         Filesystem.
     * @param AbstractResource|null $resource           AbstractResource.
     * @param AbstractDb|null       $resourceCollection AbstractDb.
     * @param array                 $data               Data.
     *
     * @throws \Magento\Framework\Exception\FileSystemException FileSystemException.
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Filesystem $filesystem,
        AbstractResource $resource=null,
        AbstractDb $resourceCollection=null,
        array $data=[]
    ) {
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        parent::__construct($context, $registry, $resource, $resourceCollection);

    }//end __construct()


    /**
     * Retrieve a file container array by grid data as CSV
     *
     * Return array with keys type and value
     *
     * @return WriteInterface
     * @throws \Magento\Framework\Exception\FileSystemException FileSystemException.
     */
    public function initCsvFile()
    {
        $name           = md5(microtime());
        $fileName       = $this->getFileName().'_';
        $this->filePath = $this->path.'/'.$fileName.$name.$this->getFileExtension();

        $this->directory->create($this->path);
        $stream = $this->directory->openFile($this->filePath, 'w+');
        $stream->lock();

        $this->setCsvHeader($stream);

        return $stream;

    }//end initCsvFile()


    /**
     * Set csv header.
     *
     * @param WriteInterface $stream WriteInterface.
     *
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException FileSystemException.
     */
    public function setCsvHeader(WriteInterface $stream)
    {
        $header = $this->getHeader();
        $this->writeCsv($stream, $header);

    }//end setCsvHeader()


    /**
     * Close Csv.
     *
     * @param WriteInterface $stream WriteInterface.
     */
    public function closeCsv(WriteInterface $stream)
    {
        $stream->unlock();
        $stream->close();
    }//end closeCsv()


    /**
     * Header Data
     *
     * @param array $data Header Data.
     *
     * @return array
     */
    public function setHeader(array $data)
    {
        $this->header = $data;
        return $this->header;

    }//end setHeader()


    /**
     * Retrieve Headers row array for Export
     *
     * @return array
     */
    public function getHeader()
    {
        return $this->header;

    }//end getHeader()


    /**
     * Set file name for dir save.
     *
     * @param string $filename FileName.
     *
     * @return string
     */
    public function setFileName(string $filename)
    {
        $this->filename = $filename;
        return $this->filename;

    }//end setFileName()


    /**
     * Retrieve filename row array for Export
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->filename;

    }//end getFileName()

    /**
     * Retrieve filename row array for Export
     *
     * @return string
     */
    public function getFileExtension()
    {
        return $this->fileExtension;

    }//end getFileName()


    /**
     * We use write function instead of writeCsv
     * Reason: core fn not able make string without enclosure.
     *
     * @param WriteInterface $stream Stream.
     * @param array         $data   Row data.
     *
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException FileSystemException.
     */
    public function writeCsv(WriteInterface $stream, array $data)
    {
        $newLine = "\n";
        $data = implode(',', $data);
        $stream->write($data.$newLine);

    }//end writeCsv()


}
