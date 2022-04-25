<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Locations;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportCsv extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Locations
{
    private $_fileFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory)
    {
        $this->_fileFactory = $fileFactory;
        parent::__construct($context, $backendAuthSession);
    }

    /**
     * Export locations grid to CSV format
     */
    public function execute()
    {
        $fileName = 'locations.csv';
        //M1 > M2 Translation Begin (Rule 2)
        //$content = $this->getLayout()->createBlock('epicor_comm/adminhtml_locations_list_grid')
        //    ->getCsvFile();

        //$this->_prepareDownloadResponse($fileName, $content);

        /** @var \Magento\Backend\Block\Widget\Grid\ExportInterface $exportBlock  */
        $exportBlock = $this->_view->getLayout()->createBlock('Epicor\Comm\Block\Adminhtml\Locations\Listing\Grid');

        return $this->_fileFactory->create(
            $fileName,
            $exportBlock->getCsvFile(),
            DirectoryList::VAR_DIR
        );
        //M1 > M2 Translation End
    }

    }
