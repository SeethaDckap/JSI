<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Crqs;

use Magento\Framework\App\Filesystem\DirectoryList;
class ExportXml extends \Epicor\SalesRep\Controller\Crqs
{

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    private $_fileFactory;
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;
    
    public function __construct(
        \Epicor\SalesRep\Controller\Context $context,    
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->customerconnectHelper = $context->getCustomerconnectHelper();
        $this->_fileFactory = $fileFactory;
        $this->resultLayoutFactory = $context->getResultLayoutFactory();
         parent::__construct(
            $context
        );
    }
    /**
     * Export invoices grid to XML format
     */
    public function execute()
    {
        $baseUrl = $this->customerconnectHelper->urlWithoutHttp();
        $fileName = $baseUrl . 'crq.xml';

        //M1 > M2 Translation Begin (Rule 2)
        //$content = $this->getLayout()->createBlock('epicor_salesrep/crqs_list_grid')
        //    ->getExcelFile();

        //$this->_prepareDownloadResponse($fileName, $content);

        /** @var \Magento\Backend\Block\Widget\Grid\ExportInterface $exportBlock  */
        $layout = $this->resultLayoutFactory->create(); 
        $exportBlock = $layout->getLayout()->createBlock('Epicor\SalesRep\Block\Crqs\Listing\Grid');
        
        return $this->_fileFactory->create(
            $fileName,
            $exportBlock->getExcelFile(),
            DirectoryList::VAR_DIR
        );
        //M1 > M2 Translation End
    }

}
