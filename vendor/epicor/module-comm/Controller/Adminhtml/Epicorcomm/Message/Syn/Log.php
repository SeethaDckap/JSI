<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Syn;

class Log extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Message\Syn
{
   
    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,            
        \Epicor\Comm\Model\Message\LogFactory $commMessageLogFactory,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper
        )
    {
        parent::__construct($context, $backendAuthSession, $commMessageLogFactory, $commMessagingHelper );
    }

    
    public function execute()
    {   
        $resultPage = $this->_initPage();
        $resultPage->getConfig()->getTitle()->prepend(__('SYN Log'));
        return $resultPage;
    }
   

}
