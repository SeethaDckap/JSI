<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Advanced\Errors;

class View extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Advanced\Errors
{

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;
    
    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $decoder;
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $decypter;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\DecoderInterface $decoder,
        \Magento\Framework\Encryption\EncryptorInterface $decrypter
        
    ) {
        $this->commonHelper = $commonHelper;
        $this->registry = $context->getRegistry();
        $this->directoryList = $directoryList;
        $this->decoder = $decoder;
        $this->decrypter = $decrypter;
        parent::__construct($context, $backendAuthSession, $directoryList);
    }

    public function execute()
    {
        $resultPage = $this->_initAction();

        $helper = $this->commonHelper;
        /* @var $helper Epicor_Common_Helper_Data */

        $file = str_replace('.', '', $this->decrypter->decrypt($this->decoder->decode($this->getRequest()->getParam('report', null))));

        if ($file) {
            //M1 > M2 Translation Begin (Rule p2-5.5)
            //$filePath = Mage::getBaseDir('var') . '/' . 'report' . '/' . $file;
            $filePath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR) . '/' . 'report' . '/' . $file;
            //M1 > M2 Translation End
            if (file_exists($filePath)) {
                $this->registry->register('report_filename', $file);
            }
        }

        if (is_null($this->registry->registry('report_filename'))) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(__('report not found'));
            $resultRedirect->setPath($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
        $resultPage->getConfig()->getTitle()->prepend(__('Viewing report ' . $file));

        return $resultPage;
    }

}
