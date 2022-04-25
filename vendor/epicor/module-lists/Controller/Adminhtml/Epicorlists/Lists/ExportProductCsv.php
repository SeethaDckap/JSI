<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

use Epicor\Lists\Controller\Adminhtml\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Epicor\Lists\Model\ListModel\Product\Export;

/**
 * Class ExportProductCsv
 */
class ExportProductCsv extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{

    /**
     * FileFactory
     *
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    private $fileFactory;

    /**
     * Product Export
     *
     * @var Export
     */
    private $productExport;


    /**
     * ExportProductCsv constructor.
     *
     * @param Context     $context            Context.
     * @param Session     $backendAuthSession BackendAuthSession.
     * @param FileFactory $fileFactory        FileFactory.
     * @param Filesystem  $filesystem         Filesystem.
     * @param Export      $produceExport      ProduceExport.
     *
     * @throws \Magento\Framework\Exception\FileSystemException FileSystemException.
     */
    public function __construct(
        Context $context,
        Session $backendAuthSession,
        FileFactory $fileFactory,
        Filesystem $filesystem,
        Export $produceExport
    ) {
        $this->fileFactory   = $fileFactory;
        $this->productExport = $produceExport;

        parent::__construct($context, $backendAuthSession);

    }//end __construct()


    /**
     * Export list product with price report grid to CSV format
     *
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\FileSystemException FileSystemException.
     */
    public function execute()
    {
        $listId   = $this->getRequest()->getParam('id');
        $fileName = $this->productExport->getCsvFileName().$this->productExport->getFileExtension();
        $data     = $this->productExport->setProductListExport($listId);

        return $this->fileFactory->create($fileName, $data, DirectoryList::VAR_DIR);

    }//end execute()


}
