<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Controller\Adminhtml\Hostingmanager\Nginxlog;

class Download extends \Epicor\HostingManager\Controller\Adminhtml\Hostingmanager\Nginxlog {

    /**
     * @var Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_downloader;

    /**
     * @var Magento\Framework\Filesystem\DirectoryList
     */
    protected $_directory;

   /**
    * 
    * @param \Epicor\Comm\Controller\Adminhtml\Context $context
    * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
    * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
    * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    * @param \Magento\Framework\Filesystem\DirectoryList $directory
    */
    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context, 
        \Magento\Backend\Model\Auth\Session $backendAuthSession, 
        \Magento\Framework\Url\DecoderInterface $urlDecoder, 
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory, 
        \Magento\Framework\Filesystem\DirectoryList $directory
    ) {
        $this->_downloader = $fileFactory;
        $this->directory = $directory;
        $this->urlDecoder = $urlDecoder;
        parent::__construct($context, $backendAuthSession);
    }

    public function execute() {
        $fileName = $this->urlDecoder->decode(html_entity_decode($this->getRequest()->getParam('filename', null)));
        $file = DIRECTORY_SEPARATOR . 'var' . '/' . 'log' . '/' . 'nginx' . DIRECTORY_SEPARATOR . $fileName;

        if (file_exists($file) && !is_dir($file)) {
            return $this->_downloader->create(
                            $fileName, @file_get_contents($file)
            );
        } else {
            $this->messageManager->addError(__('Log file not found'));
            $this->_redirect('*/*/index');
        }
    }

}
