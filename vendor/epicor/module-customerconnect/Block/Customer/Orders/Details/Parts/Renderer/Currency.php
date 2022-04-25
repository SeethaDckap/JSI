<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Orders\Details\Parts\Renderer;


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
        $order = $this->registry->registry('customer_connect_order_details');

        $helper = $this->commMessagingHelper;
        /* @var $helper Epicor_Comm_Helper_Messaging */

        $index = $this->getColumn()->getIndex();
        $currency = $helper->getCurrencyMapping($order->getCurrencyCode(), \Epicor\Comm\Helper\Messaging::ERP_TO_MAGENTO);
        if(($index === "dealer_price_inc" || $index === "dealer_line_value_inc") && !$row->getData($index)){
            return $helper->formatPrice(0, true, $currency);
        }
        if ($row->getData($index)) {
            return $helper->formatPrice($row->getData($index), true, $currency);
        } else {
            return $helper->formatPrice(0, true, $currency);
        }
    }

}
