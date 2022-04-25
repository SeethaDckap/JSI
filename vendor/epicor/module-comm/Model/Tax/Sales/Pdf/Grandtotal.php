<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Tax\Sales\Pdf;

class Grandtotal extends \Magento\Tax\Model\Sales\Pdf\Grandtotal
{

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    public function __construct(
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory,
        \Magento\Tax\Model\Config $taxConfig,
        \Epicor\Comm\Helper\Data $commHelper,
        array $data = []
    ) {
        $this->commHelper = $commHelper;
        parent::__construct(
            $taxHelper,
            $taxCalculation,
            $ordersFactory,
            $taxConfig,
            $data
        );
    }


    public function getTotalsForDisplay()
    {
        $totals = parent::getTotalsForDisplay();
        foreach ($totals as $key => $total) {
            if ($total['label'] == 'Tax:') {
                if ($this->commHelper->removeTaxLine($total['amount'])) {
                    unset($totals[$key]);
                }
            }
        }
        return $totals;
    }

}
