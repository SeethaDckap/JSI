<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Controller\Manage;

use Magento\Framework\App\Filesystem\DirectoryList;
use Epicor\AccessRight\Controller\Action as AccessRightAction;
use Epicor\Customerconnect\Helper\Data as CustomerConnectData;
use Magento\Framework\App\Response\Http\FileFactory;

class ExportXml extends AccessRightAction
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
     * ExportXml constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param FileFactory $fileFactory
     * @param CustomerConnectData $data
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        FileFactory $fileFactory,
        CustomerConnectData $data
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->data = $data;
    }

    /**
     * Export Approve/reject grid to XML format
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $baseUrl = $this->data->urlWithoutHttp();
        $fileName = $baseUrl . '_approval.xml';

        /** @var \Magento\Backend\Block\Widget\Grid\ExportInterface $exportBlock */
        $exportBlock = $this->_view->getLayout()->createBlock(
            'Epicor\OrderApproval\Block\Approvals\Listing\Grid',
            '',
            ['data' => ['is_export' => true]]
        );
        return $this->fileFactory->create(
            $fileName,
            $exportBlock->getExcelFile(),
            DirectoryList::VAR_DIR
        );
    }
}
