<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

use Magento\Framework\App\Filesystem\DirectoryList;


class ExportCsv extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{

    private $_fileFactory;


    public function __construct(
        \Epicor\Lists\Controller\Adminhtml\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        $this->_fileFactory = $fileFactory;

        parent::__construct($context, $backendAuthSession);
    }


    /**
     * Export list grid to CSV format
     *
     *
     */
    public function execute()
    {
        $fileName = 'list.csv';
        //M1 > M2 Translation Begin (Rule 2)
        //$content = $this->getLayout()->createBlock('epicor_lists/adminhtml_listing_list_grid')
        //    ->getCsvFile();

        //$this->_prepareDownloadResponse($fileName, $content);


        /** @var \Magento\Backend\Block\Widget\Grid\ExportInterface $exportBlock  */
        $exportBlock = $this->_view->getLayout()->createBlock('Epicor\Lists\Block\Adminhtml\Listing\Grid');

        return $this->_fileFactory->create(
            $fileName,
            $exportBlock->getCsvFile(),
            DirectoryList::VAR_DIR
        );
        //M1 > M2 Translation End
    }

}
