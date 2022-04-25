<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Orders\Details;


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
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Common\Helper\Data $commonHelper,
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
        $orderMsg = $this->registry->registry('supplier_connect_order_details');
        if($orderMsg) {
            $order = $orderMsg->getPurchaseOrder();
            if ($order) {
                $helper = $this->commMessagingHelper;

                $currencyCode = $helper->getCurrencyMapping($order->getCurrencyCode(), \Epicor\Customerconnect\Helper\Data::ERP_TO_MAGENTO);

                $this->addRow('Line(s) Subtotal :', $helper->getCurrencyConvertedAmount($order->getGoodsTotal(), $currencyCode), 'subtotal');
                $this->addRow('Total :', $helper->getCurrencyConvertedAmount($order->getGrandTotal(), $currencyCode), 'grand_total');
            }
        }

        $this->setColumns(10);
    }

    public function isHidePricesActive()
    {
        return (bool) $this->commHelper->getEccHidePrice() && in_array($this->commHelper->getEccHidePrice(), [1, 2, 3]);
    }

}
