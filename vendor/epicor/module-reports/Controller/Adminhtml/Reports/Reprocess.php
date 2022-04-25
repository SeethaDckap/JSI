<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Reports\Controller\Adminhtml\Reports;

class Reprocess extends \Epicor\Reports\Controller\Adminhtml\Reports
{

    /**
     * @var \Epicor\Reports\Model\RawdataFactory
     */
    protected $reportsRawdataFactory;

    public function __construct(
        \Magento\Framework\Filesystem\Io\FileFactory $ioFileFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Epicor\Lists\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Reports\Model\RawdataFactory $reportsRawdataFactory
    ) {
        $this->reportsRawdataFactory = $reportsRawdataFactory;
        parent::__construct($ioFileFactory, $directoryList, $context, $backendAuthSession);
    }
    public function execute()
    {
        /* @var $model Epicor_Reports_Model_Rawdata */
        $model = $this->reportsRawdataFactory->create();
        $model->reprocessMessageLogData();
        $this->_redirect('*/*/index');
    }

    }
