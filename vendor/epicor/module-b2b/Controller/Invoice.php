<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\B2b\Controller;


/**
 * Invoice controller
 *
 * @category   Epicor
 * @package    Epicor_B2b
 * @author     Epicor Websales Team
 */
abstract class Invoice extends \Magento\Framework\App\Action\Action
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
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->commHelper = $commHelper;
        parent::__construct(
            $context
        );
    }

/**
     * Init layout, messages and set active block for invoice
     *
     * @return null
     */
    protected function _viewPage()
    {
        if (!$this->_loadValidInvoice()) {
            return false;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');

        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('b2b/invoice');
        }

        $block = $this->getLayout()->getBlock('invoice_view');

        if ($block) {
            $block->setRefererUrl($this->_getRefererUrl());
        }

        $this->renderLayout();
    }

    /**
     * Try to load valid invoice by invoice_id and register it
     *
     * @param int $invoiceId
     * @return bool
     */
    protected function _loadValidInvoice($invoiceId = null)
    {
        if (null === $invoiceId) {
            $invoiceId = $this->getRequest()->getParam('invoice_id');
        }
        if (!$invoiceId) {
            $this->_forward('noRoute');
            return false;
        }
        $invoice = $this->_getErpInvoice($invoiceId);
        if (!$invoice) {
            $this->_redirect('b2b/invoice');
            return false;
        }
        $this->registry->register('current_invoice', $invoice);
        return true;
    }

    private function _getErpInvoice($invoiceId)
    {
        $customer = $this->customerSession->getCustomer();
        $ivd = $this->b2bMessageRequestIvdFactory->create();
        $ivd->setCustomer($customer);
        $ivd->setInvoiceNumber($invoiceId);
        if (!$ivd->sendMessage()) {
            $this->customerSession->addError($this->__('Sorry, we were unable to retrieve the invoice details. Please try again later.'));
            return false;
        } else {
            return $ivd->getErpInvoice();
        }
    }

    protected function _sendInvEmail($accountNumber, $invoiceId)
    {

        $result = false;
        $customer = $this->customerSession->getCustomer();
        $email = $this->scopeConfig->getValue('epicor_comm_enabled_messages/inv_request/inv_email_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!$email) {
            $this->customerSession->addError($this->__('Sorry, we were unable to process your request at this time.'));
        } else {
            $message = "A customer has requested a copy of an invoice: \n";
            $message .= "Magento Customer ID: " . $customer->getId() . "\n";
            $message .= "ERP Customer accountNumber: " . $accountNumber . "\n";
            //$message .= "Customer Name: " . $customer->getName() . "<br />";
            //$message .= "Customer Email Address: " . $customer->getEmail() . "<br />";
            $message .= "Invoice ID Requested: " . $invoiceId . "\n";

            $data = array('name' => $customer->getName(),
                'email' => $customer->getEmail(),
                'telephone' => $customer->getTelephone(),
                'comment' => $message,
            );

            if ($this->commHelper->sendEmail($data, $email)) {
                $result = true;
            }
        }
        return $result;
    }

    protected function _sendInvMessage($accountNumber, $invoiceId)
    {
        $customer = $this->customerSession->getCustomer();
        $inv = $this->b2bMessageRequestInvFactory->create();
        $inv->setCustomer($customer);
        $inv->setInvoiceNumber($invoiceId);
        return ($inv->sendMessage());
    }

}
