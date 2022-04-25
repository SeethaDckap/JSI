<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Total\Invoice;


class Weee extends \Magento\Weee\Model\Total\Invoice\Weee
{

    /**
     * Weee tax collector
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return \Magento\Weee\Model\Total\Invoice\Weee
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        parent::collect($invoice);
        $invoice->setGrandTotal(round($invoice->getGrandTotal(), 2));
        $invoice->setBaseGrandTotal(round($invoice->getBaseGrandTotal(), 2));
        return $this;
    }

}
