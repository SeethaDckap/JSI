<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Reports\Controller\Adminhtml;


abstract class Reports extends \Epicor\Comm\Controller\Adminhtml\Generic
{

    /**
     * @var \Magento\Framework\Filesystem\Io\FileFactory
     */
    protected $ioFileFactory;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    public function __construct(
        \Magento\Framework\Filesystem\Io\FileFactory $ioFileFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Epicor\Lists\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        $this->ioFileFactory = $ioFileFactory;
        $this->directoryList = $directoryList;

        parent::__construct($context, $backendAuthSession);
    }
    
    protected function _initPage()
    {
        // Load layout, set active menu and breadcrumbs
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Epicor_Reports::repor');
        $resultPage->getConfig()->getTitle()->prepend(__('Messaging Reports'));
        $resultPage->addBreadcrumb(__('Form'), __('Form'));
        return $resultPage;

    }
    
    
    function getCsvFile($headers, $rows)
    {

        $io = $this->ioFileFactory->create();

        //M1 > M2 Translation Begin (Rule p2-5.5)
        //$path = Mage::getBaseDir('var') . '/' . 'export' . DS;
        $path = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR) . '/' . 'export' . DIRECTORY_SEPARATOR;
        //M1 > M2 Translation End
        $name = md5(microtime());
        $file = $path . '/' . $name . '.csv';

        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $path));
       // $io->streamOpen($file, 'w+');
        $io->streamLock(true);
        $io->streamWriteCsv($headers);

        foreach ($rows as $row) {
            $io->streamWriteCsv(array_values($row));
        }

        $io->streamUnlock();
        $io->streamClose();

        return array(
            'type' => 'filename',
            'value' => $file,
            'rm' => true // can delete file after use
        );
    }

}
