<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\B2b\Controller\Invoice;

class Index extends \Epicor\B2b\Controller\Invoice
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Comm\Helper\Data $commHelper
    ) {
        parent::__construct($context,$registry,$customerSession,$scopeConfig,$commHelper);
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

        $this->getLayout()->getBlock('head')->setTitle($this->__('My B2B Invoices'));

        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('b2b/invoice/');
        }

        $block = $this->getLayout()->getBlock('invoices');

        $invoices = $block->getInvoices();
        if (!is_object($invoices)) {

            $this->customerSession->addError($this->__('Sorry, we were unable to retrieve your invoices. Please try again later.'));
            $this->_redirect('customer/account');
            return false;
        }

        if ($block) {
            $block->setRefererUrl($this->_getRefererUrl());
        }

        $this->renderLayout();
    }

    }
