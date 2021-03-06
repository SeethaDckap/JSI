<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Epicor\Comm\Block\Sales\Order;

/**
 * Sales order link
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 100.0.2
 */
class Link extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface      $defaultPath
     * @param \Magento\Framework\Registry                      $registry
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->_registry = $registry;
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    private function getOrder()
    {
        return $this->_registry->registry('current_order');
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl($this->getPath(),
            ['order_id' => $this->getOrder()->getId()]);
    }

    /**
     * Force stop display customer
     * Invoice in my account with epicor payment.
     *
     * Order approval hide
     * Epicor payment invoice
     * When order is pending for approval.
     *
     * @return string
     */
    protected function _toHtml()
    {
        $order = $this->getOrder();
        $paymentMethod = $order->getPayment()->getMethod();
        if (($order->getIsApprovalPending() == 1 || $order->getEccGorSent() == 8) && $paymentMethod == 'pay') {
            return '';
        }

        if ($this->hasKey()
            && method_exists($this->getOrder(), 'has' . $this->getKey())
            && !$this->getOrder()->{'has' . $this->getKey()}()
        ) {
            return '';
        }

        return parent::_toHtml();
    }
}
