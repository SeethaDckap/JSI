<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Invoices\Details;


class Totals extends \Epicor\Common\Block\Generic\Totals
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->commHelper = $commHelper;
        $this->commMessagingHelper = $commMessagingHelper;
        parent::__construct(
            $context,
            $commonHelper,
            $data
        );
    }
    public function _construct()
    {
        parent::_construct();
        $invoiceMsg = $this->registry->registry('supplier_connect_invoice_details');
        if($invoiceMsg) {
            $invoice = $invoiceMsg->getInvoice();
            if ($invoice) {
                $helper = $this->commMessagingHelper;
                $currencyCode = $helper->getCurrencyMapping($invoice->getCurrencyCode(), \Epicor\Customerconnect\Helper\Data::ERP_TO_MAGENTO);
                $this->addRow(__('Line Charges :'), $helper->getCurrencyConvertedAmount($invoice->getGoodsTotal(), $currencyCode), 'subtotal');
                $this->addRow(__('Invoice Amount :'), $helper->getCurrencyConvertedAmount($invoice->getGrandTotal(), $currencyCode), 'shipping');
                $this->addRow(__('Balance Due :'), $helper->getCurrencyConvertedAmount($invoice->getBalanceDue(), $currencyCode), 'grand_total');
            }
        }
        $this->setColumns(9);
    }

    public function isHidePricesActive()
    {
        return (bool) $this->commHelper->getEccHidePrice() && in_array($this->commHelper->getEccHidePrice(), [1, 2, 3]);
    }

}
