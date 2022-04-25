<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Sales\Order;


class Tax extends \Magento\Tax\Block\Sales\Order\Tax
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Tax\Model\Config $taxConfig,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $taxConfig,
            $data
        );
    }


    public function getTemplateFile($template = null)
    {
        $this->setTemplate('epicor_comm/tax/order/tax.phtml');
        return parent::getTemplateFile($template);
    }

}
