<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\B2b\Controller\Invoice;

class Copy extends \Epicor\B2b\Controller\Invoice
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

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper
    ) {
        parent::__construct($context,$registry,$customerSession,$scopeConfig,$commHelper);
        $this->commMessagingHelper = $commMessagingHelper;
    }


/**
     * Order copy of invoice
     */
    public function execute()
    {
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        if (!$invoiceId) {
            $this->_forward('noRoute');
            return false;
        }
        $accountNumber = $this->commMessagingHelper->getErpAccountNumber();
        if (!$accountNumber) {
            $this->customerSession->addError($this->__('Sorry, we were unable to retrieve a valid account number.'));
        } else {
            $result = false;
            // Either send e-mail to customer support requesting an invoice copy to be sent out or send INV message
            if ($this->scopeConfig->isSetFlag('epicor_comm_enabled_messages/inv_request/inv_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $result = $this->_sendInvEmail($accountNumber, $invoiceId);
            }
            if ($this->scopeConfig->isSetFlag('epicor_comm_enabled_messages/inv_request/inv_message', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $result = $this->_sendInvMessage($accountNumber, $invoiceId);
            }

            if (!$result) {
                $this->customerSession->addError($this->__('Sorry, we were unable to request a copy of your invoice. Please try again later.'));
            } else {
                $this->customerSession->addSuccess($this->__('A copy of your invoice has been requested'));
            }
        }
        $this->_redirect('b2b/invoice');
    }

    }
