<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\B2b\Controller\Order;

class Index extends \Epicor\B2b\Controller\Order
{

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

   public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context, $commMessagingHelper, $customerSession, $registry);
    }



    /**
     * index action
     */
    public function execute()
    {

        if (!$this->customerSession->isLoggedIn()) {
            $this->customerSession->authenticate($this);
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');

        $this->getLayout()->getBlock('head')->setTitle($this->__('My B2B Orders'));

        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('b2b/order/');
        }

        $block = $this->getLayout()->getBlock('orders');

        $orders = $block->getOrders();
        if (!is_object($orders)) {

            $this->customerSession->addError($this->__('Sorry, we were unable to retrieve your orders. Please try again later.'));
            $this->_redirect('customer/account');
            return false;
        }

        if ($block) {
            $block->setRefererUrl($this->_getRefererUrl());
        }

        $this->renderLayout();
    }

    }
