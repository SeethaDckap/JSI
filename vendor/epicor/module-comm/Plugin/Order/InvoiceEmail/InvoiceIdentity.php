<?php
/**
 * Copyright © 2020 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Order\InvoiceEmail;

use Epicor\Comm\Model\Sales\Invoice\Email\Sender\SouInvoice;

/**
 * Class InvoiceIdentity
 */
class InvoiceIdentity
{
    /**
     * @var SouInvoice
     */
    private $souInvoice;

    /**
     * InvoiceIdentity constructor.
     * @param SouInvoice $souInvoice
     */
    public function __construct(
        SouInvoice $souInvoice
    ) {
        $this->souInvoice = $souInvoice;
    }

    /**
     * It will ignore invoice configuration (Sales > Sales Emails > Invoice > Enabled). Return as Enabled.
     * The Invoice email configuration will be controlled by the SOU configuration.
     * @param \Magento\Sales\Model\Order\Email\Container\InvoiceIdentity $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsEnabled(
        \Magento\Sales\Model\Order\Email\Container\InvoiceIdentity $subject,
        bool $result
    ) {
        if ($this->souInvoice->getSouInvoiceEmailFlag()) {
            return true;
        }
        return $result;
    }
}
