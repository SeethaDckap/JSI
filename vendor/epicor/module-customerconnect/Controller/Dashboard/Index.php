<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Controller\Dashboard;

class Index extends \Epicor\Customerconnect\Controller\Dashboard
{

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cuad
     */
    protected $customerconnectMessageRequestCuad;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Epicor\Customerconnect\Helper\Messaging
     */
    protected $customerconnectMessagingHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Customerconnect\Model\Message\Request\Cuad $customerconnectMessageRequestCuad,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper
    ) {
        $this->customerconnectMessageRequestCuad = $customerconnectMessageRequestCuad;
        $this->registry = $registry;
        $this->generic = $generic;
        $this->customerconnectMessagingHelper = $customerconnectMessagingHelper;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }


    /**
     * @return bool
     */
    protected function _isAccessAllowed($code)
    {
        return $this->_accessauthorization->isAllowed($code);
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        if ($request->isDispatched() && $request->getActionName() !== 'denied' &&
            ( !$this->_isAccessAllowed(
                    'Epicor_Customerconnect::customerconnect_account_information_billing_read'
                ) &&
                !$this->_isAccessAllowed(
                    'Epicor_Customerconnect::customerconnect_account_information_information_read'
                ) &&
                !$this->_isAccessAllowed(
                    'Epicor_Customerconnect::customerconnect_account_orders_read'
                ) &&
                !$this->_isAccessAllowed(
                    'Epicor_Customerconnect::customerconnect_account_invoices_read'
                )

            )
        ) {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getLayout()->unsetElement('content');
            $resultPage->getLayout()->getBlock('page.main.title')->setTemplate('Epicor_AccessRight::access_denied.phtml');
            return $resultPage;

        }

        return parent::dispatch($request);
    }
    /**
     * Index action 
     */
    public function execute()
    {

        $message = $this->customerconnectMessageRequestCuad;
        $helper = $this->customerconnectMessagingHelper;
        $messageTypeCheck = $helper->getMessageType('CUAD');

        $result = $this->resultPageFactory->create();
        if ($message->isActive() && $messageTypeCheck) {
            $this->registry->register('customerconnect_dashboard_ok', 'dashboard ok');
        } else {
            $this->messageManager->addErrorMessage(__('Error - Customer Connect Dashboard Not Available'));
        }

        return $result;
    }

}
