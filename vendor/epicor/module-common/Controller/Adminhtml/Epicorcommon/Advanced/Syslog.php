<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Advanced;

/**
 * System Logs Controller
 *
 * This allows the ability to view files in the var/log folder
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 * 
 */
abstract class Syslog extends \Epicor\Comm\Controller\Adminhtml\Generic
{
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendSession;

    /**     
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $_directoryList;
    
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,        
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    ) {        
        $this->_resultPageFactory = $context->getResultPageFactory();       
        $this->backendSession = $backendAuthSession;
        $this->_directoryList = $directoryList;
        parent::__construct($context, $backendAuthSession);
    }
    
    protected function _initAction()
    {
        $resultPage = $this->_resultPageFactory->create();

        return $resultPage;
    }
}
