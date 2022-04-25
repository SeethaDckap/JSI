<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\DocumentPrint;

use Magento\Framework\View\Element\Template;
use \Magento\Framework\Url\EncoderInterface;
use  \Magento\Framework\Encryption\EncryptorInterface;

class ActionLinks extends Template
{
    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    /**
     * ActionLinks constructor.
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        array $data = []
    ) {
        $this->_accessauthorization = $context->getAccessAuthorization();
        parent::__construct($context, $data);
    }

    public function getAccountNumber()
    {
        return $this->getData('account_number');
    }

    public function getEntityDocument()
    {
        return $this->getData('entity_document');
    }

    public function getEntityKey()
    {
        return $this->getData('entity_key');
    }

    public function isLinkDisplayActive($linkType): bool
    {
        if ($this->isPreqActive() && $this->isErpE10() && $this->isDisplayTypeForE10($linkType)) {
            return true;
        }
        if ($this->isPreqActive() && $this->isErpP21() && $this->isDisplayTypeForP21($linkType)) {
            return true;
        }

        return false;
    }

    private function isPreqActive(): bool
    {
        return (bool)$this->_scopeConfig->getValue('customerconnect_enabled_messages/PREQ_request/active');
    }

    private function isErpE10(): bool
    {
        return $this->_scopeConfig->getValue('Epicor_Comm/licensing/erp') === 'e10';
    }

    private function isErpP21(): bool
    {
        return $this->_scopeConfig->getValue('Epicor_Comm/licensing/erp') === 'p21';
    }

    private function isDisplayTypeForE10($linkType): bool
    {
        return $this->isTypeViewDashboard($linkType)
            || $this->isTypeViewInvoice($linkType)
            || $this->isTypeViewOrder($linkType)
            || $this->isTypeViewRma($linkType)
            || $this->isTypeViewShipment($linkType);
    }

    private function isDisplayTypeForP21($linkType): bool
    {
        return $this->isTypeViewDashboard($linkType)
            || $this->isTypeViewInvoice($linkType)
            || $this->isTypeViewOrder($linkType);
    }

    private function isTypeViewDashboard($linkType): bool
    {
        $code = "Epicor_Customerconnect::customerconnect_account_orders_" . $linkType;
        switch ($linkType) {
            case 'print':
                $code = $this->getData('access_print');
                break;
            case 'email':
                $code = $this->getData('access_email');
                break;
        }
        return $this->_isAccessAllowed($code) && $this->_request->getOriginalPathInfo()
            && is_int(strpos($this->_request->getOriginalPathInfo(), '/customerconnect/dashboard/'));
    }

    private function isTypeViewInvoice($linkType): bool
    {
        $code = "Epicor_Customerconnect::customerconnect_account_invoices_" . $linkType;
        return $this->_isAccessAllowed($code) && $this->_request->getOriginalPathInfo()
            && is_int(strpos($this->_request->getOriginalPathInfo(), '/customerconnect/invoices/'));
    }

    private function isTypeViewShipment($linkType): bool
    {
        $code = "Epicor_Customerconnect::customerconnect_account_shipments_" . $linkType;
        return $this->_isAccessAllowed($code) && $this->_request->getOriginalPathInfo()
            && is_int(strpos($this->_request->getOriginalPathInfo(), '/customerconnect/shipments/'));
    }

    private function isTypeViewRma($linkType): bool
    {
        $code = "Epicor_Customerconnect::customerconnect_account_rma_" . $linkType;
        return $this->_isAccessAllowed($code) && $this->_request->getOriginalPathInfo()
            && is_int(strpos($this->_request->getOriginalPathInfo(), '/customerconnect/rmas/'));
    }

    private function isTypeViewOrder($linkType): bool
    {
        $code = "Epicor_Customerconnect::customerconnect_account_orders_" . $linkType;
        switch ($linkType) {
            case 'print':
                $code = $this->getData('access_print');
                break;
            case 'email':
                $code = $this->getData('access_email');
                break;
        }
        return $this->_isAccessAllowed($code) && $this->_request->getOriginalPathInfo()
            && (is_int(strpos($this->_request->getOriginalPathInfo(), '/customerconnect/orders/'))
                || is_int(strpos($this->_request->getOriginalPathInfo(), '/dealerconnect/orders/'))
                || is_int(strpos($this->_request->getOriginalPathInfo(), '/dealerconnect/dashboard/')));
    }

    /**
     * @return bool
     */
    protected function _isAccessAllowed($code)
    {
        return $this->_accessauthorization->isAllowed($code);
    }
}