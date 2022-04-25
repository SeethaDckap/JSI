<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Sales\Order\Totals;


class Tax extends \Magento\Sales\Block\Adminhtml\Order\Totals\Tax
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Tax\Model\Sales\Order\TaxFactory $taxOrderFactory,
        \Magento\Sales\Helper\Admin $salesAdminHelper,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $taxConfig,
            $taxHelper,
            $taxCalculation,
            $taxOrderFactory,
            $salesAdminHelper,
            $data
        );
    }


    public function getTemplateFile($template = null)
    {
        $this->setTemplate('epicor_comm/sales/order/totals/tax.phtml');
        return parent::getTemplateFile($template);
    }

}
