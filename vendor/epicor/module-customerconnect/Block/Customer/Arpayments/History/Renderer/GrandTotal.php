<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Arpayments\History\Renderer;

/**
 * Serial number display
 *
 * @author     Epicor Websales Team
 */
class GrandTotal extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
  /**
   * @var \Epicor\Customerconnect\Model\ArPayment\Order
   */
  protected $arpaymentOrder;
  /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Customerconnect\Model\ArPayment\OrderFactory $arpaymentOrder,
        array $data = []
    ) {
        $this->arpaymentOrder = $arpaymentOrder;
        parent::__construct(
            $context,
            $data
        );
    }
    
    public function render(\Magento\Framework\DataObject $row)
    {
        $order = $this->arpaymentOrder->create();
        return $order->formatPrice($row->getGrandTotal());
    }

}


