<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Controller\Invoices;

use Magento\Framework\App\Filesystem\DirectoryList;
class ExportInvoicesCsv extends \Epicor\Customerconnect\Controller\Invoices
{

    private $_fileFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Customerconnect\Model\Message\Request\Cuid $customerconnectMessageRequestCuid,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    )
    {
        $this->_fileFactory = $fileFactory;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $customerconnectHelper,
            $request,
            $customerconnectMessageRequestCuid,
            $registry,
            $generic,
            $urlDecoder,
            $encryptor
        );
    }

    /**
     * Export Invoices grid to CSV format
     */
    public function execute()
    {
        $baseUrl = $this->customerconnectHelper->urlWithoutHttp();
        $fileName = "{$baseUrl}_invoices.csv";

        //M1 > M2 Translation Begin (Rule 2)
        //$content = $this->getLayout()->createBlock('customerconnect/customer_invoices_list_grid')
        //    ->getCsvFile();

        //$this->_prepareDownloadResponse($fileName, $content);

        /** @var \Magento\Backend\Block\Widget\Grid\ExportInterface $exportBlock  */
        $exportBlock = $this->_view->getLayout()->createBlock('Epicor\Customerconnect\Block\Customer\Invoices\Listing\Grid');

        return $this->_fileFactory->create(
            $fileName,
            $exportBlock->getCsvFile(),
            DirectoryList::VAR_DIR
        );
        //M1 > M2 Translation End
    }

}
