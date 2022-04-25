<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Controller\Returns;

class Index extends \Epicor\Customerconnect\Controller\Returns
{

    const FRONTEND_RESOURCE = 'Epicor_Customerconnect::customerconnect_account_returns_read';

    /**
     * @var \Epicor\Comm\Model\Message\Request\Crrs
     */
    protected $commMessageRequestCrrs;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Comm\Model\Message\Request\Crrs $commMessageRequestCrrs,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Magento\Framework\Session\Generic $generic
    ) {
        $this->commMessageRequestCrrs = $commMessageRequestCrrs;
        $this->generic = $generic;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $commReturnsHelper,
            $generic
        );
    }
    /**
     * Index action 
     */
    public function execute()
    {
        $this->preDispatch();
        
        $crrs = $this->commMessageRequestCrrs;
        /* @var $crrs Epicor_Comm_Model_Message_Request_Crrs */
        $messageTypeCheck = $crrs->getHelper('customerconnect/messaging')->getMessageType('CRRS');

        if ($crrs->isActive() && $messageTypeCheck) {
            return $this->resultPageFactory->create();            
        } else {
            $this->messageManager->addErrorMessage(__('ERROR - Returns Search not available'));
            if ($this->messageManager->getMessages()) {
                session_write_close();
                $this->_redirect('customer/account/index');
            }
        }
    }

    }
