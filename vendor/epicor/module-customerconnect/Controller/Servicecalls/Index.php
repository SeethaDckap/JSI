<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Controller\Servicecalls;

class Index extends \Epicor\Customerconnect\Controller\Servicecalls
{

    const FRONTEND_RESOURCE = 'Epicor_Customerconnect::customerconnect_account_service_calls_read';

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cucs
     */
    protected $customerconnectMessageRequestCucs;

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
        \Epicor\Customerconnect\Model\Message\Request\Cucs $customerconnectMessageRequestCucs,
        \Magento\Framework\Session\Generic $generic
    ) {
        $this->customerconnectMessageRequestCucs = $customerconnectMessageRequestCucs;
        $this->generic = $generic;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }
    /**
     * Index action 
     */
    public function execute()
    {
        $cucs = $this->customerconnectMessageRequestCucs;
        $messageTypeCheck = $cucs->getHelper()->getMessageType('CUCS');

        if ($cucs->isActive() && $messageTypeCheck) {
            return $this->resultPageFactory->create();
        } else {
            $this->messageManager->addErrorMessage("ERROR - Service Calls Search not available");
            if ($this->messageManager->getMessages()->getItems()) {
                session_write_close();
                $this->_redirect('customer/account/index');
            }
        }
    }

    }
