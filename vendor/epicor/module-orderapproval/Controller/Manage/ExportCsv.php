<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Controller\Manage;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Epicor\Customerconnect\Helper\Data as CustomerConnectData;
use Epicor\AccessRight\Controller\Action as AccessRightAction;

class ExportCsv extends AccessRightAction
{
    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var CustomerConnectData
     */
    private $data;

    /**
     * ExportCsv constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param DirectoryList $directoryList
     * @param FileFactory $fileFactory
     * @param CustomerConnectData $data
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        DirectoryList $directoryList,
        FileFactory $fileFactory,
        CustomerConnectData $data
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->data = $data;
    }

    /**
     * Export Approval/Reject grid to CSV format
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $baseUrl = $this->data->urlWithoutHttp();
        $fileName = $baseUrl . '_approval.csv';
        /** @var \Magento\Backend\Block\Widget\Grid\ExportInterface $exportBlock */
        $exportBlock = $this->_view->getLayout()->createBlock(
            'Epicor\OrderApproval\Block\Approvals\Listing\Grid',
            '',
            ['data' => ['is_export' => true]]
        );

        return $this->fileFactory->create(
            $fileName,
            $exportBlock->getCsvFile(),
            DirectoryList::VAR_DIR
        );
    }
}
