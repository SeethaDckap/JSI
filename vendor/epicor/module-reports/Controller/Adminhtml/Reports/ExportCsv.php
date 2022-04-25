<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Reports\Controller\Adminhtml\Reports;

class ExportCsv extends \Epicor\Reports\Controller\Adminhtml\Reports
{

    /**
     * @var \Epicor\Reports\Helper\Data
     */
    protected $reportsHelper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    public function __construct(
        \Magento\Framework\Filesystem\Io\FileFactory $ioFileFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Epicor\Lists\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Reports\Helper\Data $reportsHelper
    ) {
        $this->reportsHelper = $reportsHelper;
        $this->messageManager = $context->getMessageManager();
        parent::__construct($ioFileFactory, $directoryList, $context, $backendAuthSession);
    }
    public function execute()
    {
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename= messaging_report.csv");
        header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
        header("Pragma: no-cache"); // HTTP 1.0
        header("Expires: 0"); // Proxies   
        
        /**
         * @var $helper Epicor_Reports_Helper_Data
         * @var $testBlock Epicor_Comm_Block_Adminhtml_Message_Log_Grid
         */
        $chartOptions = $this->getRequest()->getPost();
        $helper = $this->reportsHelper;
        $results = $helper->chartResults($chartOptions);
        if (sizeof($results) > 0) {
            $fileName = 'messaging_report.csv';
            $headers = sizeof($results) > 0 ? array_keys($results[0]) : array();
            $content = implode(',',$headers);
            $content .=  "\n";
            foreach ($results as $row) {
             //   print_r(implode(',',array_values($row))); exit;
                $content .= implode(',',array_values($row));
                $content .=  "\n";
             //$io->streamWriteCsv(array_values($row));
            };
            //$this->_prepareDownloadResponse($fileName, $content);
              $this->getResponse()->setBody($content);
        } else {

            //M1 > M2 Translation Begin (Rule p2-5.1)
            //Mage::getSingleton('reports/session')->addWarning(__('No data to export'));
            $this->messageManager->addWarningMessage(__('No data to export'));
            //M1 > M2 Translation End
            $this->_redirect('*/*/index');
        }
    }

    }
