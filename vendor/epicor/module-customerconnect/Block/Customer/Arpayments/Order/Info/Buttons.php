<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/**
 * Block of links in Order view page
 */
namespace Epicor\Customerconnect\Block\Customer\Arpayments\Order\Info;

use Magento\Customer\Model\Context;

class Buttons extends \Magento\Sales\Block\Order\Info\Buttons
{
    /**
     * @var string
     */
    protected $_template = 'Epicor_Customerconnect::customerconnect/arpayments/order/info/buttons.phtml';
   
    /**
     * Get url for printing order
     *
     * @param \Epicor\Customerconnect\Model\ArPayment\Order $order
     * @return string
     */
    public function getPrintUrl($order)
    {
        if (!$this->httpContext->getValue(Context::CONTEXT_AUTH)) {
            return $this->getUrl('customerconnect/arpayments/printpayment', ['order_id' => $order->getId()]);
        }
        return $this->getUrl('customerconnect/arpayments/printpayment', ['order_id' => $order->getId()]);
    }

}
