<?php

namespace Silk\CustomAccount\Controller\Quote;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Checkout\Model\Cart;

class Create extends \Magento\Framework\App\Action\Action
{
    protected $session;

    protected $resultPageFactory;

    protected $checkoutSession;

    protected $cart;

    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        Cart $cart
    ) {
        $this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->cart = $cart;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }


    public function execute()
    {
        if (!$this->session->isLoggedIn()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }

        try {
            $this->cart->truncate()->save();
        } catch (\Exception $exception) {

        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('New Quote'));
  
        return $resultPage;
    }
}
