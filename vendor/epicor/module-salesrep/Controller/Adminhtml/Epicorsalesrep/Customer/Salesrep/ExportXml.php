<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Salesrep;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportXml extends \Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Salesrep
{

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    private $_fileFactory;

    public function __construct(
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Context $context
    )
    {
        $this->_fileFactory = $fileFactory;
        parent::__construct($context);
    }


/**
     * Export Sales Rep Accounts grid to XML format
     */
    public function execute()
    {
        $fileName = 'salesrepaccounts.xml';
        //M1 > M2 Translation Begin (Rule 2)
        //$content = $this->getLayout()->createBlock('epicor_salesrep/adminhtml_customer_salesrep_grid')
        //    ->getExcelFile();

        //$this->_prepareDownloadResponse($fileName, $content);

        /** @var \Magento\Backend\Block\Widget\Grid\ExportInterface $exportBlock  */
        $exportBlock = $this->_view->getLayout()->createBlock('Epicor\SalesRep\Block\Adminhtml\Customer\Salesrep\Grid');

        return $this->_fileFactory->create(
            $fileName,
            $exportBlock->getExcelFile(),
            DirectoryList::VAR_DIR
        );
        //M1 > M2 Translation End
    }

}
