<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Products;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportXml extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Products
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
        $fileName = 'products.xml';

        /** @var \Magento\Backend\Block\Widget\Grid\ExportInterface $exportBlock  */
        $exportBlock = $this->_view->getLayout()->createBlock('Epicor\Common\Block\Adminhtml\Mapping\Products\Grid');

        return $this->_fileFactory->create(
            $fileName,
            $exportBlock->getExcelFile(),
            DirectoryList::VAR_DIR
        );
    }

}
