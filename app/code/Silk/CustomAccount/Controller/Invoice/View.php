<?php

namespace Silk\CustomAccount\Controller\Invoice;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class View extends \Magento\Framework\App\Action\Action
{
    protected $session;

    protected $resultPageFactory;

    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory
    ) {
        $this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        // if (!$this->session->isLoggedIn()) {
        //     $resultRedirect = $this->resultRedirectFactory->create();
        //     $resultRedirect->setPath('customer/account/login');
        //     return $resultRedirect;
        // }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Invoice View'));
        return $resultPage;
    }
}
