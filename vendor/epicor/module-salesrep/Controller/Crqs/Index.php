<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Crqs;

class Index extends \Epicor\SalesRep\Controller\Crqs
{

    /**
     * @var \Epicor\Comm\Helper\Messaging\Crqs
     */
    protected $commMessagingCrqsHelper;


    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
     /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    
    
    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Crqs
     */    
    protected $customerconnectMessageRequestCrqs;
    
    public function __construct(
         \Epicor\SalesRep\Controller\Context $context,       
        \Epicor\Customerconnect\Model\Message\Request\Crqs $customerconnectMessageRequestCrqs,
        \Epicor\Comm\Helper\Messaging\Crqs $commMessagingCrqsHelper
  //  \Epicor\Customerconnect\Model\Message\Request\Crqs $customerconnectMessageRequestCrqs,
    ) {
        $this->commMessagingCrqsHelper = $commMessagingCrqsHelper;
        $this->customerconnectMessageRequestCrqs = $customerconnectMessageRequestCrqs;//$customerconnectMessageRequestCrqs;
        $this->registry =  $context->getRegistry();
        $this->resultPageFactory = $context->getResultPageFactory();
        
          parent::__construct(
            $context
        );
    }
    /**
     * Index action 
     */
    public function execute()
    {
        $helper = $this->commMessagingCrqsHelper;
        /* @var $helper Epicor_Comm_Helper_Messaging_Crqs */

        if ($helper->mutipleAccountsEnabled()) {
            $crqs = $this->customerconnectMessageRequestCrqs;
            $messageTypeCheck = $crqs->getHelper('customerconnect/messaging')->getMessageType('CRQS');
            if ($crqs->isActive() && $messageTypeCheck) {
                $this->registry->register('rfqs_editable', true);
                $result = $this->resultPageFactory->create();
                return $result;
            } else {
                $this->messageManager->addErrorMessage(__('ERROR - RFQ Search not available'));
                if ($this->messageManager->getMessages()->getItems()) {
                    session_write_close();
                    $this->_redirect('customer/account/index');
                }
            }
        } else {
            $this->norouteAction();
        }
    }
}
