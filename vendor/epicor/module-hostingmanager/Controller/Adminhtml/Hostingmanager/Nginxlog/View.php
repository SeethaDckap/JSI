<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\HostingManager\Controller\Adminhtml\Hostingmanager\Nginxlog;

class View extends \Epicor\HostingManager\Controller\Adminhtml\Hostingmanager\Nginxlog
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;
    

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context, 
        \Magento\Backend\Model\Auth\Session $backendAuthSession, 
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Url\DecoderInterface $urlDecoder
    )
    {
        $this->urlDecoder = $urlDecoder;
        $this->registry = $context->getRegistry();
        parent::__construct($context, $backendAuthSession);
    }

    public function execute()
    {
        /* @var $helper Epicor_Common_Helper_Data */
        $logFile = $this->urlDecoder->decode(html_entity_decode($this->getRequest()->getParam('filename', null)));
        
        if ($logFile) {
            $logFilePath = DIRECTORY_SEPARATOR . 'var' . '/' . 'log' . '/' . 'nginx' . DIRECTORY_SEPARATOR . $logFile;
            if (file_exists($logFilePath) && !is_dir($logFilePath)) {
                $this->registry->register('log_filename', $logFile);
            }
        }

        if (is_null($this->registry->registry('log_filename'))) {
            $this->messageManager->addErrorMessage(__('Log file not found'));
            $this->_redirect('*/*/index');
        } else {
            return $this->_initPage();
        }
    }

}
