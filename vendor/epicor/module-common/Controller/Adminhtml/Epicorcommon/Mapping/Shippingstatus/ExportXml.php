<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Shippingstatus;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportXml extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Shippingstatus
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


public function execute()
    {
        $fileName = 'shipstatus.xml';
        //M1 > M2 Translation Begin (Rule 2)
        //$content = $this->getLayout()->createBlock('epicor_comm/adminhtml_mapping_shippingmethods_grid')
        //    ->getExcelFile();

        //$this->_prepareDownloadResponse($fileName, $content);

        /** @var \Magento\Backend\Block\Widget\Grid\ExportInterface $exportBlock  */
        $exportBlock = $this->_view->getLayout()->createBlock('Epicor\Common\Block\Adminhtml\Mapping\Shippingstatus\Griddownload');

        return $this->_fileFactory->create(
            $fileName,
            $exportBlock->getExcelFile(),
            DirectoryList::VAR_DIR
        );
        //M1 > M2 Translation End
    }

}
