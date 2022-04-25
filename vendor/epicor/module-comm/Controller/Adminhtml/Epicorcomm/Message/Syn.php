<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message;


/**
 * Epicor_Comm_Adminhtml_Message_SynController
 * 
 * Controller for Epicor > Messages > Send SYN
 * 
 * @author Gareth.James
 */
abstract class Syn extends \Epicor\Comm\Controller\Adminhtml\Generic
{
    
    /**
     * @var \Epicor\Comm\Model\Message\LogFactory
     */
    protected $commMessageLogFactory;


    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;


    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Comm\Model\Message\LogFactory $commMessageLogFactory,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper
        )
    {
        $this->commMessagingHelper = $commMessagingHelper;
        $this->commMessageLogFactory=$commMessageLogFactory;
        parent::__construct($context, $backendAuthSession);
    }
   
    protected function _initPage()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Epicor_Comm::message');
        
        return $resultPage;
    }
}
