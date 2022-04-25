<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\RecentPurchases;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportOrdersCsv extends \Epicor\Customerconnect\Controller\RecentPurchases {

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;
    private $_fileFactory;

    public function __construct(
    \Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\Locale\ResolverInterface $localeResolver, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory, \Epicor\Customerconnect\Model\Message\Request\Cphs $customerconnectMessageRequestCphs, \Magento\Framework\Session\Generic $generic, \Epicor\Customerconnect\Helper\Data $customerconnectHelper, \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {

        $this->customerconnectHelper = $customerconnectHelper;
        $this->_fileFactory = $fileFactory;
        parent::__construct(
                $context, $customerSession, $localeResolver, $resultPageFactory, $resultLayoutFactory
        );
    }

    /**
     * Export RecentPurchase grid to CSV format
     */
    public function execute() {
        $baseUrl = $this->customerconnectHelper->urlWithoutHttp();
        $fileName = "{$baseUrl}_recentpurchases.csv";

        //M1 > M2 Translation Begin (Rule 2)
        //$content = $this->getLayout()->createBlock('customerconnect/customer_recentpurchases_list_grid')
        //    ->getCsvFile();
        //$this->_prepareDownloadResponse($fileName, $content);

        /** @var \Magento\Backend\Block\Widget\Grid\ExportInterface $exportBlock  */
        $exportBlock = $this->_view->getLayout()->createBlock('Epicor\Customerconnect\Block\Customer\Recentpurchases\Listing\Grid');

        return $this->_fileFactory->create(
                        $fileName, $exportBlock->getCsvFile(), DirectoryList::VAR_DIR
        );
        //M1 > M2 Translation End
    }

}
