<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Log;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportXml extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Log
{

    private $_fileFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Comm\Model\Message\LogFactory $commMessageLogFactory,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        $this->_fileFactory = $fileFactory;
        parent::__construct($context, $commMessageLogFactory, $commMessagingHelper, $backendAuthSession);
    }
    public function execute()
    {
        $fileName = 'messages.xml';
        //M1 > M2 Translation Begin (Rule 2)
        //$content = $this->getLayout()->createBlock('epicor_comm/adminhtml_message_log_grid')
        //    ->getExcelFile();

        //$this->_prepareDownloadResponse($fileName, $content);

        /** @var \Magento\Backend\Block\Widget\Grid\ExportInterface $exportBlock  */
        $exportBlock = $this->_view->getLayout()->createBlock('Epicor\Comm\Block\Adminhtml\Message\Log\Grid');

        return $this->_fileFactory->create(
            $fileName,
            $exportBlock->getExcelFile(),
            DirectoryList::VAR_DIR
        );
        //M1 > M2 Translation End
    }

    }
