<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Invoices\Details\Lines\Renderer;


/**
 * Currency display, converts a row value to currency display
 *
 * @author Gareth.James
 */
class Currency extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->commMessagingHelper = $commMessagingHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {

        $invoice = $this->registry->registry('supplier_connect_invoice_details');

        $helper = $this->commMessagingHelper;
        /* @var $helper Epicor\Comm\Helper\Messaging */

        $index = $this->getColumn()->getIndex();
        $currency = $helper->getCurrencyMapping($invoice->getInvoice()->getCurrencyCode(), \Epicor\Comm\Helper\Messaging::ERP_TO_MAGENTO);

        return $helper->formatPrice($row->getData($index), true, $currency);
    }

}
